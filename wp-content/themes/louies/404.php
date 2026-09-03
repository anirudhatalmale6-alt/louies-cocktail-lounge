<?php get_header(); ?>
<section class="page-hero">
	<div class="wrap">
		<p class="eyebrow"><?php esc_html_e( 'Wrong turn', 'louies' ); ?></p>
		<h1><?php esc_html_e( 'That page has left the building', 'louies' ); ?></h1>
	</div>
</section>
<section class="section">
	<div class="wrap wrap-narrow">
		<p class="lede"><?php esc_html_e( 'The bar is still here though. Try one of these:', 'louies' ); ?></p>
		<div class="hero-actions">
			<a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'louies' ); ?></a>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/events/' ) ); ?>"><?php esc_html_e( "What's on", 'louies' ); ?></a>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/menu/' ) ); ?>"><?php esc_html_e( 'Menu', 'louies' ); ?></a>
			<a class="btn btn-ghost" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php esc_html_e( 'Call us', 'louies' ); ?></a>
		</div>
	</div>
</section>
<?php get_footer();
