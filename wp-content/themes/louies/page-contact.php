<?php
/**
 * Template Name: Contact
 */

get_header();

if ( have_posts() ) {
	the_post();
}

$sent = isset( $_GET['sent'] ) ? sanitize_key( wp_unslash( $_GET['sent'] ) ) : '';
?>

<section class="page-hero">
	<div class="wrap">
		<p class="eyebrow"><?php esc_html_e( 'Get in touch', 'louies' ); ?></p>
		<h1><?php the_title(); ?></h1>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<div class="split split-wide">

			<div>
				<?php if ( trim( get_the_content() ) ) : ?>
					<div class="entry-content"><?php the_content(); ?></div>
				<?php endif; ?>

				<?php if ( 'ok' === $sent ) : ?>
					<p class="form-note is-ok"><?php esc_html_e( 'Thanks &mdash; your message is on its way. We\'ll get back to you.', 'louies' ); ?></p>
				<?php elseif ( 'invalid' === $sent ) : ?>
					<p class="form-note is-bad"><?php esc_html_e( 'Please fill in your name, a valid email address and a message.', 'louies' ); ?></p>
				<?php elseif ( 'error' === $sent ) : ?>
					<p class="form-note is-bad"><?php esc_html_e( 'Something went wrong sending that. Please give us a call instead.', 'louies' ); ?></p>
				<?php endif; ?>

				<form class="contact-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="louies_contact">
					<?php wp_nonce_field( 'louies_contact', 'louies_contact_nonce' ); ?>

					<div class="hp-field" aria-hidden="true">
						<label for="louies_website"><?php esc_html_e( 'Leave this empty', 'louies' ); ?></label>
						<input type="text" id="louies_website" name="louies_website" tabindex="-1" autocomplete="off">
					</div>

					<div>
						<label for="louies_name"><?php esc_html_e( 'Your name', 'louies' ); ?></label>
						<input type="text" id="louies_name" name="louies_name" required>
					</div>

					<div>
						<label for="louies_email"><?php esc_html_e( 'Email', 'louies' ); ?></label>
						<input type="email" id="louies_email" name="louies_email" required>
					</div>

					<div>
						<label for="louies_phone"><?php esc_html_e( 'Phone (optional)', 'louies' ); ?></label>
						<input type="tel" id="louies_phone" name="louies_phone">
					</div>

					<div>
						<label for="louies_subject"><?php esc_html_e( 'What\'s this about?', 'louies' ); ?></label>
						<select id="louies_subject" name="louies_subject">
							<option><?php esc_html_e( 'General question', 'louies' ); ?></option>
							<option><?php esc_html_e( 'Book the venue for a party', 'louies' ); ?></option>
							<option><?php esc_html_e( 'Booking enquiry &mdash; band or KJ', 'louies' ); ?></option>
							<option><?php esc_html_e( 'Something else', 'louies' ); ?></option>
						</select>
					</div>

					<div>
						<label for="louies_message"><?php esc_html_e( 'Message', 'louies' ); ?></label>
						<textarea id="louies_message" name="louies_message" required></textarea>
					</div>

					<div><button class="btn" type="submit"><?php esc_html_e( 'Send it', 'louies' ); ?></button></div>
				</form>
			</div>

			<div>
				<div class="tile" style="margin-bottom:1.2rem">
					<h3><?php esc_html_e( 'Quickest way', 'louies' ); ?></h3>
					<p><?php esc_html_e( 'Just call. Someone behind the bar will pick up.', 'louies' ); ?></p>
					<a class="footer-big" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php echo esc_html( louies_option( 'phone' ) ); ?></a>
				</div>

				<div class="tile" style="margin-bottom:1.2rem">
					<h3><?php esc_html_e( 'Where', 'louies' ); ?></h3>
					<p style="margin-bottom:.6rem">
						<?php echo esc_html( louies_option( 'address_1' ) ); ?><br>
						<?php echo esc_html( louies_option( 'address_2' ) ); ?>
					</p>
					<a href="<?php echo esc_url( louies_directions_link() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get directions &rarr;', 'louies' ); ?></a>
				</div>

				<div class="tile" style="margin-bottom:1.2rem">
					<h3><?php esc_html_e( 'When', 'louies' ); ?></h3>
					<p style="margin-bottom:.3rem"><?php echo wp_kses_post( louies_option( 'hours' ) ); ?></p>
					<p style="margin:0;color:var(--brass)"><?php esc_html_e( 'Happy hour:', 'louies' ); ?> <?php echo wp_kses_post( louies_option( 'happy_hour' ) ); ?></p>
				</div>

				<div class="map-frame">
					<iframe src="<?php echo esc_url( louies_map_embed_src() ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php esc_attr_e( 'Map to Louie\'s Cocktail Lounge', 'louies' ); ?>"></iframe>
				</div>
			</div>

		</div>
	</div>
</section>

<?php get_footer(); ?>
