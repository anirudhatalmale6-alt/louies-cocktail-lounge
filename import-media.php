<?php
/**
 * Import every image the site needs and wire it to the thing that shows it.
 *
 * Run from inside this folder:
 *     wp --path=/path/to/wordpress eval-file import-media.php
 *
 * Paths are resolved against this file's own directory, so the folder can sit
 * anywhere - your machine, the server, a temp directory over SFTP.
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

$root = __DIR__;

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

function louies_get_option( $key ) {
	$opts = get_option( 'louies_settings', array() );
	return is_array( $opts ) && isset( $opts[ $key ] ) ? (string) $opts[ $key ] : '';
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

/**
 * Give an event its poster.
 */
function louies_set_poster( $event_slug, $attachment_id ) {
	if ( ! $attachment_id ) {
		return;
	}
	$event = get_posts( array(
		'post_type'      => 'louies_event',
		'name'           => $event_slug,
		'posts_per_page' => 1,
		'post_status'    => 'any',
	) );
	if ( ! $event ) {
		WP_CLI::warning( "no event found for poster: {$event_slug}" );
		return;
	}
	set_post_thumbnail( $event[0]->ID, $attachment_id );
	WP_CLI::log( sprintf( 'poster #%d -> event %s (#%d)', $attachment_id, $event_slug, $event[0]->ID ) );
}

// ------------------------------------------------------- brand and fixtures ---
// These five are what the site is built around: the header logo, the picture
// Facebook shows when someone shares a link, the home page background, the
// karaoke band photo and the combo artwork. They used to be imported by hand
// during the build, which meant a fresh install came up with no logo and no
// hero photo. They belong in the script.

$logo_id = louies_import(
	$root . '/brand/louies-logo-1600.png',
	'louies-cocktail-lounge-logo',
	"Louie's Cocktail Lounge logo",
	"Louie's Cocktail Lounge"
);
if ( $logo_id ) {
	set_theme_mod( 'custom_logo', $logo_id );
	WP_CLI::log( sprintf( 'header logo -> #%d', $logo_id ) );
}

$og_id = louies_import(
	$root . '/brand/louies-og.png',
	'louies-cocktail-lounge',
	"Louie's Cocktail Lounge",
	"Louie's Cocktail Lounge, Rancho Cordova"
);
if ( $og_id ) {
	louies_set_option( 'social_image_id', $og_id );
}

$hero_id = louies_import(
	$root . '/photos/louies-exterior-dusk.jpg',
	'louies-cocktail-lounge-at-dusk',
	"Louie's Cocktail Lounge at dusk",
	"Louie's Cocktail Lounge exterior at dusk with the neon sign lit and the flag flying"
);
if ( $hero_id ) {
	louies_set_option( 'hero_image_id', $hero_id );
}

$hotdog_id = louies_import(
	$root . '/photos/louies-combo-hotdog.jpg',
	'hot-dog-combo',
	'Hot dog combo',
	'Hot dog with chips and a domestic draft beer, the $2 combo at Louie\'s'
);
if ( $hotdog_id ) {
	louies_set_option( 'combo_hotdog_id', $hotdog_id );
}

// Not wired to a setting, but the bar asked for both combos photographed and
// this is the other one. Kept in the library so it is there when they want it.
louies_import(
	$root . '/photos/louies-combo-burger.jpg',
	'cheeseburger-combo',
	'Cheeseburger combo',
	'Cheeseburger with chips and a domestic draft beer, the $2 combo at Louie\'s'
);

// The karaoke band at the top of the home page. Left alone if the bar has
// already chosen its own photo in Bar Details - they are shooting their own.
$karaoke_id = louies_import(
	$root . '/photos/louies-karaoke-stage.jpg',
	'the-karaoke-stage',
	'The karaoke stage',
	"Singer on the karaoke stage with the big lyric screen and lit dance floor at Louie's"
);
if ( $karaoke_id && ! trim( louies_get_option( 'karaoke_image_id' ) ) ) {
	louies_set_option( 'karaoke_image_id', $karaoke_id );
	WP_CLI::log( sprintf( 'karaoke photo -> #%d', $karaoke_id ) );
}

// The wall of TVs. This is the Monday Night Football poster - see below.
$tvs_id = louies_import(
	$root . '/photos/louies-tables-and-tvs.jpg',
	'tables-and-the-wall-of-tvs',
	'Tables and the wall of TVs',
	"Tables and booths in front of the wall of sports TVs at Louie's"
);

// --------------------------------------------------------------- photos ---
// Order here is the order they appear in the gallery. Front-loaded with the
// pictures that have people in them - the bar asked for that specifically, and
// an empty room at 6am sells nothing.

$photos = array(
	array( '20240323_231108.jpg', 'louies-band-dancefloor', 'Live band and a full dance floor', 'A band playing to a full dance floor at Louie\'s' ),
	array( '20240720_150031.jpg', 'louies-busy-bar', 'A busy night at the bar', 'Customers along the bar on a busy night' ),
	array( '2025-12-05-21-26-17.jpg', 'louies-packed-house', 'A packed house', 'A packed room at Louie\'s Cocktail Lounge' ),
	array( '20240720_145239.jpg', 'louies-live-stage', 'A band on the Louie\'s stage', 'A live band on stage under the Louie\'s sign' ),
	array( '20260807_200204.jpg', 'louies-pool-players', 'Pool in the games room', 'Two people playing pool by the arcade machines' ),
	array( '20250311_084405.jpg', 'louies-exterior-day', 'Louie\'s Cocktail Lounge, 3030 Mather Field Road', 'The front of Louie\'s Cocktail Lounge in daylight' ),
	array( '20220514_211110.jpg', 'louies-sign-lit-night', 'The sign lit up after dark', 'The neon martini glass and Louie\'s sign lit at night' ),
	array( '20240308_212736.jpg', 'louies-wrestling-ringside', 'Wrestling night, ringside', 'A wrestling ring set up at Louie\'s with a crowd around it' ),
	array( '20251031_231920.jpg', 'louies-halloween', 'Halloween at Louie\'s', 'Customers in costume on the dance floor at Halloween' ),
	array( '20260228_225207.jpg', 'louies-live-late', 'Live music, late', 'A band playing under stage lights' ),
	array( '20240929_005039.jpg', 'louies-regulars', 'Regulars', 'A group of regulars at Louie\'s' ),
	array( '20251225_221931.jpg', 'louies-christmas', 'Christmas at the bar', 'Two customers at the bar at Christmas' ),
	array( '20231202_224936.jpg', 'louies-wrestling-crowd', 'Ringside seats', 'The crowd around the ring on a wrestling night' ),
	array( '20230615_060907.jpg', 'louies-back-bar', 'The bar and the back bar', 'The bar counter and bottles at Louie\'s' ),
	array( '20251001_165653.jpg', 'louies-pool-pinball', 'Pool tables and pinball', 'Pool table with arcade and pinball machines behind' ),
	array( '20230615_060924.jpg', 'louies-pool-arcade', 'The pool room', 'Two pool tables and the arcade corner' ),
	array( '20230615_060809.jpg', 'louies-main-room', 'The main room', 'Tables, pool table and neon signs in the main room' ),
	array( '20230615_060853.jpg', 'louies-tables', 'Tables and the pool room', 'Seating and tables looking through to the pool room' ),
	array( 'FB_IMG_1767819987446.jpg', 'louies-bottle-on-ice', 'Bottle on ice', 'A bottle of champagne in an ice bucket' ),
);

$photo_ids = array();
foreach ( $photos as $p ) {
	$id = louies_import( $root . '/photos-2026-09/' . $p[0], $p[1], $p[2], $p[3], isset( $p[4] ) ? $p[4] : '' );
	if ( $id ) {
		$photo_ids[ $p[1] ] = $id;
	}
}

// --------------------------------------------------------------- posters ---
// The picture on each event's card and page. The bar's own flyers where they
// have one, their own photographs where they don't.
//
// Wednesday and Friday karaoke used to carry a stock studio shot of a model
// singing into a microphone - it came off the old site. It is now the bar's own
// stage. Monday Night Football used to carry a close-up of a pool break, which
// made a football night look like a pool night; it is now the wall of TVs.

$posters = array(
	array( 'thursday-karaoke.jpg', 'louies-flyer-thursday-karaoke', 'Thursday Night Karaoke', 'Thursday Night Karaoke flyer, 9pm to 1:30am, Louie\'s Cocktail Lounge, 3030 Mather Field Rd', array( 'thursday-night-karaoke' ) ),
	array( 'saturday-karaoke.jpg', 'louies-flyer-saturday-karaoke', 'Saturday Night Karaoke', 'Saturday Night Karaoke flyer, 9pm to 1:30am, Louie\'s Cocktail Lounge, Rancho Cordova', array( 'saturday-night-karaoke' ) ),
	array( '8-ball-tournament.jpg', 'louies-flyer-8-ball', '8-Ball Pool Tournament', 'Flyer for the 8-ball pool tournament, last Saturday of the month at 3pm, $10 buy-in', array( 'saturday-8-ball-tournament' ) ),
	array( 'bingo-night.jpg', 'louies-flyer-bingo', 'Tuesday Night Bingo', 'Tuesday Night Bingo flyer, 7:30pm to 10pm', array( 'tuesday-night-bingo' ) ),
	array( 'geo-jam.jpg', 'louies-flyer-geo-jam', 'Geo Jam Open Mic', 'Geo Jam open mic flyer, every last Saturday, 3pm to 6pm, all musicians welcome', array( 'geo-jam' ) ),
	array( 'mr-purple.jpg', 'louies-flyer-mr-purple', 'Mr. Purple - Saturday 12 September', 'Flyer for Mr. Purple playing Louie\'s on Saturday 12 September, 3pm to 6pm', array( 'mr-purple' ) ),
	array( 'chili-cook-off.jpg', 'louies-flyer-chili-cook-off', 'Chili Cook-Off - Friday 25 September', 'Flyer for the Louie\'s chili cook-off on 25 September 2026', array( 'chili-cook-off' ) ),
);

foreach ( $posters as $p ) {
	$id = louies_import( $root . '/posters/' . $p[0], $p[1], $p[2], $p[3] );
	foreach ( $p[4] as $event_slug ) {
		louies_set_poster( $event_slug, $id );
	}
}

// Events with no flyer of their own borrow a photograph already in the library.
louies_set_poster( 'wednesday-night-karaoke', $karaoke_id );
louies_set_poster( 'friday-night-karaoke', $karaoke_id );
louies_set_poster( 'monday-night-football', $tvs_id );

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

WP_CLI::success( sprintf( 'media done: %d photos, %d posters, gallery and logo set', count( $photo_ids ), count( $posters ) ) );
