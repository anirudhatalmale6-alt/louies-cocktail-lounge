<?php
/**
 * Template Name: Private Events
 *
 * Book the room. Same tiny form as Contact, but with the fields an enquiry
 * about a party actually needs - date, headcount, what it's for.
 */

get_header();

if ( have_posts() ) {
	the_post();
}

$sent = isset( $_GET['sent'] ) ? sanitize_key( wp_unslash( $_GET['sent'] ) ) : '';
$hero = louies_photo_url( 'private_image_id', 245, 'louies-hero' );
?>

<section class="hero" style="--hero-image:url('<?php echo esc_url( $hero ); ?>')">
	<div class="wrap">
		<p class="eyebrow" style="color:var(--butter)"><?php esc_html_e( 'Private events', 'louies' ); ?></p>
		<h1 class="hero-title" style="font-size:clamp(2.4rem,8vw,4.6rem)"><?php the_title(); ?></h1>
		<p class="lede" style="color:#fff;max-width:52ch;text-shadow:0 2px 14px rgba(36,16,24,.7)">
			<?php esc_html_e( 'Birthdays, celebrations of life, club runs, car shows, company parties and fundraisers. Tell us what you have in mind and we will come back to you.', 'louies' ); ?>
		</p>
		<div class="hero-actions" style="margin-top:1.4rem">
			<a class="btn btn-coral" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php echo esc_html( louies_option( 'phone' ) ); ?></a>
			<a class="btn btn-ghost" href="#enquiry"><?php esc_html_e( 'Send an enquiry', 'louies' ); ?></a>
		</div>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<div class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'What you get', 'louies' ); ?></p>
			<h2><?php esc_html_e( 'The room is yours', 'louies' ); ?></h2>
		</div>

		<div class="tiles">
			<div class="tile">
				<h3><?php esc_html_e( 'Stage &amp; dance floor', 'louies' ); ?></h3>
				<p><?php esc_html_e( 'A professional live stage, a lit dance floor and a full PA. Bring a band, a DJ, or use our karaoke rig.', 'louies' ); ?></p>
			</div>
			<div class="tile">
				<h3><?php esc_html_e( 'Thirteen screens', 'louies' ); ?></h3>
				<p><?php esc_html_e( 'Twelve 55-inch, a 70-inch and a 110-inch projector. Put the game on, or run a slideshow for the occasion.', 'louies' ); ?></p>
			</div>
			<div class="tile">
				<h3><?php esc_html_e( 'Heated patio', 'louies' ); ?></h3>
				<p><?php esc_html_e( 'Its own 55-inch screen, surround heaters for winter and overhead misters for summer. Smoking friendly.', 'louies' ); ?></p>
			</div>
			<div class="tile">
				<h3><?php esc_html_e( 'Pool, darts &amp; pinball', 'louies' ); ?></h3>
				<p><?php esc_html_e( 'Professional tables and games to keep everyone busy between the speeches.', 'louies' ); ?></p>
			</div>
			<div class="tile">
				<h3><?php esc_html_e( 'Parking for everyone', 'louies' ); ?></h3>
				<p><?php esc_html_e( 'A hundred free spaces right out front, all on camera. Easy on and off Highway 50 at exit 15.', 'louies' ); ?></p>
			</div>
			<div class="tile">
				<h3><?php esc_html_e( 'Food &amp; a full bar', 'louies' ); ?></h3>
				<p><?php esc_html_e( 'Burgers, hot dogs, Philly cheesesteaks and pastrami, plus one of the deepest back bars in Rancho Cordova.', 'louies' ); ?></p>
			</div>
		</div>
	</div>
</section>

<section class="section section-sand" id="enquiry">
	<div class="wrap">
		<div class="split split-wide">
			<div>
				<div class="section-head">
					<p class="eyebrow"><?php esc_html_e( 'Enquire', 'louies' ); ?></p>
					<h2><?php esc_html_e( 'Tell us about it', 'louies' ); ?></h2>
				</div>

				<?php if ( trim( get_the_content() ) ) : ?>
					<div class="entry-content"><?php the_content(); ?></div>
				<?php endif; ?>

				<?php if ( 'ok' === $sent ) : ?>
					<p class="form-note is-ok"><?php esc_html_e( 'Thanks &mdash; your enquiry is on its way. We will get back to you shortly.', 'louies' ); ?></p>
				<?php elseif ( 'invalid' === $sent ) : ?>
					<p class="form-note is-bad"><?php esc_html_e( 'Please fill in your name, a valid email address and a few details.', 'louies' ); ?></p>
				<?php elseif ( 'error' === $sent ) : ?>
					<p class="form-note is-bad"><?php esc_html_e( 'Something went wrong sending that. Please give us a call instead.', 'louies' ); ?></p>
				<?php endif; ?>

				<form class="contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="louies_contact">
					<input type="hidden" name="louies_subject" value="<?php esc_attr_e( 'Private event enquiry', 'louies' ); ?>">
					<?php wp_nonce_field( 'louies_contact', 'louies_contact_nonce' ); ?>

					<div class="hp-field" aria-hidden="true">
						<label for="pe_website"><?php esc_html_e( 'Leave this empty', 'louies' ); ?></label>
						<input type="text" id="pe_website" name="louies_website" tabindex="-1" autocomplete="off">
					</div>

					<div>
						<label for="pe_name"><?php esc_html_e( 'Your name', 'louies' ); ?></label>
						<input type="text" id="pe_name" name="louies_name" required>
					</div>

					<div class="field-row">
						<div>
							<label for="pe_email"><?php esc_html_e( 'Email', 'louies' ); ?></label>
							<input type="email" id="pe_email" name="louies_email" required>
						</div>
						<div>
							<label for="pe_phone"><?php esc_html_e( 'Phone', 'louies' ); ?></label>
							<input type="tel" id="pe_phone" name="louies_phone">
						</div>
					</div>

					<div class="field-row">
						<div>
							<label for="pe_date"><?php esc_html_e( 'Date you have in mind', 'louies' ); ?></label>
							<input type="date" id="pe_date" name="louies_event_date">
						</div>
						<div>
							<label for="pe_guests"><?php esc_html_e( 'Roughly how many people', 'louies' ); ?></label>
							<input type="number" id="pe_guests" name="louies_guests" min="1" max="500" inputmode="numeric">
						</div>
					</div>

					<div>
						<label for="pe_type"><?php esc_html_e( 'What is the occasion?', 'louies' ); ?></label>
						<select id="pe_type" name="louies_event_type">
							<option><?php esc_html_e( 'Birthday party', 'louies' ); ?></option>
							<option><?php esc_html_e( 'Celebration of life', 'louies' ); ?></option>
							<option><?php esc_html_e( 'Company or work party', 'louies' ); ?></option>
							<option><?php esc_html_e( 'Club run or car show', 'louies' ); ?></option>
							<option><?php esc_html_e( 'Fundraiser', 'louies' ); ?></option>
							<option><?php esc_html_e( 'Live music booking', 'louies' ); ?></option>
							<option><?php esc_html_e( 'Something else', 'louies' ); ?></option>
						</select>
					</div>

					<div>
						<label for="pe_message"><?php esc_html_e( 'Anything else we should know', 'louies' ); ?></label>
						<textarea id="pe_message" name="louies_message" required placeholder="<?php esc_attr_e( 'Times, whether you want the stage, food, anything at all.', 'louies' ); ?>"></textarea>
					</div>

					<div><button class="btn" type="submit"><?php esc_html_e( 'Send enquiry', 'louies' ); ?></button></div>
				</form>
			</div>

			<div>
				<div class="tile" style="margin-bottom:1.2rem">
					<h3><?php esc_html_e( 'Rather just call?', 'louies' ); ?></h3>
					<p><?php esc_html_e( 'Quickest way by far. Someone behind the bar will pick up.', 'louies' ); ?></p>
					<a class="footer-big" style="color:var(--coral-deep)" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php echo esc_html( louies_option( 'phone' ) ); ?></a>
				</div>

				<div class="tile" style="margin-bottom:1.2rem">
					<h3><?php esc_html_e( 'Good to know', 'louies' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Open 6am to 2am, every day', 'louies' ); ?></li>
						<li><?php esc_html_e( 'Must be 21 or over', 'louies' ); ?></li>
						<li><?php esc_html_e( 'Dress code applies Fri &amp; Sat after 9pm', 'louies' ); ?></li>
						<li><?php esc_html_e( 'Last call 1:30am', 'louies' ); ?></li>
					</ul>
				</div>

				<div class="map-frame">
					<iframe src="<?php echo esc_url( louies_map_embed_src() ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php esc_attr_e( 'Map to Louie\'s Cocktail Lounge', 'louies' ); ?>"></iframe>
				</div>
			</div>
		</div>
	</div>
</section>

<?php get_template_part( 'parts/find-us' ); ?>

<?php get_footer(); ?>
