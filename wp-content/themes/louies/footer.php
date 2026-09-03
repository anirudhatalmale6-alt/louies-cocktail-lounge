<?php
/**
 * Footer, plus the sticky thumb bar that follows you down every page on a phone.
 */
?>
</main>

<footer class="site-footer">
	<div class="wrap">
		<div class="footer-cols">
			<div>
				<h4><?php esc_html_e( 'Louie\'s', 'louies' ); ?></h4>
				<p style="color:#cfc3bd">
					<?php esc_html_e( 'Karaoke, live music, sports and pool, just off Highway 50 in Rancho Cordova.', 'louies' ); ?>
				</p>
				<a class="footer-big" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php echo esc_html( louies_option( 'phone' ) ); ?></a>
			</div>

			<div>
				<h4><?php esc_html_e( 'Find us', 'louies' ); ?></h4>
				<ul>
					<li><?php echo esc_html( louies_option( 'address_1' ) ); ?></li>
					<li><?php echo esc_html( louies_option( 'address_2' ) ); ?></li>
					<li style="margin-top:.7rem"><a href="<?php echo esc_url( louies_directions_link() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get directions &rarr;', 'louies' ); ?></a></li>
				</ul>
			</div>

			<div>
				<h4><?php esc_html_e( 'Hours', 'louies' ); ?></h4>
				<ul>
					<li><?php echo wp_kses_post( louies_option( 'hours' ) ); ?></li>
					<li style="margin-top:.7rem;color:var(--brass);font-weight:600"><?php esc_html_e( 'Happy hour', 'louies' ); ?></li>
					<li><?php echo wp_kses_post( louies_option( 'happy_hour' ) ); ?></li>
				</ul>
			</div>

			<div>
				<h4><?php esc_html_e( 'Pages', 'louies' ); ?></h4>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => '__return_empty_string',
				) );
				?>
				<?php if ( louies_option( 'facebook' ) || louies_option( 'instagram' ) ) : ?>
					<ul style="margin-top:.8rem">
						<?php if ( louies_option( 'facebook' ) ) : ?>
							<li><a href="<?php echo esc_url( louies_option( 'facebook' ) ); ?>" target="_blank" rel="noopener">Facebook</a></li>
						<?php endif; ?>
						<?php if ( louies_option( 'instagram' ) ) : ?>
							<li><a href="<?php echo esc_url( louies_option( 'instagram' ) ); ?>" target="_blank" rel="noopener">Instagram</a></li>
						<?php endif; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>

		<div class="colophon">
			<span>&copy; <?php echo esc_html( current_datetime()->format( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Must be 21 or over. Please drink responsibly.', 'louies' ); ?></span>
			<span><?php esc_html_e( 'We reserve the right to refuse service or entry to anyone.', 'louies' ); ?></span>
		</div>
	</div>
</footer>

<nav class="thumb-bar" aria-label="<?php esc_attr_e( 'Quick actions', 'louies' ); ?>">
	<a href="<?php echo esc_url( louies_phone_link() ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
		<?php esc_html_e( 'Call', 'louies' ); ?>
	</a>
	<a href="<?php echo esc_url( louies_directions_link() ); ?>" target="_blank" rel="noopener">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
		<?php esc_html_e( 'Map', 'louies' ); ?>
	</a>
	<a href="<?php echo esc_url( get_permalink( get_page_by_path( 'events' ) ) ?: home_url( '/events/' ) ); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
		<?php esc_html_e( 'Events', 'louies' ); ?>
	</a>
</nav>

<?php wp_footer(); ?>
</body>
</html>
