<?php
/**
 * One event. For a repeating night it lists the next several dates so nobody
 * has to work out when "every other Saturday" actually falls.
 */

get_header();

while ( have_posts() ) :
	the_post();

	$m     = louies_event_meta( get_the_ID() );
	$label = louies_repeat_label( $m );
	$today = louies_today();
	$next  = louies_event_dates( get_the_ID(), $today, $today->modify( '+6 months' ) );
	$first = $next[0] ?? null;
	?>

	<article class="event-hero">
		<div class="wrap wrap-narrow">
			<p class="eyebrow">
				<a href="<?php echo esc_url( home_url( '/events/' ) ); ?>" style="color:inherit;text-decoration:none">&larr; <?php esc_html_e( 'All events', 'louies' ); ?></a>
			</p>

			<h1><?php the_title(); ?></h1>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="event-media" style="margin:1.5rem 0">
					<?php the_post_thumbnail( 'louies-hero', array( 'style' => 'width:100%;height:100%;object-fit:cover' ) ); ?>
				</div>
			<?php endif; ?>

			<dl class="event-facts">
				<div>
					<dt><?php esc_html_e( 'Next date', 'louies' ); ?></dt>
					<dd><?php echo $first ? esc_html( louies_format_date( $first->format( 'Y-m-d' ) ) ) : esc_html__( 'Past event', 'louies' ); ?></dd>
				</div>
				<?php if ( $m['louies_time_start'] ) : ?>
					<div>
						<dt><?php esc_html_e( 'Time', 'louies' ); ?></dt>
						<dd><?php echo wp_kses_post( louies_format_time( $m['louies_time_start'], $m['louies_time_end'] ) ); ?></dd>
					</div>
				<?php endif; ?>
				<?php if ( $m['louies_price'] ) : ?>
					<div>
						<dt><?php esc_html_e( 'Cover', 'louies' ); ?></dt>
						<dd><?php echo esc_html( $m['louies_price'] ); ?></dd>
					</div>
				<?php endif; ?>
			</dl>

			<?php if ( $label ) : ?>
				<p style="font-family:var(--display);font-size:1.35rem;font-weight:800;color:var(--neon);letter-spacing:.03em;text-transform:uppercase;margin-bottom:.6rem">
					<?php echo esc_html( $label ); ?>
				</p>
				<?php if ( count( $next ) > 1 ) : ?>
					<p style="font-size:.8rem;letter-spacing:.16em;text-transform:uppercase;color:var(--muted);margin-bottom:.5rem"><?php esc_html_e( 'Coming up', 'louies' ); ?></p>
					<ul class="next-dates" style="margin-bottom:1.8rem">
						<?php foreach ( array_slice( $next, 0, 10 ) as $d ) : ?>
							<li><?php echo esc_html( louies_format_date( $d->format( 'Y-m-d' ) ) ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			<?php endif; ?>

			<div class="entry-content"><?php the_content(); ?></div>

			<div class="hero-actions" style="margin-top:2rem">
				<?php if ( $m['louies_ticket_url'] ) : ?>
					<a class="btn" href="<?php echo esc_url( $m['louies_ticket_url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get tickets', 'louies' ); ?></a>
				<?php endif; ?>
				<a class="btn btn-ghost" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php esc_html_e( 'Call the bar', 'louies' ); ?></a>
				<a class="btn btn-ghost" href="<?php echo esc_url( louies_directions_link() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Directions', 'louies' ); ?></a>
			</div>
		</div>
	</article>

	<?php
	$more = array_values( array_filter( louies_upcoming( 4, array( 'unique' => true ) ), function ( $o ) {
		return $o['post_id'] !== get_the_ID();
	} ) );
	?>
	<?php if ( $more ) : ?>
		<section class="section section-alt" style="margin-top:clamp(3rem,8vw,5rem)">
			<div class="wrap">
				<div class="section-head"><h2><?php esc_html_e( 'Also coming up', 'louies' ); ?></h2></div>
				<div class="event-grid">
					<?php foreach ( array_slice( $more, 0, 3 ) as $o ) : get_template_part( 'parts/event-card', null, array( 'occurrence' => $o ) ); endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

<?php endwhile; ?>

<?php get_footer(); ?>
