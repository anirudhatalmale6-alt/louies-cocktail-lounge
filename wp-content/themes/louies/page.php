<?php
/**
 * Standard page - About Us, Privacy, anything the bar writes itself.
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<section class="page-hero">
		<div class="wrap">
			<h1><?php the_title(); ?></h1>
		</div>
	</section>

	<section class="section">
		<div class="wrap wrap-narrow">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'louies-hero', array( 'style' => 'border-radius:4px;margin-bottom:2rem' ) ); ?>
			<?php endif; ?>
			<div class="entry-content"><?php the_content(); ?></div>
		</div>
	</section>
	<?php
endwhile;

get_footer();
