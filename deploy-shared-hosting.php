<?php
/**
 * One-shot installer for Louie's Cocktail Lounge.
 *
 * Uploaded over FTP, run once over HTTPS, then deleted. Guarded by a token so
 * that nobody who guesses the filename can run it, and it refuses to do
 * anything at all once the marker file is gone.
 */

define( 'LOUIES_TOKEN', 'CHANGE-ME-BEFORE-UPLOADING' );

if ( ( $_GET['t'] ?? '' ) !== LOUIES_TOKEN ) {
	http_response_code( 403 );
	exit( "no\n" );
}

header( 'Content-Type: text/plain; charset=utf-8' );

$root = __DIR__;
$src  = $root . '/louies-src';
$step = $_GET['step'] ?? '';

function out( $s ) { echo $s . "\n"; }

function rcopy( $from, $to ) {
	if ( is_dir( $from ) ) {
		if ( ! is_dir( $to ) && ! mkdir( $to, 0755, true ) ) { return false; }
		foreach ( scandir( $from ) as $f ) {
			if ( '.' === $f || '..' === $f ) { continue; }
			if ( ! rcopy( $from . '/' . $f, $to . '/' . $f ) ) { return false; }
		}
		return true;
	}
	return copy( $from, $to );
}

function rrm( $path ) {
	if ( is_dir( $path ) ) {
		foreach ( scandir( $path ) as $f ) {
			if ( '.' === $f || '..' === $f ) { continue; }
			rrm( $path . '/' . $f );
		}
		return rmdir( $path );
	}
	return is_file( $path ) ? unlink( $path ) : true;
}

/**
 * The seed scripts were written for wp-cli and there is no wp-cli on shared
 * hosting. They only ever call log/warning/success, so a shim is enough - and
 * it keeps ONE canonical copy of each script rather than a second hosting
 * variant that would drift away from the tested one.
 */
if ( ! class_exists( 'WP_CLI' ) ) {
	class WP_CLI {
		public static function log( $m ) { echo '  ' . $m . "\n"; }
		public static function warning( $m ) { echo '  WARNING: ' . $m . "\n"; }
		public static function success( $m ) { echo '  OK: ' . $m . "\n"; }
		public static function error( $m ) { echo '  ERROR: ' . $m . "\n"; exit( 1 ); }
	}
}

function louies_shim_wp_cli() {
	if ( ! defined( 'WP_CLI' ) ) { define( 'WP_CLI', true ); }
}

function louies_wp() {
	define( 'WP_USE_THEMES', false );
	require_once __DIR__ . '/wp-load.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-includes/pluggable.php';
}

switch ( $step ) {

	case 'unzip':
		$zip = new ZipArchive();
		if ( true !== $zip->open( $root . '/louies-deploy.zip' ) ) {
			out( 'FAIL: cannot open louies-deploy.zip' );
			exit;
		}
		rrm( $src );
		mkdir( $src, 0755, true );
		$zip->extractTo( $src );
		$n = $zip->numFiles;
		$zip->close();
		out( "unzipped {$n} files into louies-src" );

		foreach ( array( 'themes/louies', 'plugins/louies-core' ) as $rel ) {
			$to = $root . '/wp-content/' . $rel;
			rrm( $to );
			out( ( rcopy( $src . '/wp-content/' . $rel, $to ) ? 'installed  ' : 'FAILED     ' ) . $rel );
		}
		break;

	case 'activate':
		louies_wp();
		switch_theme( 'louies' );
		out( 'theme: ' . get_option( 'stylesheet' ) );
		$r = activate_plugin( 'louies-core/louies-core.php' );
		out( 'plugin: ' . ( is_wp_error( $r ) ? 'ERROR ' . $r->get_error_message() : 'active' ) );
		break;

	case 'seed':
		louies_wp();
		louies_shim_wp_cli();
		out( '--- seed.php ---' );
		require $src . '/seed.php';
		break;

	// Importing 37 images with every thumbnail size takes longer than nginx's
	// 60-second proxy timeout. The 504 is the PROXY giving up, not PHP - so run
	// detached from the connection and write the log to disk instead of to a
	// response nobody is still listening to.
	case 'media':
		set_time_limit( 0 );
		ignore_user_abort( true );
		$log = $root . '/louies-media.log';
		ob_start( function ( $chunk ) use ( $log ) {
			file_put_contents( $log, $chunk, FILE_APPEND );
			return '';
		}, 1 );
		file_put_contents( $log, "--- import-media.php ---\n" );
		louies_wp();
		louies_shim_wp_cli();
		require $src . '/import-media.php';
		echo "DONE\n";
		ob_end_flush();
		break;

	case 'flush':
		louies_wp();
		flush_rewrite_rules( true );
		out( 'permalinks: ' . get_option( 'permalink_structure' ) );
		out( 'front page: ' . get_option( 'show_on_front' ) . ' #' . get_option( 'page_on_front' ) );
		out( 'pages:  ' . wp_count_posts( 'page' )->publish );
		out( 'events: ' . wp_count_posts( 'louies_event' )->publish );
		out( 'menu items: ' . wp_count_posts( 'louies_menu_item' )->publish );
		out( 'attachments: ' . wp_count_posts( 'attachment' )->inherit );
		foreach ( (array) get_theme_mod( 'nav_menu_locations', array() ) as $loc => $id ) {
			out( "nav {$loc}: #{$id} (" . count( (array) wp_get_nav_menu_items( $id ) ) . ' items)' );
		}
		out( 'logo: #' . (int) get_theme_mod( 'custom_logo' ) );
		break;

	/**
	 * The first media run was killed by the proxy timeout partway through, and
	 * louies_import() sets the canonical post_name AFTER the sideload - so any
	 * image interrupted in that gap got imported a second time under a
	 * WordPress-generated slug. List every attachment with whether anything
	 * actually points at it, so the strays can be removed by ID and nothing is
	 * deleted on a guess.
	 */
	case 'audit':
		louies_wp();
		global $wpdb;
		$opts = wp_json_encode( get_option( 'louies_settings', array() ) );
		foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) ) as $a ) {
			$used = array();
			$thumb = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_thumbnail_id' AND meta_value=%d", $a->ID ) );
			if ( $thumb ) { $used[] = 'thumb:' . implode( ',', $thumb ); }
			if ( (int) get_theme_mod( 'custom_logo' ) === $a->ID ) { $used[] = 'logo'; }
			if ( preg_match( '/\b' . $a->ID . '\b/', $opts ) ) { $used[] = 'option'; }
			$in_post = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_status='publish' AND post_content LIKE %s", '%wp-image-' . $a->ID . '%' ) );
			if ( $in_post ) { $used[] = 'content:' . implode( ',', $in_post ); }
			printf( "%-5d %-40s %-45s %s\n", $a->ID, $a->post_name, basename( get_attached_file( $a->ID ) ), $used ? implode( ' ', $used ) : 'UNREFERENCED' );
		}
		break;

	case 'delete':
		louies_wp();
		foreach ( array_filter( array_map( 'intval', explode( ',', $_GET['ids'] ?? '' ) ) ) as $id ) {
			$p = get_post( $id );
			if ( ! $p || 'attachment' !== $p->post_type ) { out( "skip #{$id}: not an attachment" ); continue; }
			out( ( wp_delete_attachment( $id, true ) ? 'deleted #' : 'FAILED  #' ) . $id . '  ' . $p->post_name );
		}
		break;

	case 'cleanup':
		rrm( $src );
		unlink( $root . '/louies-deploy.zip' );
		out( 'removed louies-src and louies-deploy.zip' );
		out( 'now delete louies-deploy.php' );
		break;

	default:
		out( 'steps: unzip, activate, seed, media, flush, cleanup' );
}
