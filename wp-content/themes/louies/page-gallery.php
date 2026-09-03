<?php
/**
 * Template Name: Photo Gallery
 *
 * Pulls from the photo list in Bar Details, so adding a picture to the site is
 * uploading it and pasting its number - no gallery plugin.
 */

get_header();

if ( have_posts() ) {
	the_post();
}

$photos = louies_gallery_photos( 40 );
?>

<section class="page-hero">
	<div class="wrap">
		<p class="eyebrow"><?php esc_html_e( 'Have a look round', 'louies' ); ?></p>
		<h1><?php the_title(); ?></h1>
		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="lede" style="max-width:64ch"><?php the_content(); ?></div>
		<?php endif; ?>
	</div>
</section>

<section class="section">
	<div class="wrap">
		<?php if ( $photos ) : ?>
			<div class="photo-grid">
				<?php foreach ( $photos as $i => $photo ) : ?>
					<figure class="<?php echo 0 === $i % 5 ? 'is-wide' : ''; ?>">
						<img src="<?php echo esc_url( $photo['url'] ); ?>" alt="<?php echo esc_attr( $photo['alt'] ); ?>" loading="lazy">
						<?php if ( $photo['caption'] ) : ?>
							<figcaption><?php echo esc_html( $photo['caption'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="lede"><?php esc_html_e( 'Photos coming soon.', 'louies' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'parts/find-us' ); ?>

<?php get_footer(); ?>
