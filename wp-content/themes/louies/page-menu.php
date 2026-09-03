<?php
/**
 * Template Name: Food & Drink Menu
 *
 * Sections come from the Menu Sections taxonomy, items from Food & Drink.
 * A section whose items have no prices renders as a tidy back-bar list.
 */

get_header();

if ( have_posts() ) {
	the_post();
}

$sections = get_terms( array(
	'taxonomy'   => 'louies_menu_section',
	'hide_empty' => true,
	'orderby'    => 'term_id',
	'order'      => 'ASC',
) );

$sections = is_wp_error( $sections ) ? array() : $sections;
?>

<section class="page-hero">
	<div class="wrap">
		<p class="eyebrow"><?php esc_html_e( 'Behind the bar', 'louies' ); ?></p>
		<h1><?php the_title(); ?></h1>
		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="lede" style="max-width:64ch"><?php the_content(); ?></div>
		<?php endif; ?>
	</div>
</section>

<div class="wrap">
	<?php if ( $sections ) : ?>
		<nav class="menu-nav" aria-label="<?php esc_attr_e( 'Menu sections', 'louies' ); ?>">
			<?php foreach ( $sections as $s ) : ?>
				<a href="#<?php echo esc_attr( $s->slug ); ?>"><?php echo esc_html( $s->name ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
</div>

<section class="section" style="padding-top:0">
	<div class="wrap">
		<?php if ( ! $sections ) : ?>
			<p class="lede"><?php esc_html_e( 'The menu is being updated. Call the bar and we\'ll tell you what\'s pouring.', 'louies' ); ?></p>
		<?php endif; ?>

		<?php foreach ( $sections as $section ) :

			$items = get_posts( array(
				'post_type'      => 'louies_menu_item',
				'posts_per_page' => -1,
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'no_found_rows'  => true,
				'tax_query'      => array( array(
					'taxonomy' => 'louies_menu_section',
					'field'    => 'term_id',
					'terms'    => $section->term_id,
				) ),
			) );

			if ( ! $items ) {
				continue;
			}

			// A section with no prices anywhere is a bottle list, not a price list.
			$has_price = false;
			foreach ( $items as $item ) {
				if ( get_post_meta( $item->ID, '_louies_price', true ) ) {
					$has_price = true;
					break;
				}
			}
			?>

			<div class="menu-section" id="<?php echo esc_attr( $section->slug ); ?>">
				<h2><?php echo esc_html( $section->name ); ?></h2>

				<?php if ( $section->description ) : ?>
					<p class="lede" style="margin-top:-.5rem;margin-bottom:1.2rem"><?php echo esc_html( $section->description ); ?></p>
				<?php endif; ?>

				<?php if ( $has_price ) : ?>
					<ul class="priced-list">
						<?php foreach ( $items as $item ) :
							$price = get_post_meta( $item->ID, '_louies_price', true );
							$desc  = get_post_meta( $item->ID, '_louies_desc', true );
							?>
							<li>
								<span class="pl-name">
									<?php echo esc_html( $item->post_title ); ?>
									<?php if ( $desc ) : ?><span class="pl-desc"><?php echo esc_html( $desc ); ?></span><?php endif; ?>
								</span>
								<span class="pl-dots" aria-hidden="true"></span>
								<?php if ( $price ) : ?><span class="pl-price"><?php echo esc_html( $price ); ?></span><?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<ul class="bottle-list">
						<?php foreach ( $items as $item ) : ?>
							<li><?php echo esc_html( $item->post_title ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

		<div class="tile" style="margin-top:2rem">
			<h3><?php esc_html_e( 'A note on the kitchen', 'louies' ); ?></h3>
			<p style="margin:0">
				<?php esc_html_e( 'Louie\'s doesn\'t have a full kitchen. What we do have is honest bar food that goes with a drink: hot links, Angus cheeseburgers, hot dogs, Philly cheesesteaks, and plenty to snack on.', 'louies' ); ?>
			</p>
		</div>
	</div>
</section>

<?php get_footer(); ?>
