<?php
/**
 * Header. Phone number and open/closed light are never more than a glance away.
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#08060a">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'louies' ); ?></a>

<?php $notice = louies_option( 'notice' ); ?>
<?php if ( $notice ) : ?>
	<div class="site-notice"><?php echo esc_html( $notice ); ?></div>
<?php endif; ?>

<header class="site-header">
	<div class="wrap header-strip">
		<?php louies_logo(); ?>

		<nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e( 'Main menu', 'louies' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'depth'          => 1,
				'fallback_cb'    => '__return_empty_string',
			) );
			?>
		</nav>

		<div class="header-actions">
			<span class="status-pill <?php echo louies_is_open_now() ? 'is-open' : ''; ?>">
				<span class="status-dot"></span>
				<?php echo louies_is_open_now() ? esc_html__( 'Open now', 'louies' ) : esc_html__( 'Closed', 'louies' ); ?>
			</span>

			<a class="header-phone" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php echo esc_html( louies_option( 'phone' ) ); ?></a>

			<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="<?php esc_attr_e( 'Menu', 'louies' ); ?>">
				<span></span><span></span><span></span>
			</button>
		</div>
	</div>
</header>

<main id="main">
