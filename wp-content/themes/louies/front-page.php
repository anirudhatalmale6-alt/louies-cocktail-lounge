<?php
/**
 * Front page.
 *
 * Order is deliberate and mobile-first: what's on tonight, then the weekly
 * regulars, then what's coming up, then the room itself, then photos, and
 * the map and phone number anchored at the very bottom.
 */

get_header();

$tonight = louies_tonight();

// "Next up" is for the one-offs and the monthly nights, and ONLY those. The
// weekly regulars have their own grid below.
//
// This used to fall back to including the weeklies whenever fewer than three
// one-offs were booked, on the theory that a thin row looked unfinished. It
// doesn't - it just repeats the grid directly above it and buries the one date
// that is actually news. One real event beats six filler cards.
$upcoming = louies_upcoming( 6, array( 'unique' => true, 'skip_weekly' => true ) );

$week      = louies_weekly_grid();
$today_dow = (int) current_datetime()->format( 'w' );
$day_names = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
$hero_img  = louies_hero_image();
$gallery   = louies_gallery_photos( 8 );
?>

<section class="hero" style="--hero-image:url('<?php echo esc_url( $hero_img ); ?>')">
	<div class="wrap">
		<p class="eyebrow" style="color:var(--butter)"><?php esc_html_e( 'Rancho Cordova &middot; One block off Highway 50, exit 15', 'louies' ); ?></p>

		<h1 class="hero-title">
			<?php esc_html_e( 'Louie\'s', 'louies' ); ?>
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
			<a class="btn btn-coral" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php echo esc_html( louies_option( 'phone' ) ); ?></a>
			<a class="btn btn-ghost" href="<?php echo esc_url( louies_directions_link() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Directions', 'louies' ); ?></a>
		</div>
	</div>
</section>

<section class="fact-strip">
	<div class="wrap">
		<dl class="fact-cards">
			<div class="fact-card">
				<dt><?php esc_html_e( 'Open daily', 'louies' ); ?></dt>
				<dd><?php echo wp_kses_post( louies_option( 'hours' ) ); ?><small><?php esc_html_e( 'Seven days a week, all year.', 'louies' ); ?></small></dd>
			</div>
			<div class="fact-card">
				<dt><?php esc_html_e( 'Happy hour', 'louies' ); ?></dt>
				<dd><?php echo wp_kses_post( louies_option( 'happy_hour' ) ); ?><small><?php esc_html_e( 'Twice a day, every day.', 'louies' ); ?></small></dd>
			</div>
			<div class="fact-card">
				<dt><?php esc_html_e( 'Find us', 'louies' ); ?></dt>
				<dd>
					<a href="<?php echo esc_url( louies_directions_link() ); ?>" target="_blank" rel="noopener">
						<?php echo esc_html( louies_option( 'address_1' ) ); ?>
						<small><?php echo esc_html( louies_option( 'address_2' ) ); ?> &middot; <?php esc_html_e( '100 free parking spaces', 'louies' ); ?></small>
					</a>
				</dd>
			</div>
			<div class="fact-card">
				<dt><?php esc_html_e( 'Call the bar', 'louies' ); ?></dt>
				<dd>
					<a href="<?php echo esc_url( louies_phone_link() ); ?>">
						<?php echo esc_html( louies_option( 'phone' ) ); ?>
						<small><?php esc_html_e( 'Tap to call &mdash; someone always picks up.', 'louies' ); ?></small>
					</a>
				</dd>
			</div>
		</dl>
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

<section class="section" id="week">
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
						<p class="week-quiet"><?php esc_html_e( 'Bar, TVs &amp; pool', 'louies' ); ?></p>
					<?php endif; ?>
				</div>
			<?php endfor; ?>
		</div>
	</div>
</section>

<section class="section section-sand" id="upcoming">
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

<?php get_template_part( 'parts/sports' ); ?>

<?php get_template_part( 'parts/food' ); ?>

<?php if ( $gallery ) : ?>
	<section class="section" id="photos">
		<div class="wrap">
			<div class="section-head section-head-row">
				<div>
					<p class="eyebrow"><?php esc_html_e( 'Have a look round', 'louies' ); ?></p>
					<h2><?php esc_html_e( 'Inside Louie\'s', 'louies' ); ?></h2>
				</div>
				<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( home_url( '/gallery/' ) ); ?>"><?php esc_html_e( 'More photos', 'louies' ); ?></a>
			</div>

			<div class="photo-grid">
				<?php foreach ( $gallery as $i => $photo ) : ?>
					<figure class="<?php echo 0 === $i ? 'is-wide' : ''; ?>">
						<img src="<?php echo esc_url( $photo['url'] ); ?>" alt="<?php echo esc_attr( $photo['alt'] ); ?>" loading="lazy">
						<?php if ( $photo['caption'] ) : ?>
							<figcaption><?php echo esc_html( $photo['caption'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="section section-plum on-plum" id="the-room">
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
				<p><?php esc_html_e( 'No full kitchen, no fuss. Hot links, Angus cheeseburgers, hot dogs, Philly cheesesteaks, and snacks to soak up the night.', 'louies' ); ?></p>
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

<?php
// Map and phone anchored at the very bottom of the home page, as requested.
get_template_part( 'parts/find-us' );
?>

<?php get_footer(); ?>
