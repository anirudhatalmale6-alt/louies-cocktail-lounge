<?php
/**
 * Front page.
 *
 * Order is deliberate and mobile-first: what's on tonight, then the weekly
 * regulars, then what's coming up, then the room itself. Someone deciding
 * where to go gets their answer before they scroll.
 */

get_header();

$tonight = louies_tonight();

// "Next up" is for the one-offs and the monthly nights. The weekly regulars
// already have their own grid below, and three identical karaoke cards in a
// row helps nobody.
$upcoming = louies_upcoming( 6, array( 'unique' => true, 'skip_weekly' => true ) );
if ( count( $upcoming ) < 3 ) {
	$upcoming = louies_upcoming( 6, array( 'unique' => true ) );
}

$week        = louies_weekly_grid();
$today_dow   = (int) current_datetime()->format( 'w' );
$day_names   = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
$hero_image  = get_theme_file_uri( 'assets/img/hero.jpg' );
?>

<section class="hero" style="--hero-image:url('<?php echo esc_url( $hero_image ); ?>')">
	<div class="wrap">
		<p class="eyebrow"><?php esc_html_e( 'Rancho Cordova &middot; Just off Highway 50', 'louies' ); ?></p>

		<h1 class="hero-title">
			<span class="neon"><?php esc_html_e( 'Louie\'s', 'louies' ); ?></span>
			<span class="script"><?php esc_html_e( 'Cocktail Lounge', 'louies' ); ?></span>
		</h1>

		<ul class="hero-tags">
			<li><?php esc_html_e( 'Karaoke', 'louies' ); ?></li>
			<li><?php esc_html_e( 'Live Music', 'louies' ); ?></li>
			<li><?php esc_html_e( 'Sports Bar', 'louies' ); ?></li>
			<li><?php esc_html_e( 'Pool &amp; Darts', 'louies' ); ?></li>
			<li><?php esc_html_e( 'Patio', 'louies' ); ?></li>
		</ul>

		<div class="hero-actions">
			<a class="btn" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php esc_html_e( 'Call the bar', 'louies' ); ?></a>
			<a class="btn btn-ghost" href="<?php echo esc_url( louies_directions_link() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Directions', 'louies' ); ?></a>
		</div>

		<div class="hero-facts">
			<div><strong><?php esc_html_e( 'Open daily', 'louies' ); ?></strong> &middot; <?php echo wp_kses_post( louies_option( 'hours' ) ); ?></div>
			<div><strong><?php esc_html_e( 'Happy hour', 'louies' ); ?></strong> &middot; <?php echo wp_kses_post( louies_option( 'happy_hour' ) ); ?></div>
			<div><strong><?php echo esc_html( louies_option( 'address_1' ) ); ?></strong> <?php echo esc_html( louies_option( 'address_2' ) ); ?></div>
			<div><strong><?php esc_html_e( 'Free parking', 'louies' ); ?></strong> &middot; <?php esc_html_e( '100 secure spaces out front', 'louies' ); ?></div>
		</div>
	</div>
</section>

<section class="tonight">
	<div class="wrap">
		<div class="tonight-card">
			<div class="tonight-head">
				<h2><?php esc_html_e( 'Tonight at Louie\'s', 'louies' ); ?></h2>
				<span class="tonight-date"><?php echo esc_html( current_datetime()->format( 'l, F j' ) ); ?></span>
			</div>

			<?php if ( $tonight ) : ?>
				<ul class="tonight-list">
					<?php foreach ( $tonight as $o ) : ?>
						<li>
							<span class="tonight-time"><?php echo wp_kses_post( louies_format_time( $o['time_start'], $o['time_end'] ) ); ?></span>
							<span class="tonight-name"><a href="<?php echo esc_url( get_permalink( $o['post_id'] ) ); ?>"><?php echo esc_html( get_the_title( $o['post_id'] ) ); ?></a></span>
							<?php if ( $o['price'] ) : ?>
								<span class="tonight-price"><?php echo esc_html( $o['price'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="tonight-empty">
					<?php esc_html_e( 'No scheduled event tonight &mdash; but the bar is open, the TVs are on and the pool tables are free.', 'louies' ); ?>
				</p>
			<?php endif; ?>

			<?php if ( louies_is_happy_hour() ) : ?>
				<span class="hh-flag"><?php esc_html_e( 'Happy hour is on right now', 'louies' ); ?></span>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="section section-alt" id="week">
	<div class="wrap">
		<div class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Every single week', 'louies' ); ?></p>
			<h2><?php esc_html_e( 'The regular line-up', 'louies' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'These run week in, week out. Special events and live bands go on top.', 'louies' ); ?></p>
		</div>

		<div class="week">
			<?php for ( $d = 1; $d <= 7; $d++ ) :
				$dow = $d % 7; // Start the week on Monday, finish on Sunday.
				?>
				<div class="week-day <?php echo $dow === $today_dow ? 'is-today' : ''; ?>">
					<h3 class="week-name"><?php echo esc_html( substr( $day_names[ $dow ], 0, 3 ) ); ?></h3>
					<?php if ( ! empty( $week[ $dow ] ) ) : ?>
						<ul class="week-list">
							<?php foreach ( $week[ $dow ] as $item ) : ?>
								<li>
									<b><?php echo esc_html( get_the_title( $item['post'] ) ); ?></b>
									<span><?php echo wp_kses_post( louies_format_time( $item['meta']['louies_time_start'], $item['meta']['louies_time_end'] ) ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p class="week-quiet"><?php esc_html_e( 'Bar &amp; TVs', 'louies' ); ?></p>
					<?php endif; ?>
				</div>
			<?php endfor; ?>
		</div>
	</div>
</section>

<section class="section" id="upcoming">
	<div class="wrap">
		<div class="section-head section-head-row">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'What\'s coming', 'louies' ); ?></p>
				<h2><?php esc_html_e( 'Next up', 'louies' ); ?></h2>
			</div>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( home_url( '/events/' ) ); ?>"><?php esc_html_e( 'Full calendar', 'louies' ); ?></a>
		</div>

		<?php if ( $upcoming ) : ?>
			<div class="event-grid">
				<?php foreach ( $upcoming as $o ) : get_template_part( 'parts/event-card', null, array( 'occurrence' => $o ) ); endforeach; ?>
			</div>
		<?php else : ?>
			<p class="lede"><?php esc_html_e( 'Nothing on the calendar just yet. Give us a call and we\'ll tell you what\'s happening.', 'louies' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<section class="section section-alt" id="the-room">
	<div class="wrap">
		<div class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'The room', 'louies' ); ?></p>
			<h2><?php esc_html_e( 'What you\'ll find inside', 'louies' ); ?></h2>
		</div>

		<div class="tiles">
			<div class="tile">
				<h3><?php esc_html_e( 'Every game, every screen', 'louies' ); ?></h3>
				<ul>
					<li><?php esc_html_e( '12 &times; 55" widescreens', 'louies' ); ?></li>
					<li><?php esc_html_e( '1 &times; 70" widescreen', 'louies' ); ?></li>
					<li><?php esc_html_e( '1 &times; 110" projector', 'louies' ); ?></li>
					<li><?php esc_html_e( 'Satellite TV &mdash; NFL, MLB, NBA, soccer, golf', 'louies' ); ?></li>
				</ul>
			</div>

			<div class="tile">
				<h3><?php esc_html_e( 'Outdoor patio', 'louies' ); ?></h3>
				<ul>
					<li><?php esc_html_e( '55" screen outside', 'louies' ); ?></li>
					<li><?php esc_html_e( 'Surround heaters for winter', 'louies' ); ?></li>
					<li><?php esc_html_e( 'Overhead misters for summer', 'louies' ); ?></li>
					<li><?php esc_html_e( 'Smoking and drinking friendly', 'louies' ); ?></li>
				</ul>
			</div>

			<div class="tile">
				<h3><?php esc_html_e( 'Pool, darts &amp; pinball', 'louies' ); ?></h3>
				<p><?php esc_html_e( 'Professional tables, a dartboard and classic pinball. Saturday 8-ball tournament at 3pm, $10 buy-in, winner takes all.', 'louies' ); ?></p>
			</div>

			<div class="tile">
				<h3><?php esc_html_e( 'Good and simple food', 'louies' ); ?></h3>
				<p><?php esc_html_e( 'No full kitchen, no fuss. Hot pastrami and cheese, Angus cheeseburgers, Philly cheesesteaks, and snacks to soak up the night.', 'louies' ); ?></p>
				<p><a href="<?php echo esc_url( home_url( '/menu/' ) ); ?>"><?php esc_html_e( 'See the full menu &rarr;', 'louies' ); ?></a></p>
			</div>

			<div class="tile">
				<h3><?php esc_html_e( 'ATM, WiFi &amp; parking', 'louies' ); ?></h3>
				<p><?php esc_html_e( 'Cash machine on site, free WiFi (ask the bartender), and a huge free lot with 100 camera-secured spaces.', 'louies' ); ?></p>
			</div>

			<div class="tile">
				<h3><?php esc_html_e( 'House rules', 'louies' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Dress code enforced Fri &amp; Sat after 9pm', 'louies' ); ?></li>
					<li><?php esc_html_e( 'IDs checked from 9pm Fri &amp; Sat', 'louies' ); ?></li>
					<li><?php esc_html_e( 'Last call 1:30am, complimentary water', 'louies' ); ?></li>
				</ul>
				<p style="margin-top:.7rem"><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'Full rules &amp; dress code &rarr;', 'louies' ); ?></a></p>
			</div>
		</div>
	</div>
</section>

<section class="section" id="find-us">
	<div class="wrap">
		<div class="split split-wide">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Find us', 'louies' ); ?></p>
				<h2><?php esc_html_e( 'One block off exit 15', 'louies' ); ?></h2>
				<p class="lede">
					<?php esc_html_e( 'Straight off Highway 50, one block north of Mather Field Rd. Easy on, easy off, and a hundred parking spaces right in front &mdash; no crawling through city traffic to get here.', 'louies' ); ?>
				</p>
				<p style="font-family:var(--display);font-size:1.5rem;font-weight:800;line-height:1.2;margin-bottom:1.2rem">
					<?php echo esc_html( louies_option( 'address_1' ) ); ?><br>
					<?php echo esc_html( louies_option( 'address_2' ) ); ?>
				</p>
				<div class="hero-actions">
					<a class="btn" href="<?php echo esc_url( louies_directions_link() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Open in maps', 'louies' ); ?></a>
					<a class="btn btn-ghost" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php echo esc_html( louies_option( 'phone' ) ); ?></a>
				</div>
			</div>

			<div class="map-frame">
				<iframe src="<?php echo esc_url( louies_map_embed_src() ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php esc_attr_e( 'Map to Louie\'s Cocktail Lounge', 'louies' ); ?>"></iframe>
			</div>
		</div>
	</div>
</section>

<?php get_footer(); ?>
