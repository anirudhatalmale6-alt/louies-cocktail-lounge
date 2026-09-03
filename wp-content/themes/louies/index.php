<?php
/**
 * Fallback for anything without a more specific template.
 */

get_header();
?>
<section class="page-hero">
	<div class="wrap">
		<h1><?php echo esc_html( is_home() ? get_bloginfo( 'name' ) : wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
	</div>
</section>

<section class="section">
	<div class="wrap wrap-narrow">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article style="padding-bottom:2rem;margin-bottom:2rem;border-bottom:1px solid var(--ink-line)">
					<h2 style="font-size:1.6rem"><a href="<?php the_permalink(); ?>" style="color:var(--bone);text-decoration:none"><?php the_title(); ?></a></h2>
					<div class="entry-content"><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
			<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
		<?php else : ?>
			<p class="lede"><?php esc_html_e( 'Nothing here.', 'louies' ); ?></p>
		<?php endif; ?>
	</div>
</section>
<?php get_footer();
