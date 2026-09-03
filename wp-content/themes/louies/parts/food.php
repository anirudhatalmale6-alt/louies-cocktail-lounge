<?php
/**
 * Food. The $12 combo is the headline because it is the only thing on the menu
 * with a deal attached - everything else is a price list and belongs on the
 * menu page.
 */

$burger = louies_photo_url( 'combo_burger_id', 247, 'large' );
$hotdog = louies_photo_url( 'combo_hotdog_id', 248, 'large' );
?>
<section class="section section-sand" id="food">
	<div class="wrap">
		<div class="section-head">
			<p class="eyebrow"><?php esc_html_e( 'Kitchen', 'louies' ); ?></p>
			<h2><?php esc_html_e( 'Stadium food, bar prices', 'louies' ); ?></h2>
			<p class="lede"><?php esc_html_e( 'No full kitchen, no fuss - just the right food to go with a drink and a game.', 'louies' ); ?></p>
		</div>

		<div class="combo">
			<div class="combo-shots">
				<img src="<?php echo esc_url( $burger ); ?>" alt="<?php esc_attr_e( 'Cheeseburger with chips and a domestic draft beer', 'louies' ); ?>" loading="lazy">
				<img src="<?php echo esc_url( $hotdog ); ?>" alt="<?php esc_attr_e( 'Hot dog with chips and a domestic draft beer', 'louies' ); ?>" loading="lazy">
			</div>

			<div class="combo-copy">
				<span class="combo-flag"><?php esc_html_e( 'The deal', 'louies' ); ?></span>
				<h3><?php esc_html_e( 'Burger or hot dog, chips and a draft beer', 'louies' ); ?></h3>
				<p class="combo-price">$12</p>
				<p><?php esc_html_e( 'Cheeseburger or hot dog, a bag of chips and a domestic draft. The whole thing, twelve dollars.', 'louies' ); ?></p>
				<a class="btn btn-coral" href="<?php echo esc_url( home_url( '/menu/' ) ); ?>"><?php esc_html_e( 'See the full menu', 'louies' ); ?></a>
			</div>
		</div>

		<ul class="food-strip">
			<li><b><?php esc_html_e( 'Angus Cheeseburger', 'louies' ); ?></b><span>$6.00</span></li>
			<li><b><?php esc_html_e( 'Hot Dog', 'louies' ); ?></b><span>$6.00</span></li>
			<li><b><?php esc_html_e( 'Philly Cheese Steak', 'louies' ); ?></b><span>$5.25</span></li>
			<li><b><?php esc_html_e( 'Hot Pastrami &amp; Cheese', 'louies' ); ?></b><span>$3.75</span></li>
			<li><b><?php esc_html_e( 'Pacific Gold Jerky', 'louies' ); ?></b><span>$3.00</span></li>
			<li><b><?php esc_html_e( 'Chips, nuts &amp; Slim Jims', 'louies' ); ?></b><span><?php esc_html_e( 'from $1.00', 'louies' ); ?></span></li>
		</ul>
	</div>
</section>
