<?php
/**
 * One event on one date.
 *
 * @var array $args ['occurrence' => array from louies_get_occurrences()]
 */

$o = $args['occurrence'] ?? null;
if ( ! $o ) {
	return;
}

// 'large', not the hard-cropped card size. Event artwork arrives as portrait
// flyers - a poster is taller than it is wide - and a 16:9 crop of one takes a
// band out of the middle, losing the date off the top and the venue off the
// bottom. The card shows the whole flyer instead and fills the gap at the sides
// with a blurred copy of it.
$image = louies_event_image( $o['post_id'], 'large' );
$date  = DateTimeImmutable::createFromFormat( 'Y-m-d|', $o['date'], louies_timezone() );
$label = louies_repeat_label( $o['meta'] );
?>
<article class="event-card">
	<div class="event-media<?php echo $image ? ' has-poster' : ''; ?>">
		<?php if ( $image ) : ?>
			<img class="event-poster-bg" src="<?php echo esc_url( $image ); ?>" alt="" aria-hidden="true" loading="lazy" decoding="async">
			<img class="event-poster" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $o['post_id'] ) ); ?>" loading="lazy">
		<?php else : ?>
			<div class="event-media-empty"><span>Louie's</span></div>
		<?php endif; ?>

		<?php if ( $date ) : ?>
			<div class="event-date-chip">
				<span class="dc-day"><?php echo esc_html( $date->format( 'j' ) ); ?></span>
				<span class="dc-mon"><?php echo esc_html( $date->format( 'M' ) ); ?></span>
			</div>
		<?php endif; ?>
	</div>

	<div class="event-body">
		<?php if ( $label ) : ?>
			<span class="event-repeat"><?php echo esc_html( $label ); ?></span>
		<?php endif; ?>

		<h3><a href="<?php echo esc_url( get_permalink( $o['post_id'] ) ); ?>"><?php echo esc_html( get_the_title( $o['post_id'] ) ); ?></a></h3>

		<ul class="event-meta">
			<li><?php echo esc_html( louies_format_date( $o['date'], true ) ); ?></li>
			<?php if ( $o['time_start'] ) : ?>
				<li><?php echo wp_kses_post( louies_format_time( $o['time_start'], $o['time_end'] ) ); ?></li>
			<?php endif; ?>
			<?php if ( $o['price'] ) : ?>
				<li><strong><?php echo esc_html( $o['price'] ); ?></strong></li>
			<?php endif; ?>
		</ul>

		<?php if ( $o['ticket_url'] ) : ?>
			<a class="btn btn-sm" href="<?php echo esc_url( $o['ticket_url'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Get tickets', 'louies' ); ?></a>
		<?php else : ?>
			<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( get_permalink( $o['post_id'] ) ); ?>"><?php esc_html_e( 'Details', 'louies' ); ?></a>
		<?php endif; ?>
	</div>
</article>
