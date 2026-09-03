<?php
/**
 * SEO: titles, descriptions, social cards and structured data.
 *
 * Deliberately small and self-contained rather than pulling in Yoast. A bar's
 * site needs local search to work - name, address, phone, hours, and the events
 * marked up so Google can show them. That's what this does, and nothing else.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The words people actually type. Used in the fallback description and the
 * "about" copy - never stuffed into hidden text, which earns a penalty.
 */
function louies_keywords() {
	return apply_filters( 'louies_keywords', array(
		'karaoke bar Rancho Cordova',
		'live music Rancho Cordova',
		'sports bar Rancho Cordova',
		'cocktail lounge Sacramento',
		'happy hour Rancho Cordova',
		'pool tables Rancho Cordova',
		'bingo night Sacramento',
		'bar near Mather Field Road',
	) );
}

/**
 * One clean description per page type.
 */
function louies_meta_description() {
	$bar  = louies_plain( get_bloginfo( 'name' ) );
	$city = 'Rancho Cordova';

	if ( is_singular( 'louies_event' ) ) {
		$m    = louies_event_meta( get_the_ID() );
		$bits = array( get_the_title() . ' at ' . $bar . ', ' . $city . '.' );

		$label = louies_repeat_label( $m );
		if ( $label ) {
			$bits[] = $label . '.';
		} elseif ( $m['louies_date'] ) {
			$bits[] = louies_format_date( $m['louies_date'], true ) . '.';
		}
		if ( $m['louies_time_start'] ) {
			$bits[] = wp_strip_all_tags( louies_format_time( $m['louies_time_start'], $m['louies_time_end'] ) ) . '.';
		}
		if ( $m['louies_price'] ) {
			$bits[] = $m['louies_price'] . '.';
		}
		$excerpt = has_excerpt() ? wp_strip_all_tags( get_the_excerpt() ) : '';
		if ( $excerpt ) {
			$bits[] = $excerpt;
		}
		return louies_trim_description( implode( ' ', $bits ) );
	}

	if ( is_page() ) {
		$page = get_post();
		if ( $page && has_excerpt( $page ) ) {
			return louies_trim_description( wp_strip_all_tags( get_the_excerpt( $page ) ) );
		}
		$slug = $page ? $page->post_name : '';
		$map  = array(
			'menu'    => 'Drink specials every night of the week, pub food, beers on tap and a deep back bar of whiskey, tequila and vodka at ' . $bar . ', ' . $city . '.',
			'events'  => 'Karaoke, live bands, bingo and pool tournaments at ' . $bar . '. See what is on this week in ' . $city . ', one block off Highway 50.',
			'about'   => 'About ' . $bar . ' - karaoke, live music, 13 screens of sport, pool, darts and a heated patio in ' . $city . ', open 6am to 2am daily.',
			'contact' => 'Call ' . louies_option( 'phone' ) . ' or message ' . $bar . '. Book the venue for a party, or ask about playing here. ' . louies_option( 'address_1' ) . ', ' . $city . '.',
			'gallery' => 'Photos of ' . $bar . ' in ' . $city . ' - the bar, the pool room, karaoke nights and live music.',
		);
		if ( isset( $map[ $slug ] ) ) {
			return louies_trim_description( $map[ $slug ] );
		}
	}

	return louies_trim_description( louies_option( 'seo_tagline' ) );
}

/**
 * Raw text, entities decoded.
 *
 * WordPress hands back "Louie&#039;s" from get_bloginfo() and friends. That is
 * right for HTML, but structured data and meta attributes must carry the real
 * apostrophe - otherwise Google indexes the business as "Louie&#039;s".
 */
function louies_plain( $text ) {
	return trim( html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES, 'UTF-8' ) );
}

function louies_trim_description( $text ) {
	$text = trim( preg_replace( '/\s+/', ' ', louies_plain( $text ) ) );
	if ( mb_strlen( $text ) <= 160 ) {
		return $text;
	}
	// Cut on a word boundary rather than mid-word.
	$cut = mb_substr( $text, 0, 157 );
	$sp  = mb_strrpos( $cut, ' ' );
	return rtrim( $sp ? mb_substr( $cut, 0, $sp ) : $cut, ' ,.;:' ) . '...';
}

/**
 * A title that reads well in a search result rather than "Home | Site".
 */
add_filter( 'document_title_parts', function ( $parts ) {
	if ( is_front_page() ) {
		$parts['title']    = get_bloginfo( 'name' );
		$parts['tagline']  = 'Karaoke, Live Music & Sports Bar in Rancho Cordova';
		unset( $parts['site'] );
	}
	return $parts;
} );

add_filter( 'document_title_separator', function () {
	return '|';
} );

/**
 * The share image: a page's own photo if it has one, else the logo card.
 */
function louies_social_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		$url = get_the_post_thumbnail_url( get_the_ID(), 'louies-hero' );
		if ( $url ) {
			return $url;
		}
	}
	$id = (int) louies_option( 'social_image_id', 0 );
	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( $url ) {
			return $url;
		}
	}
	return function_exists( 'louies_hero_image' ) ? louies_hero_image() : '';
}

add_action( 'wp_head', 'louies_head_meta', 2 );

function louies_head_meta() {
	$desc  = louies_meta_description();
	$image = louies_social_image();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );

	echo "\n<!-- Louie's SEO -->\n";

	printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $url ) );

	printf( '<meta property="og:type" content="%s">' . "\n", is_singular( 'louies_event' ) ? 'article' : 'website' );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( louies_plain( get_bloginfo( 'name' ) ) ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( louies_plain( wp_get_document_title() ) ) );
	printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $url ) );
	printf( '<meta property="og:locale" content="%s">' . "\n", esc_attr( get_locale() ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
		printf( '<meta name="twitter:image" content="%s">' . "\n", esc_url( $image ) );
	}

	printf( '<meta name="twitter:card" content="summary_large_image">' . "\n" );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( louies_plain( wp_get_document_title() ) ) );
	printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $desc ) );

	// Local-intent signals that search engines still read off the page.
	printf( '<meta name="geo.placename" content="%s">' . "\n", esc_attr( louies_option( 'address_2' ) ) );
	printf( '<meta name="geo.region" content="US-CA">' . "\n" );

	echo louies_schema_jsonld(); // phpcs:ignore WordPress.Security.EscapeOutput -- built with wp_json_encode below.
	echo "<!-- /Louie's SEO -->\n\n";
}

/**
 * Structured data. A BarOrPub for the business, plus an Event for each
 * occurrence so Google can list "what's on" directly in the results.
 */
function louies_schema_jsonld() {
	$name  = louies_plain( get_bloginfo( 'name' ) );
	$phone = louies_plain( louies_option( 'phone' ) );

	$address = array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => rtrim( louies_plain( louies_option( 'address_1' ) ), '.' ),
		'addressLocality' => 'Rancho Cordova',
		'addressRegion'   => 'CA',
		'postalCode'      => '95670',
		'addressCountry'  => 'US',
	);

	$sameas = array_values( array_filter( array( louies_option( 'facebook' ), louies_option( 'instagram' ) ) ) );

	$business = array(
		'@type'          => 'BarOrPub',
		'@id'            => home_url( '/#bar' ),
		'name'           => $name,
		'url'            => home_url( '/' ),
		'telephone'      => $phone,
		'address'        => $address,
		'description'    => louies_plain( louies_option( 'seo_tagline' ) ),
		'priceRange'     => '$',
		'smokingAllowed' => true,
		'servesCuisine'  => 'Bar food',
		'amenityFeature' => array(
			array( '@type' => 'LocationFeatureSpecification', 'name' => 'Karaoke', 'value' => true ),
			array( '@type' => 'LocationFeatureSpecification', 'name' => 'Live music', 'value' => true ),
			array( '@type' => 'LocationFeatureSpecification', 'name' => 'Pool tables', 'value' => true ),
			array( '@type' => 'LocationFeatureSpecification', 'name' => 'Darts', 'value' => true ),
			array( '@type' => 'LocationFeatureSpecification', 'name' => 'Outdoor patio', 'value' => true ),
			array( '@type' => 'LocationFeatureSpecification', 'name' => 'Free parking', 'value' => true ),
			array( '@type' => 'LocationFeatureSpecification', 'name' => 'Free WiFi', 'value' => true ),
			array( '@type' => 'LocationFeatureSpecification', 'name' => 'ATM', 'value' => true ),
		),
		// Open 6am to 2am the next day - schema handles the wrap with a closing
		// time earlier than the opening one.
		'openingHoursSpecification' => array( array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
			'opens'     => '06:00',
			'closes'    => '02:00',
		) ),
	);

	$logo = (int) louies_option( 'social_image_id', 0 );
	if ( $logo ) {
		$url = wp_get_attachment_image_url( $logo, 'full' );
		if ( $url ) {
			$business['image'] = $url;
			$business['logo']  = $url;
		}
	}
	if ( $sameas ) {
		$business['sameAs'] = $sameas;
	}

	$graph = array( $business );

	// On the home page, publish the next handful of events too.
	if ( is_front_page() ) {
		foreach ( louies_upcoming( 8, array( 'unique' => true ) ) as $o ) {
			$graph[] = louies_event_schema( $o );
		}
	}

	if ( is_singular( 'louies_event' ) ) {
		$today = louies_today();
		$dates = louies_event_dates( get_the_ID(), $today, $today->modify( '+1 year' ) );
		foreach ( array_slice( $dates, 0, 6 ) as $d ) {
			$m = louies_event_meta( get_the_ID() );
			$graph[] = louies_event_schema( array(
				'post_id'    => get_the_ID(),
				'date'       => $d->format( 'Y-m-d' ),
				'time_start' => $m['louies_time_start'],
				'time_end'   => $m['louies_time_end'],
				'price'      => $m['louies_price'],
				'ticket_url' => $m['louies_ticket_url'],
			) );
		}
	}

	$json = wp_json_encode(
		array( '@context' => 'https://schema.org', '@graph' => $graph ),
		JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
	);

	return '<script type="application/ld+json">' . $json . '</script>' . "\n";
}

/**
 * One dated occurrence as schema.org/Event.
 */
function louies_event_schema( $o ) {
	$start = $o['date'] . 'T' . ( $o['time_start'] ? $o['time_start'] : '00:00' ) . ':00';

	$event = array(
		'@type'               => 'Event',
		'name'                => louies_plain( get_the_title( $o['post_id'] ) ),
		'url'                 => get_permalink( $o['post_id'] ),
		'startDate'           => $start,
		'eventStatus'         => 'https://schema.org/EventScheduled',
		'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
		'location'            => array( '@id' => home_url( '/#bar' ) ),
		'organizer'           => array( '@id' => home_url( '/#bar' ) ),
	);

	if ( ! empty( $o['time_end'] ) ) {
		// A 1:30am finish belongs to the following day.
		$end_day = $o['date'];
		if ( ! empty( $o['time_start'] ) && $o['time_end'] < $o['time_start'] ) {
			$d = DateTimeImmutable::createFromFormat( 'Y-m-d|', $o['date'], louies_timezone() );
			if ( $d ) {
				$end_day = $d->modify( '+1 day' )->format( 'Y-m-d' );
			}
		}
		$event['endDate'] = $end_day . 'T' . $o['time_end'] . ':00';
	}

	$image = louies_event_image( $o['post_id'], 'louies-hero' );
	if ( $image ) {
		$event['image'] = $image;
	}

	$excerpt = get_the_excerpt( $o['post_id'] );
	if ( $excerpt ) {
		$event['description'] = louies_trim_description( $excerpt );
	}

	$price = isset( $o['price'] ) ? trim( (string) $o['price'] ) : '';
	if ( $price ) {
		$is_free = 0 === stripos( $price, 'free' );
		$amount  = $is_free ? '0' : preg_replace( '/[^0-9.]/', '', $price );
		if ( '' !== $amount ) {
			$event['offers'] = array(
				'@type'         => 'Offer',
				'price'         => $amount,
				'priceCurrency' => 'USD',
				'availability'  => 'https://schema.org/InStock',
				'url'           => ! empty( $o['ticket_url'] ) ? $o['ticket_url'] : get_permalink( $o['post_id'] ),
			);
		}
	}

	return $event;
}

/**
 * Keep event URLs out of the index when the event has finished, so the site
 * isn't full of dead pages competing with the live ones.
 */
add_filter( 'wp_robots', function ( $robots ) {
	if ( is_singular( 'louies_event' ) ) {
		$today = louies_today();
		$next  = louies_event_dates( get_the_ID(), $today, $today->modify( '+2 years' ) );
		if ( ! $next ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
	}
	return $robots;
} );

/**
 * Point robots.txt at the sitemap - but only if core hasn't already, otherwise
 * the file ends up listing it twice.
 */
add_filter( 'robots_txt', function ( $output ) {
	if ( false === stripos( $output, 'sitemap:' ) ) {
		$output .= "\nSitemap: " . home_url( '/wp-sitemap.xml' ) . "\n";
	}
	return $output;
}, 20, 1 );

/**
 * Author archives are pointless on a one-person bar site and only dilute the
 * pages that matter, so keep them out of the sitemap.
 */
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

/**
 * Menu items and event types are internal plumbing - keep them out of the
 * sitemap so the pages that matter aren't diluted.
 */
add_filter( 'wp_sitemaps_post_types', function ( $types ) {
	unset( $types['louies_menu_item'] );
	return $types;
} );

add_filter( 'wp_sitemaps_taxonomies', function ( $taxes ) {
	unset( $taxes['louies_menu_section'] );
	return $taxes;
} );
