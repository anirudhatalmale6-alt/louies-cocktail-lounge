<?php
/**
 * Game day. A full-bleed photo of the room with the game on, the hardware
 * count stated plainly, and the four sports as their own cards.
 */

$bg = louies_photo_url( 'sports_image_id', 243, 'louies-hero' );

$sports = array(
	array(
		'name'  => __( 'Football', 'louies' ),
		'when'  => __( 'Sun, Mon &amp; Thu', 'louies' ),
		'blurb' => __( 'NFL every Sunday, Monday and Thursday on the 110" projector. Game-day drink specials at the bar.', 'louies' ),
		'icon'  => 'M12 2c-3 3-4.5 6.4-4.5 10S9 19 12 22c3-3 4.5-6.4 4.5-10S15 5 12 2Zm-7.5 6C3 9.5 2.5 12 3 14.5m16.5-6.5c1.5 1.5 2 4 1.5 6.5M9.5 12h5M12 9.5v5',
	),
	array(
		'name'  => __( 'Baseball', 'louies' ),
		'when'  => __( 'All day, every day', 'louies' ),
		'blurb' => __( 'MLB playing all day, every day through the season. Never miss a pitch.', 'louies' ),
		'icon'  => 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Zm-5.5 2.5c2 2.2 3 5 3 6.5s-1 4.3-3 6.5m11-13c-2 2.2-3 5-3 6.5s1 4.3 3 6.5',
	),
	array(
		'name'  => __( 'Basketball', 'louies' ),
		'when'  => __( 'Tip-off to the Finals', 'louies' ),
		'blurb' => __( 'NBA and NCAA from the first tip-off through to the Finals.', 'louies' ),
		'icon'  => 'M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Zm0 0v18M3 12h18M5.6 5.6c3.6 3.6 3.6 9.2 0 12.8m12.8-12.8c-3.6 3.6-3.6 9.2 0 12.8',
	),
	array(
		'name'  => __( 'Golf', 'louies' ),
		'when'  => __( 'Majors &amp; internationals', 'louies' ),
		'blurb' => __( 'The Masters, the US Open and the international tournaments. Want a specific game? Just ask.', 'louies' ),
		'icon'  => 'M11 21V4l7 3.5-7 3.5M11 21H8m3 0h3M6 14.5c-1.2.6-2 1.4-2 2.3C4 18.6 7.6 20 12 20s8-1.4 8-3.2c0-.9-.8-1.7-2-2.3',
	),
);
?>
<section class="section gameday on-plum" id="sports" style="--gameday-image:url('<?php echo esc_url( $bg ); ?>')">
	<div class="wrap">
		<div class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Game day', 'louies' ); ?></p>
			<h2><?php esc_html_e( 'Every game. Every screen.', 'louies' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'If it is live, it is on. Ask the bar for a specific game and we will put it up.', 'louies' ); ?></p>
		</div>

		<ul class="screen-stats">
			<li><b>12</b><span><?php esc_html_e( '55-inch screens', 'louies' ); ?></span></li>
			<li><b>1</b><span><?php esc_html_e( '70-inch screen', 'louies' ); ?></span></li>
			<li><b>110"</b><span><?php esc_html_e( 'projector', 'louies' ); ?></span></li>
			<li><b><?php esc_html_e( 'Patio', 'louies' ); ?></b><span><?php esc_html_e( '55-inch screen outside', 'louies' ); ?></span></li>
		</ul>

		<div class="sport-grid">
			<?php foreach ( $sports as $s ) : ?>
				<article class="sport-card">
					<svg class="sport-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="<?php echo esc_attr( $s['icon'] ); ?>" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
					<h3><?php echo esc_html( $s['name'] ); ?></h3>
					<p class="sport-when"><?php echo wp_kses_post( $s['when'] ); ?></p>
					<p class="sport-blurb"><?php echo esc_html( $s['blurb'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
