<?php
/**
 * Seeds the demo with the real content recovered from the archived site.
 * Run with: wp eval-file seed.php
 *
 * Safe to run twice - everything is matched on slug and updated in place.
 */

if ( ! defined( 'WP_CLI' ) ) {
	die( "Run this through wp-cli.\n" );
}

// ---------------------------------------------------------------- helpers ---

function seed_page( $slug, $title, $content = '', $template = '' ) {
	$existing = get_page_by_path( $slug );
	$args     = array(
		'post_type'    => 'page',
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_content' => $content,
		'post_status'  => 'publish',
	);
	if ( $existing ) {
		$args['ID'] = $existing->ID;
		$id = wp_update_post( $args );
	} else {
		$id = wp_insert_post( $args );
	}
	if ( $template ) {
		update_post_meta( $id, '_wp_page_template', $template );
	}
	WP_CLI::log( "page: {$slug} (#{$id})" );
	return $id;
}

function seed_event( $slug, $title, $meta, $content = '', $types = array() ) {
	$existing = get_posts( array( 'post_type' => 'louies_event', 'name' => $slug, 'posts_per_page' => 1, 'post_status' => 'any' ) );
	$args     = array(
		'post_type'    => 'louies_event',
		'post_name'    => $slug,
		'post_title'   => $title,
		'post_content' => $content,
		'post_status'  => 'publish',
	);
	if ( $existing ) {
		$args['ID'] = $existing[0]->ID;
		$id = wp_update_post( $args );
	} else {
		$id = wp_insert_post( $args );
	}

	foreach ( louies_event_fields() as $key => $default ) {
		$short = ltrim( $key, '_' );
		update_post_meta( $id, $key, $meta[ $short ] ?? $default );
	}
	if ( $types ) {
		wp_set_object_terms( $id, $types, 'louies_event_type' );
	}
	WP_CLI::log( "event: {$slug} (#{$id})" );
	return $id;
}

function seed_section( $slug, $name, $description = '' ) {
	$term = get_term_by( 'slug', $slug, 'louies_menu_section' );
	if ( $term ) {
		wp_update_term( $term->term_id, 'louies_menu_section', array( 'name' => $name, 'description' => $description ) );
		return $term->term_id;
	}
	$new = wp_insert_term( $name, 'louies_menu_section', array( 'slug' => $slug, 'description' => $description ) );
	return is_wp_error( $new ) ? 0 : $new['term_id'];
}

function seed_item( $section_slug, $title, $price = '', $desc = '', $order = 0 ) {
	$slug     = sanitize_title( $section_slug . '-' . $title );
	$existing = get_posts( array( 'post_type' => 'louies_menu_item', 'name' => $slug, 'posts_per_page' => 1, 'post_status' => 'any' ) );
	$args     = array(
		'post_type'   => 'louies_menu_item',
		'post_name'   => $slug,
		'post_title'  => $title,
		'post_status' => 'publish',
		'menu_order'  => $order,
	);
	if ( $existing ) {
		$args['ID'] = $existing[0]->ID;
		$id = wp_update_post( $args );
	} else {
		$id = wp_insert_post( $args );
	}
	update_post_meta( $id, '_louies_price', $price );
	update_post_meta( $id, '_louies_desc', $desc );
	wp_set_object_terms( $id, array( $section_slug ), 'louies_menu_section' );
	return $id;
}

/**
 * Take something back off the site.
 *
 * Matching on slug and updating in place makes this script safe to re-run, but
 * it also means an item can never LEAVE the site - a second run would simply
 * skip a row that is no longer in the list and the old post would sit there
 * forever. Anything the bar has retired has to be named explicitly.
 */
function seed_retire( $post_type, $slugs ) {
	foreach ( (array) $slugs as $slug ) {
		$found = get_posts( array( 'post_type' => $post_type, 'name' => $slug, 'posts_per_page' => 1, 'post_status' => 'any' ) );
		if ( $found ) {
			wp_delete_post( $found[0]->ID, true );
			WP_CLI::log( "retired: {$post_type} {$slug}" );
		}
	}
}

function seed_retire_item( $section_slug, $title ) {
	seed_retire( 'louies_menu_item', sanitize_title( $section_slug . '-' . $title ) );
}

// ------------------------------------------------------------------ pages ---

$home = seed_page( 'home', 'Home' );

seed_page( 'events', "What's On", "Karaoke four nights a week, live bands, bingo, pool tournaments and the big fights. Everything below is what's actually booked.", 'page-events.php' );

seed_page( 'menu', 'Food & Drink', "Two happy hours a day, a different special every night of the week, and a back bar that runs deeper than most places twice our size.", 'page-menu.php' );

seed_page( 'about', 'About Us', <<<'HTML'
<p>Louie's Cocktail Lounge is a karaoke bar, live music venue and sports bar in <strong>Rancho Cordova</strong>, one block north of Mather Field Rd just off Highway 50 at exit 15. Our doors are open from 6am to 2am, every single day of the year.</p>

<p>Two happy hours daily &mdash; 6 to 10 in the morning and 4 to 7 in the evening &mdash; with a different drink special every night of the week. Our stage is worked by some of the best KJs in Sacramento, and with a professional live stage and a proper dance floor, the spotlight is yours for the taking.</p>

<p>Thirteen HD widescreens for the big game, professional pool tables, darts, pinball and electronic game machines. Surround sound from the Music Box, a heated outdoor patio with misters for summer, and a hundred camera-secured parking spaces right in front.</p>

<h2>What's on through the week</h2>
<ul>
<li><strong>Karaoke</strong> Wednesday through Saturday, 9pm to 1:30am, no cover.</li>
<li><strong>Bingo</strong> every Tuesday at 7:30pm, free to play.</li>
<li><strong>8-ball pool tournament</strong> every Saturday at 3pm, $10 buy-in, winner takes all.</li>
<li><strong>Live bands and DJs</strong> most weekends &mdash; see the calendar for who's playing.</li>
<li><strong>Every game</strong> &mdash; NFL, MLB, NBA, NCAA, soccer and golf on satellite.</li>
</ul>

<h2>Getting here</h2>
<p>We're the easiest bar in Sacramento County to get to. One block north of Mather Field Rd, off exit 15 on Highway 50 &mdash; easy on, easy off, and no crawling through city traffic. Free parking out front, a hundred spaces, all on camera.</p>

<h2>Booking the venue</h2>
<p>Louie's hosts birthdays, celebrations of life, club runs, car shows and company parties. If you're a band or a KJ looking for a room in the Sacramento area, get in touch &mdash; we book live entertainment most weeks.</p>

<h2>Club rules</h2>
<ul>
<li>Dress code strictly enforced Friday and Saturday nights after 9pm.</li>
<li>IDs checked by security from 9pm on Friday and Saturday. You must have valid ID to enter.</li>
<li>Last call 1:30am. Complimentary water.</li>
<li>Soda available for purchase until 1:55am.</li>
</ul>

<h2>Dress code</h2>
<ul>
<li>No durags, bandanas or hoods up.</li>
<li>No attire or colours affiliated with any gang or club, support attire included.</li>
<li>No saggy or baggy clothing. Pants and shorts must be appropriately sized and held at the waist.</li>
<li>No tank tops on men.</li>
</ul>

<p>We reserve the right to refuse service or entry to anyone. Must be 21 or over.</p>

<h2>Also good to know</h2>
<ul>
<li><strong>ATM</strong> on site if you prefer to pay cash.</li>
<li><strong>Free WiFi</strong> &mdash; ask the bartender for details.</li>
<li><strong>Parking</strong> &mdash; a huge lot with plenty of free spaces.</li>
</ul>
HTML );

seed_page( 'contact', 'Contact & Venue Hire', "<p>Quickest way to reach us is the phone &mdash; someone behind the bar will pick up. Booking a party, pitching a band, or asking about the patio? Use the form and we'll come back to you.</p>", 'page-contact.php' );

seed_page( 'gallery', 'Photos', "<p>The bar, the pool room, karaoke nights and live music at Louie's Cocktail Lounge in Rancho Cordova.</p>", 'page-gallery.php' );

seed_page( 'private-events', 'Book a Private Event', "<p>Louie&#8217;s has hosted birthdays, celebrations of life, club runs, car shows, fundraisers and company parties. The stage, the dance floor, the patio and a hundred parking spaces are all yours.</p>", 'page-private-events.php' );

seed_page( 'privacy-policy', 'Privacy Policy', "<p>We collect only what you send us through the contact form on this site: your name, email address, phone number if you give one, and your message. We use it to reply to you and nothing else. We do not sell it, share it or add you to a mailing list.</p><p>This site uses no advertising or tracking cookies. Embedded Google Maps is provided by Google and subject to their own privacy policy.</p><p>Want your details removed from our records? Call the bar and ask.</p>" );

// The front page is the theme's front-page.php, so 'home' just anchors the menu.
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
update_option( 'blogname', "Louie's Cocktail Lounge" );
update_option( 'blogdescription', 'Karaoke, live music & sports in Rancho Cordova' );
update_option( 'timezone_string', 'America/Los_Angeles' );
update_option( 'date_format', 'F j, Y' );
update_option( 'time_format', 'g:i a' );
update_option( 'permalink_structure', '/%postname%/' );

// ------------------------------------------------------------- bar details ---

// Merge rather than overwrite - image IDs differ per install and must survive
// a re-run of this script.
$louies_settings = array_merge( (array) get_option( 'louies_settings', array() ), array(
	'phone'      => '(916) 362-9151',
	'address_1'  => '3030 Mather Field Rd.',
	'address_2'  => 'Rancho Cordova, CA 95670',
	'hours'      => '6:00 am - 2:00 am daily',
	'happy_hour' => '6am - 10am and 4pm - 7pm',
	'maps_query' => '3030 Mather Field Rd, Rancho Cordova, CA 95670',
	'facebook'   => '',
	'instagram'  => '',
	'email'      => 'louiescocktails@gmail.com',
	'notice'     => '',
) );
update_option( 'louies_settings', $louies_settings );

// ------------------------------------------------------------ event types ---

foreach ( array( 'Karaoke', 'Live Music', 'Sports', 'Games & Tournaments', 'Special Event' ) as $t ) {
	if ( ! term_exists( $t, 'louies_event_type' ) ) {
		wp_insert_term( $t, 'louies_event_type' );
	}
}

// ----------------------------------------------------------------- events ---
// Dates below are start dates; the weekly ones repeat forever from there.

$start = '2026-01-01';

seed_event( 'wednesday-night-karaoke', "Wednesday Night Karaoke", array(
	'louies_date'       => $start,
	'louies_time_start' => '21:00',
	'louies_time_end'   => '01:30',
	'louies_price'      => 'FREE',
	'louies_repeat'     => 'weekly',
	'louies_weekdays'   => '3',
), "<p>The mic is yours. Some of the best KJs in Sacramento, a professional stage and a proper dance floor. Sing a classic or something off the radio this week &mdash; either way the room is on your side.</p>", array( 'Karaoke' ) );

seed_event( 'thursday-night-karaoke', "Thursday Night Karaoke", array(
	'louies_date'       => $start,
	'louies_time_start' => '21:00',
	'louies_time_end'   => '01:30',
	'louies_price'      => 'FREE',
	'louies_repeat'     => 'weekly',
	'louies_weekdays'   => '4',
), "<p>Thursday night, mic open, no cover. Crown Royal is $7 all night.</p>", array( 'Karaoke' ) );

seed_event( 'friday-night-karaoke', "Friday Night Karaoke", array(
	'louies_date'       => $start,
	'louies_time_start' => '21:00',
	'louies_time_end'   => '01:30',
	'louies_price'      => 'FREE',
	'louies_repeat'     => 'weekly',
	'louies_weekdays'   => '5',
), "<p>Friday night karaoke at Louie's. Dress code enforced after 9pm and IDs checked on the door from 9 &mdash; bring valid ID.</p>", array( 'Karaoke' ) );

seed_event( 'saturday-night-karaoke', "Saturday Night Karaoke", array(
	'louies_date'       => $start,
	'louies_time_start' => '21:00',
	'louies_time_end'   => '01:30',
	'louies_price'      => 'FREE',
	'louies_repeat'     => 'weekly',
	'louies_weekdays'   => '6',
), "<p>The big one. Saturday night, packed room, mic open until last call. Dress code enforced after 9pm.</p>", array( 'Karaoke' ) );

seed_event( 'tuesday-night-bingo', "Tuesday Night Bingo", array(
	'louies_date'       => $start,
	'louies_time_start' => '19:30',
	'louies_time_end'   => '22:00',
	'louies_price'      => 'FREE',
	'louies_repeat'     => 'weekly',
	'louies_weekdays'   => '2',
), "<p>Bingo every Tuesday at 7:30. Free to play, and Jagermeister shots are $6 all night.</p>", array( 'Games & Tournaments' ) );

seed_event( 'saturday-8-ball-tournament', "Saturday 8-Ball Tournament", array(
	'louies_date'       => $start,
	'louies_time_start' => '15:00',
	'louies_time_end'   => '17:30',
	'louies_price'      => '$10 buy-in',
	'louies_repeat'     => 'weekly',
	'louies_weekdays'   => '6',
), "<p>Every Saturday at 3pm. Ten dollars in, winner takes all. Pro tables, bragging rights and cash.</p>", array( 'Games & Tournaments' ) );

seed_event( 'sunday-football-and-pool', "Sunday Football Games and Pool", array(
	'louies_date'       => $start,
	'louies_time_start' => '10:00',
	'louies_time_end'   => '20:00',
	'louies_price'      => 'FREE',
	'louies_repeat'     => 'weekly',
	'louies_weekdays'   => '0',
), "<p>Sunday football on every screen, from the early games right through the afternoon, with the pool tables open all day. Jameson and a Modelo pint is $12 on Sundays.</p>", array( 'Sports' ) );

seed_event( 'monday-night-football', "Monday Night Football", array(
	'louies_date'       => '2026-09-07',
	'louies_time_start' => '17:00',
	'louies_time_end'   => '23:00',
	'louies_price'      => 'FREE',
	'louies_repeat'     => 'weekly',
	'louies_weekdays'   => '1',
	'louies_repeat_until' => '2027-01-11',
), "<p>Every Monday through the season on the 110-inch projector and thirteen more screens. Game-day specials at the bar. Jameson is $6 on Mondays.</p>", array( 'Sports' ) );

seed_event( 'geo-jam', "Geo Jam Open Mic", array(
	'louies_date'         => '2026-01-31',
	'louies_time_start'   => '15:00',
	'louies_time_end'     => '18:00',
	'louies_price'        => 'FREE',
	'louies_repeat'       => 'monthly',
	'louies_monthly_nth'  => 'last',
	'louies_monthly_day'  => '6',
), "<p>Last Saturday of every month. Bring an instrument, plug in, play. Free to watch, free to join in.</p>", array( 'Live Music' ) );

// One-off events. The bar clears these out when they're done - only what is
// actually booked belongs here, and a stale poster is worse than an empty
// section. Everything else on the site is a weekly or monthly repeat and looks
// after itself.
//
// Both of these came off flyers the bar sent on 03/09/2026. Every fact below is
// read off the artwork - nothing is filled in from assumption. Where the flyer
// is silent (Mr. Purple says nothing about a cover charge) the field is left
// empty rather than guessed at, because "FREE" on a website is a promise the
// door staff have to keep.

seed_event( 'mr-purple', "Mr. Purple", array(
	'louies_date'       => '2026-09-12',
	'louies_time_start' => '15:00',
	'louies_time_end'   => '18:00',
	'louies_price'      => '',
), "<p>Mr. Purple play the afternoon slot - Kris, Slinger and Jeff, three hours of live rock from 3pm.</p>", array( 'Live Music' ) );

// "5:00PM - UNTIL" on the flyer, so there is deliberately no end time set.
// The two entry fees are different things and the site has to keep them apart:
// $20 to cook, $10 to judge. Getting that backwards costs somebody money.
seed_event( 'chili-cook-off', "Chili Cook-Off", array(
	'louies_date'       => '2026-09-25',
	'louies_time_start' => '17:00',
	'louies_time_end'   => '',
	'louies_price'      => '$20 to enter &middot; $10 to judge',
), "<p>Raffles, friends, music and fun - and a pot of chili with your name on it.</p>"
 . "<p><strong>Cooking?</strong> Entry is $20 and you need to RSVP by <strong>Sunday 20 September</strong>. Speak to Janene to pay and get your spot.</p>"
 . "<p><strong>Judging?</strong> $10 on the day. Cornbread gets judged too, so bring that as well if you have a recipe worth defending.</p>",
	array( 'Special Event' ) );

// Taken down 03/09/2026 - these were recovered from old archived pages and are
// not on the books. Named explicitly so a second run of this script removes
// them rather than quietly leaving them behind.
seed_retire( 'louies_event', array(
	'shades-of-pink-floyd',
	'dj-kid-wrench',
	'girls-night-out-the-show',
	'micromania-wrestling',
	'nye-party',
) );

// ------------------------------------------------------------------- menu ---

$sections = array(
	'specials'   => array( 'Nightly Drink Specials', 'A different one every night of the week.' ),
	'happy-hour' => array( 'Happy Hour', 'Twice a day, every day: 6am to 10am and 4pm to 7pm.' ),
	'food'       => array( 'Pub Food & Munchies', 'No full kitchen, no fuss - just the right bites to go with a drink.' ),
	'draft'      => array( 'Beer On Tap', 'By the pint.' ),
	'bottles'    => array( 'Bottled Beer & Cider', '' ),
	'vodka'      => array( 'Vodka', '' ),
	'whiskey'    => array( 'Whiskey & Bourbon', 'The deepest shelf in Rancho Cordova.' ),
	'tequila'    => array( 'Tequila & Mezcal', '' ),
);
foreach ( $sections as $slug => $s ) {
	seed_section( $slug, $s[0], $s[1] );
}

$specials = array(
	array( 'Sunday &mdash; Jameson + Modelo Pint', '$12.00' ),
	array( 'Monday &mdash; Jameson', '$6.00' ),
	array( 'Tuesday &mdash; Jagermeister Shots', '$6.00' ),
	array( 'Wednesday &mdash; Kamikaze', '$6.00' ),
	array( 'Thursday &mdash; Crown Royal', '$7.00' ),
	array( 'Friday &mdash; Fireball Shots', '$5.00' ),
	array( 'Saturday &mdash; Bulleit Bourbon & Rye', '$7.00' ),
	array( 'Mumm Champagne from Napa &mdash; by the glass', '$8.00' ),
	array( 'Mumm Champagne from Napa &mdash; bottle', '$32.00' ),
);
foreach ( $specials as $i => $row ) {
	seed_item( 'specials', $row[0], $row[1], '', $i );
}

seed_item( 'happy-hour', 'Morning happy hour', '6am - 10am', 'Every day.', 0 );
seed_item( 'happy-hour', 'Evening happy hour', '4pm - 7pm', 'Every day.', 1 );

$food = array(
	array( 'Hot Link', '$7.00', '' ),
	array( 'Hot Dog', '$6.00', '' ),
	array( 'Cheeseburger', '$6.00', 'Angus. Juicy, flavorful, classic.' ),
	array( 'Philly Cheese Steak', '$6.00', 'Loaded with all the good stuff.' ),
	array( 'Pacific Gold Jerky', '$3.50', '' ),
	// One line rather than six. The bar gave a floor price for the snack rack
	// and nothing per item, and inventing the missing ones would put prices on
	// the site that nobody at Louie's has ever agreed to.
	array( 'Chips, Nuts, Slim Jims &amp; Snacks', 'from $2.00', 'Off the rack behind the bar.' ),
);
foreach ( $food as $i => $row ) {
	seed_item( 'food', $row[0], $row[1], $row[2], $i );
}

// Off the menu as of 03/09/2026.
seed_retire_item( 'food', 'Pastrami with Cheese' );
foreach ( array( "Jack's Teriyaki Beef Stick", 'Slim Jim', 'Cracker Jacks', 'Bag of Cashews', 'Bag of Peanuts', 'Bag of Chips' ) as $gone ) {
	seed_retire_item( 'food', $gone );
}

$draft = array( 'Blue Moon', 'Bud Light', 'Coors Light', 'Knee Deep Hoptologist', 'Modelo', 'Boneyard Beer', '805', 'Societe Brewing Co.' );
foreach ( $draft as $i => $name ) {
	seed_item( 'draft', $name, '', '', $i );
}

$bottles = array( 'Bud', 'Bud Light', 'Coors', 'Coors Light', 'Miller High Life', 'Modelo', 'Corona Extra', 'Pacifico', 'Stella Artois', 'Heineken', 'Heineken 0.0 (NA)', 'Guinness', 'Angry Orchard Cider', 'Boneyard Beer' );
foreach ( $bottles as $i => $name ) {
	seed_item( 'bottles', $name, '', '', $i );
}

$vodka = array( 'Absolut 80', 'Absolut Mandarin', 'Belvedere', "Burnett's Cherry", 'Grey Goose', 'Ketel One', 'Ketel One Citroen', 'Mr Black', "Seagram's Sweet Tea", 'Skyy', 'Smirnoff 80', 'Smirnoff Green Apple', 'Smirnoff Peach', 'Smirnoff Watermelon', 'Stoli 80', 'Svedka', 'Svedka Blueberry', 'Svedka Mango', "Tito's" );
foreach ( $vodka as $i => $name ) {
	seed_item( 'vodka', $name, '', '', $i );
}

$whiskey = array( '1792', "Angel's Envy Bourbon", 'Basil Hayden', "Blanton's Bourbon", 'Bulleit Bourbon', 'Bulleit Rye', 'Bushmills White Irish', 'Canadian Club', 'Contradiction Smooth Ambler', 'Crown Royal', 'Crown Royal Black', 'Crown Royal Apple', 'Dickel 8 Year', 'Dickel 12', 'Dickel Bottled in Bond', 'Dickel Rye', 'Eagle Rare Bourbon', 'Fireball Cinnamon', 'Jack Daniels No.7', 'Jack Daniels Bonded', 'Jack Daniels Rye', 'Jack Daniels Tennessee Apple', 'Jack Daniels Tennessee Fire', 'Jack Daniels Tennessee Honey', 'Jack Daniels Gentleman Jack', 'Jack Daniels Single Barrel', 'Jack Daniels Triple Mash', 'Jameson Irish Whiskey', 'Jameson Black Barrel', 'Jameson Caskmates', 'Jefferson Bourbon', 'Jim Beam White', 'Jim Beam Apple', 'Kessler', 'Knob Creek 100', 'Lot No.40 Rye', "Maker's Mark", "Maker's Mark 46", 'Monkey Shoulder', 'Nelson Brothers Reserve', 'Oban 14yr', 'Old Forester 86', 'Old Forester 100', 'Pendleton', 'Rabbit Hole', 'Redbreast Irish', "Russell's Reserve", "Seagram's 7", "Seagram's VO", "Serpent's Bite Apple Cider Whiskey", 'Skrewball Peanut Butter', 'Slane Irish', 'Southern Comfort', 'Toki Suntory', 'Tullamore Dew', 'Weller Bourbon', 'Wild Turkey 101', 'Wild Turkey Longbranch', 'Woodford Reserve', 'Woodford Reserve Rye' );
foreach ( $whiskey as $i => $name ) {
	seed_item( 'whiskey', $name, '', '', $i );
}

$tequila = array( '1800 Silver', '1800 Reposado', '1800 Anejo', 'Altos Anejo', 'Avion Silver 80', 'Avion Reposado', 'Avion Anejo', 'Avion Reserva 44', 'Casamigos Blanco', 'Cazadores Blanco', 'Cazadores Reposado', 'Cazadores Anejo', 'Cenote Blanco', 'Codigo 1530 Blanco', 'Codigo 1530 Rosa', 'Codigo 1530 Reposado', 'Codigo 1530 Anejo', 'Contraluz Cristalino Mezcal', 'Coramino Anejo', 'Cutwater Blanco', 'Cutwater Reposado', 'Del Maguey VIDA Mezcal', 'Don Julio Blanco', 'Don Julio Reposado', 'Dulce Vida Blanco', 'Dulce Vida Lime', 'Dulce Vida Grapefruit', 'Dulce Vida Reposado', 'Espolon Blanco', 'Fiero Habanero Blanco', 'Gran Centenario Reposado', 'Gran Centenario Anejo', 'Gran Centenario Cristalino', 'Herradura Silver', 'Herradura Reposado', 'Herradura Ultra Anejo', 'Herradura Legend Anejo', 'Hornitos Reposado', 'Jose Cuervo Especial Silver', 'Jose Cuervo Gold', 'Cuervo Tradicional Silver', 'Lobos 1707 Joven', 'Maestro Dobel Silver', 'Maestro Dobel Diamante', 'Milagro Blanco', 'Milagro Reposado', 'Olmeca Plata', 'Olmeca Anejo', 'Patron Silver', 'Patron Reposado', 'Patron Anejo', 'Santo Blanco', 'Teremana Blanco', 'Teremana Reposado' );
foreach ( $tequila as $i => $name ) {
	seed_item( 'tequila', $name, '', '', $i );
}

WP_CLI::success( 'Content seeded.' );
