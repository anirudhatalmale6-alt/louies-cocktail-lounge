<?php
/**
 * Louie's Cocktail Lounge theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LOUIES_VERSION', '1.0.0' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 480, 'flex-height' => true, 'flex-width' => true ) );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'louies' ),
		'footer'  => __( 'Footer Menu', 'louies' ),
	) );

	add_image_size( 'louies-card', 900, 506, true );
	add_image_size( 'louies-hero', 1600, 900, true );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'louies-fonts',
		'https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@400;600;700;800;900&family=Archivo:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Yellowtail&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'louies-main', get_theme_file_uri( 'assets/css/main.css' ), array( 'louies-fonts' ), LOUIES_VERSION );
	wp_enqueue_script( 'louies-main', get_theme_file_uri( 'assets/js/main.js' ), array(), LOUIES_VERSION, true );
} );

add_action( 'wp_head', function () {
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1 );

/**
 * The trading hours the open/closed light runs off, as "HH:MM".
 *
 * Separate little functions because these two values are needed in two places
 * that must not be allowed to disagree: PHP renders the light server-side, and
 * the same numbers get handed to the browser as data attributes so JavaScript
 * can re-check it. One source, two consumers.
 */
function louies_open_time() {
	return (string) apply_filters( 'louies_open_time', '06:00' );
}

function louies_close_time() {
	return (string) apply_filters( 'louies_close_time', '02:00' );
}

/**
 * The happy hour windows, as pairs of "HH:MM".
 */
function louies_happy_hours() {
	return (array) apply_filters( 'louies_happy_hours', array( array( '06:00', '10:00' ), array( '16:00', '19:00' ) ) );
}

/**
 * Is the bar open right now? Hours are stored as plain text for the humans,
 * so the open/closed light reads from a separate pair of times.
 *
 * NOTE: this answer is only true at the moment the page is BUILT. Almost every
 * host puts a page cache in front of WordPress, and the static preview is flat
 * files, so a server-rendered light gets frozen at whatever it said when the
 * page was generated - which is how the bar ended up advertising "Closed" all
 * evening. This still runs, because it is the correct no-JavaScript fallback
 * and it is what search engines read, but assets/js/main.js re-checks it in the
 * browser and corrects it. Treat this as the first guess, not the answer.
 */
function louies_is_open_now() {
	$open  = louies_open_time();
	$close = louies_close_time();

	$now     = current_datetime();
	$minutes = ( (int) $now->format( 'G' ) * 60 ) + (int) $now->format( 'i' );

	list( $oh, $om ) = array_map( 'intval', explode( ':', $open ) );
	list( $ch, $cm ) = array_map( 'intval', explode( ':', $close ) );
	$open_m  = ( $oh * 60 ) + $om;
	$close_m = ( $ch * 60 ) + $cm;

	// Closing time is after midnight, so the open window wraps around the day.
	if ( $close_m <= $open_m ) {
		return $minutes >= $open_m || $minutes < $close_m;
	}
	return $minutes >= $open_m && $minutes < $close_m;
}

/**
 * Happy hour windows, for the little "on now" flag.
 */
function louies_is_happy_hour() {
	$windows = louies_happy_hours();
	$now     = current_datetime();
	$minutes = ( (int) $now->format( 'G' ) * 60 ) + (int) $now->format( 'i' );

	foreach ( $windows as $w ) {
		list( $sh, $sm ) = array_map( 'intval', explode( ':', $w[0] ) );
		list( $eh, $em ) = array_map( 'intval', explode( ':', $w[1] ) );
		if ( $minutes >= ( $sh * 60 + $sm ) && $minutes < ( $eh * 60 + $em ) ) {
			return true;
		}
	}
	return false;
}

/**
 * The bar's timezone as an IANA name the browser's Intl API will accept, or ''.
 *
 * WordPress lets a site be configured with a bare UTC offset ("UTC+5:30")
 * instead of a real zone, and wp_timezone_string() then hands back "+05:30".
 * Intl.DateTimeFormat rejects that, so return nothing rather than something
 * that will throw - the JavaScript falls back to what PHP rendered.
 *
 * An offset would be the wrong answer here anyway: Rancho Cordova moves an hour
 * twice a year, and a hard-coded -08:00 would have the bar opening at 7am for
 * eight months of the year.
 */
function louies_timezone_name() {
	$tz = wp_timezone_string();
	return ( false !== strpos( $tz, '/' ) ) ? $tz : '';
}

/**
 * What's on today, so the front page can lead with it.
 */
function louies_tonight() {
	$today = current_datetime()->format( 'Y-m-d' );
	return louies_get_occurrences( $today, $today );
}

/**
 * The regular weekly line-up, one column per weekday.
 * Reads the real events rather than a hand-typed list, so it can never drift.
 */
function louies_weekly_grid() {
	$grid = array_fill( 0, 7, array() );

	foreach ( get_posts( array(
		'post_type'      => 'louies_event',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'meta_query'     => array( array( 'key' => '_louies_repeat', 'value' => 'weekly' ) ),
	) ) as $post ) {
		$m    = louies_event_meta( $post->ID );
		$days = louies_weekday_list( $m['louies_weekdays'] );
		foreach ( $days as $d ) {
			if ( isset( $grid[ $d ] ) ) {
				$grid[ $d ][] = array( 'post' => $post, 'meta' => $m );
			}
		}
	}

	foreach ( $grid as &$day ) {
		usort( $day, function ( $a, $b ) {
			return strcmp( $a['meta']['louies_time_start'], $b['meta']['louies_time_start'] );
		} );
	}
	return $grid;
}

/**
 * Hero background. The bar can swap it from Bar Details without touching code.
 */
function louies_hero_image() {
	$id = (int) louies_option( 'hero_image_id', 0 );
	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, 'louies-hero' );
		if ( $url ) {
			return $url;
		}
	}
	return get_theme_file_uri( 'assets/img/hero.jpg' );
}

/**
 * A picture chosen in Bar Details, with a sensible fallback if the setting is
 * blank or points at something that has been deleted.
 */
function louies_photo_url( $setting, $fallback_id = 0, $size = 'louies-card' ) {
	$id = (int) louies_option( $setting, 0 );
	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, $size );
		if ( $url ) {
			return $url;
		}
	}
	if ( $fallback_id ) {
		$url = wp_get_attachment_image_url( $fallback_id, $size );
		if ( $url ) {
			return $url;
		}
	}
	return get_theme_file_uri( 'assets/img/hero.jpg' );
}

/**
 * Photos of the room, for the front-page strip and the gallery page.
 *
 * Reads a "Gallery" media category if the bar has tagged photos into one,
 * otherwise falls back to the newest images in the media library. Either way
 * it never returns an event poster - those are artwork, not photographs of
 * the place, and mixing them makes the bar look like a flyer.
 */
function louies_gallery_photos( $limit = 8 ) {
	$ids = array_filter( array_map( 'intval', explode( ',', (string) louies_option( 'gallery_ids' ) ) ) );

	$photos = array();
	foreach ( array_slice( $ids, 0, $limit ) as $id ) {
		$url = wp_get_attachment_image_url( $id, 'louies-card' );
		if ( ! $url ) {
			continue;
		}
		$photos[] = array(
			'url'     => $url,
			'alt'     => get_post_meta( $id, '_wp_attachment_image_alt', true ) ?: get_the_title( $id ),
			'caption' => wp_get_attachment_caption( $id ),
		);
	}
	return $photos;
}

/**
 * Poster image for an event, falling back to the first image in its type.
 */
function louies_event_image( $post_id, $size = 'louies-card' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail_url( $post_id, $size );
	}
	return '';
}

function louies_menu_sections() {
	return get_terms( array(
		'taxonomy'   => 'louies_menu_section',
		'hide_empty' => true,
		'orderby'    => 'term_order',
	) );
}

/**
 * Contact form. Deliberately tiny - no plugin, no spam vector beyond a honeypot.
 */
add_action( 'admin_post_nopriv_louies_contact', 'louies_handle_contact' );
add_action( 'admin_post_louies_contact', 'louies_handle_contact' );

function louies_handle_contact() {
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );

	if ( ! isset( $_POST['louies_contact_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['louies_contact_nonce'] ), 'louies_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'sent', 'error', $back ) );
		exit;
	}

	// Honeypot: a real person never fills a hidden field.
	if ( ! empty( $_POST['louies_website'] ) ) {
		wp_safe_redirect( add_query_arg( 'sent', 'ok', $back ) );
		exit;
	}

	$name    = sanitize_text_field( wp_unslash( $_POST['louies_name'] ?? '' ) );
	$email   = sanitize_email( wp_unslash( $_POST['louies_email'] ?? '' ) );
	$phone   = sanitize_text_field( wp_unslash( $_POST['louies_phone'] ?? '' ) );
	$subject = sanitize_text_field( wp_unslash( $_POST['louies_subject'] ?? 'Website enquiry' ) );
	$message = sanitize_textarea_field( wp_unslash( $_POST['louies_message'] ?? '' ) );

	if ( ! $name || ! $message || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'sent', 'invalid', $back ) );
		exit;
	}

	// The private-events form posts a few extra fields. Anything sent has to end
	// up in the email - a field that silently disappears is worse than no field.
	$extra = array(
		'Date wanted' => sanitize_text_field( wp_unslash( $_POST['louies_event_date'] ?? '' ) ),
		'Guests'      => sanitize_text_field( wp_unslash( $_POST['louies_guests'] ?? '' ) ),
		'Occasion'    => sanitize_text_field( wp_unslash( $_POST['louies_event_type'] ?? '' ) ),
	);

	$to   = louies_option( 'email' );
	$body = "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\n";
	foreach ( array_filter( $extra ) as $label => $value ) {
		$body .= "{$label}: {$value}\n";
	}
	$body .= "\n{$message}\n";

	$sent = wp_mail(
		$to,
		'[Louie\'s website] ' . $subject,
		$body,
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);

	wp_safe_redirect( add_query_arg( 'sent', $sent ? 'ok' : 'error', $back ) );
	exit;
}

/**
 * Keep the admin bar off the front end for logged-in staff - it confuses people
 * who only ever log in to add a band.
 */
add_filter( 'show_admin_bar', function ( $show ) {
	return current_user_can( 'manage_options' ) ? $show : false;
} );

/**
 * Trim WordPress noise the bar will never use.
 */
add_action( 'init', function () {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );

	// The emoji detection script is a separate JS request on every page load to
	// decide whether the browser can draw emoji. It can. Nothing on this site
	// uses them, and it is the only script here that isn't ours.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
} );

/**
 * Fall back to the site title when no logo has been set.
 */
function louies_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	printf(
		'<a class="site-logo-text" href="%s"><span class="logo-main">Louie\'s</span><span class="logo-sub">Cocktail Lounge</span></a>',
		esc_url( home_url( '/' ) )
	);
}
