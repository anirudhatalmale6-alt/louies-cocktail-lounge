<?php
/**
 * Map, address, phone and hours. Sits at the bottom of the home page and
 * anywhere else it earns its place.
 */
?>
<section class="findus on-plum" id="find-us">
	<div class="wrap">
		<div class="findus-grid">
			<div>
				<p class="eyebrow"><?php esc_html_e( 'Find us', 'louies' ); ?></p>
				<h2 style="font-size:clamp(2.1rem,6vw,3.2rem)"><?php esc_html_e( 'Come and see us', 'louies' ); ?></h2>

				<p class="findus-address">
					<?php echo esc_html( louies_option( 'address_1' ) ); ?><br>
					<?php echo esc_html( louies_option( 'address_2' ) ); ?>
				</p>

				<a class="findus-phone" href="<?php echo esc_url( louies_phone_link() ); ?>">
					<svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
					<?php echo esc_html( louies_option( 'phone' ) ); ?>
				</a>

				<p class="findus-hours">
					<strong><?php esc_html_e( 'Open daily', 'louies' ); ?></strong> <?php echo wp_kses_post( louies_option( 'hours' ) ); ?><br>
					<strong><?php esc_html_e( 'Happy hour', 'louies' ); ?></strong> <?php echo wp_kses_post( louies_option( 'happy_hour' ) ); ?><br>
					<?php esc_html_e( 'One block north of Mather Field Rd, off exit 15. Free parking out front.', 'louies' ); ?>
				</p>

				<div class="hero-actions">
					<a class="btn btn-coral" href="<?php echo esc_url( louies_directions_link() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get directions', 'louies' ); ?></a>
					<a class="btn btn-ghost" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php esc_html_e( 'Call the bar', 'louies' ); ?></a>
				</div>
			</div>

			<div class="map-frame">
				<iframe src="<?php echo esc_url( louies_map_embed_src() ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php esc_attr_e( 'Map to Louie\'s Cocktail Lounge, 3030 Mather Field Rd, Rancho Cordova', 'louies' ); ?>"></iframe>
			</div>
		</div>
	</div>
</section>
