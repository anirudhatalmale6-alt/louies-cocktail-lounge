<?php
/**
 * Import the bar's own photographs, event flyers and league logos.
 *
 * Run with:  wp --path=site eval-file import-media.php
 *
 * Idempotent by attachment slug. `wp media import` happily creates a second
 * copy every time you run it, which is how a media library ends up with
 * pool-table-1, pool-table-2, pool-table-3 and a gallery pointing at whichever
 * one happened to be newest. Here every file has a fixed slug and an existing
 * attachment with that slug is reused, so re-running this changes nothing.
 *
 * NOTE: wp eval-file runs this inside a function, so nothing here is a global.
 * Anything the rest of the script needs is passed explicitly.
 */

if ( ! defined( 'WP_CLI' ) ) {
	die( "run me through wp eval-file\n" );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$root = dirname( __DIR__ ) . '/40687911';
if ( ! is_dir( $root ) ) {
	$root = getcwd();
}

/**
 * Write a single Bar Details field.
 *
 * They all live inside one `louies_settings` array option, so this has to be
 * read-modify-write. Calling update_option( 'louies_settings', array( ... ) )
 * with just the one key would wipe the phone number, the address and every
 * other setting on the page.
 */
function louies_set_option( $key, $value ) {
	$opts = get_option( 'louies_settings', array() );
	if ( ! is_array( $opts ) ) {
		$opts = array();
	}
	$opts[ $key ] = (string) $value;
	update_option( 'louies_settings', $opts );
}

/**
 * Import one file, or hand back the one already imported under this slug.
 */
function louies_import( $path, $slug, $title, $alt, $caption = '' ) {
	$existing = get_page_by_path( $slug, OBJECT, 'attachment' );
	if ( $existing ) {
		update_post_meta( $existing->ID, '_wp_attachment_image_alt', $alt );
		wp_update_post( array(
			'ID'           => $existing->ID,
			'post_title'   => $title,
			'post_excerpt' => $caption,
		) );
		WP_CLI::log( sprintf( 'reuse  #%d  %s', $existing->ID, $slug ) );
		return (int) $existing->ID;
	}

	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( "missing file: {$path}" );
		return 0;
	}

	// Copy to a temp name first. media_handle_sideload MOVES the file it is
	// given, and moving the originals out of the project directory would mean
	// the next run has nothing left to import.
	$tmp = wp_tempnam( basename( $path ) );
	copy( $path, $tmp );

	$id = media_handle_sideload(
		array( 'name' => $slug . '.' . pathinfo( $path, PATHINFO_EXTENSION ), 'tmp_name' => $tmp ),
		0,
		$title
	);

	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		WP_CLI::warning( $slug . ': ' . $id->get_error_message() );
		return 0;
	}

	wp_update_post( array( 'ID' => $id, 'post_name' => $slug, 'post_excerpt' => $caption ) );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	WP_CLI::log( sprintf( 'import #%d  %s', $id, $slug ) );
	return (int) $id;
}

// --------------------------------------------------------------- photos ---
// Order here is the order they appear in the gallery. Front-loaded with the
// pictures that have people in them - the bar asked for that specifically, and
// an empty room at 6am sells nothing.

$photos = array(
	array( 'photos3/20240323_231108.jpg', 'louies-band-dancefloor', 'Live band and a full dance floor', 'A band playing to a full dance floor at Louie\'s' ),
	array( 'photos3/20240720_150031.jpg', 'louies-busy-bar', 'A busy night at the bar', 'Customers along the bar on a busy night' ),
	array( 'photos3/2025-12-05-21-26-17.jpg', 'louies-packed-house', 'A packed house', 'A packed room at Louie\'s Cocktail Lounge' ),
	array( 'photos3/20240720_145239.jpg', 'louies-live-stage', 'A band on the Louie\'s stage', 'A live band on stage under the Louie\'s sign' ),
	array( 'photos3/20260807_200204.jpg', 'louies-pool-players', 'Pool in the games room', 'Two people playing pool by the arcade machines' ),
	array( 'photos3/20250311_084405.jpg', 'louies-exterior-day', 'Louie\'s Cocktail Lounge, 3030 Mather Field Road', 'The front of Louie\'s Cocktail Lounge in daylight' ),
	array( 'photos3/20220514_211110.jpg', 'louies-sign-lit-night', 'The sign lit up after dark', 'The neon martini glass and Louie\'s sign lit at night' ),
	array( 'photos3/20240308_212736.jpg', 'louies-wrestling-ringside', 'Wrestling night, ringside', 'A wrestling ring set up at Louie\'s with a crowd around it' ),
	array( 'photos3/20251031_231920.jpg', 'louies-halloween', 'Halloween at Louie\'s', 'Customers in costume on the dance floor at Halloween' ),
	array( 'photos3/20260228_225207.jpg', 'louies-live-late', 'Live music, late', 'A band playing under stage lights' ),
	array( 'photos3/20240929_005039.jpg', 'louies-regulars', 'Regulars', 'A group of regulars at Louie\'s' ),
	array( 'photos3/20251225_221931.jpg', 'louies-christmas', 'Christmas at the bar', 'Two customers at the bar at Christmas' ),
	array( 'photos3/20231202_224936.jpg', 'louies-wrestling-crowd', 'Ringside seats', 'The crowd around the ring on a wrestling night' ),
	array( 'photos3/20230615_060907.jpg', 'louies-back-bar', 'The bar and the back bar', 'The bar counter and bottles at Louie\'s' ),
	array( 'photos3/20251001_165653.jpg', 'louies-pool-pinball', 'Pool tables and pinball', 'Pool table with arcade and pinball machines behind' ),
	array( 'photos3/20230615_060924.jpg', 'louies-pool-arcade', 'The pool room', 'Two pool tables and the arcade corner' ),
	array( 'photos3/20230615_060809.jpg', 'louies-main-room', 'The main room', 'Tables, pool table and neon signs in the main room' ),
	array( 'photos3/20230615_060853.jpg', 'louies-tables', 'Tables and the pool room', 'Seating and tables looking through to the pool room' ),
	array( 'photos3/FB_IMG_1767819987446.jpg', 'louies-bottle-on-ice', 'Bottle on ice', 'A bottle of champagne in an ice bucket' ),
);

$photo_ids = array();
foreach ( $photos as $p ) {
	$id = louies_import( $root . '/' . $p[0], $p[1], $p[2], $p[3], isset( $p[4] ) ? $p[4] : '' );
	if ( $id ) {
		$photo_ids[ $p[1] ] = $id;
	}
}

// --------------------------------------------------------------- flyers ---

$flyers = array(
	'mr-purple'      => array( 'IMG_20260903_174650.jpg', 'Mr. Purple - Saturday 12 September', 'Flyer for Mr. Purple playing Louie\'s on Saturday 12 September, 3pm to 6pm' ),
	'chili-cook-off' => array( 'image0.jpg', 'Chili Cook-Off - Friday 25 September', 'Flyer for the Louie\'s chili cook-off on 25 September 2026' ),
);

foreach ( $flyers as $event_slug => $f ) {
	$id = louies_import( $root . '/' . $f[0], 'louies-flyer-' . $event_slug, $f[1], $f[2] );
	if ( ! $id ) {
		continue;
	}
	$event = get_posts( array( 'post_type' => 'louies_event', 'name' => $event_slug, 'posts_per_page' => 1, 'post_status' => 'any' ) );
	if ( $event ) {
		set_post_thumbnail( $event[0]->ID, $id );
		WP_CLI::log( sprintf( 'poster #%d -> event %s (#%d)', $id, $event_slug, $event[0]->ID ) );
	} else {
		WP_CLI::warning( "no event found for flyer: {$event_slug}" );
	}
}

// -------------------------------------------------------- league logos ---
// Cut out of the sheet the bar supplied on 03/09/2026. See parts/sports.php for
// why these are an upload rather than something shipped inside the theme.

$leagues = array(
	'nfl' => 'NFL',
	'nba' => 'NBA',
	'mlb' => 'MLB',
	'nhl' => 'NHL',
);

foreach ( $leagues as $key => $mark ) {
	$id = louies_import(
		$root . '/leagues/' . $key . '.png',
		'louies-league-' . $key,
		$mark . ' logo',
		$mark
	);
	if ( $id ) {
		louies_set_option( 'league_' . $key . '_id', $id );
		WP_CLI::log( sprintf( 'league %s -> #%d', $key, $id ) );
	}
}

// ------------------------------------------------------------- settings ---

// The gallery, in the order defined above.
louies_set_option( 'gallery_ids', implode( ',', array_values( $photo_ids ) ) );

// Section photographs. Each of these was a stock-ish interior before; now they
// are the bar's own room with its own customers in it.
if ( isset( $photo_ids['louies-busy-bar'] ) ) {
	louies_set_option( 'sports_image_id', $photo_ids['louies-busy-bar'] );
}
if ( isset( $photo_ids['louies-packed-house'] ) ) {
	louies_set_option( 'private_image_id', $photo_ids['louies-packed-house'] );
}

// The karaoke band at the top of the home page. This one is not in $photo_ids
// because it came off the old site rather than out of the phone photos, so it
// is looked up by slug - and left alone if the bar has already chosen its own.
$karaoke = get_page_by_path( 'the-karaoke-stage', OBJECT, 'attachment' );
if ( $karaoke && ! trim( (string) louies_option( 'karaoke_image_id' ) ) ) {
	louies_set_option( 'karaoke_image_id', (int) $karaoke->ID );
	WP_CLI::log( sprintf( 'karaoke photo -> #%d', $karaoke->ID ) );
}

WP_CLI::success( sprintf( 'media done: %d photos, gallery set', count( $photo_ids ) ) );
