<?php
/**
 * Template Name: Events Calendar
 *
 * Everything on, grouped by month. A weekly night appears on each of its dates
 * without anybody having to create it more than once.
 */

get_header();

if ( have_posts() ) {
	the_post();
}

$months  = max( 1, min( 12, (int) ( $_GET['months'] ?? 3 ) ) );
$today   = louies_today();
$from    = $today->format( 'Y-m-d' );
$to      = $today->modify( '+' . $months . ' months' )->format( 'Y-m-d' );
$all     = louies_get_occurrences( $from, $to );

$grouped = array();
foreach ( $all as $o ) {
	$grouped[ substr( $o['date'], 0, 7 ) ][] = $o;
}
?>

<section class="page-hero">
	<div class="wrap">
		<p class="eyebrow"><?php esc_html_e( "Live at Louie's", "louies" ); ?></p>
		<h1><?php the_title(); ?></h1>
		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="lede wrap-narrow" style="padding:0;max-width:64ch"><?php the_content(); ?></div>
		<?php endif; ?>
	</div>
</section>

<section class="section">
	<div class="wrap">

		<?php if ( ! $grouped ) : ?>
			<p class="lede"><?php esc_html_e( 'Nothing on the calendar for the next few months. Call the bar and we\'ll tell you what\'s happening.', 'louies' ); ?></p>
		<?php endif; ?>

		<?php foreach ( $grouped as $ym => $occurrences ) :
			$month = DateTimeImmutable::createFromFormat( 'Y-m-d|', $ym . '-01', louies_timezone() );
			?>
			<div class="cal-group">
				<h3><?php echo esc_html( $month ? $month->format( 'F Y' ) : $ym ); ?></h3>

				<?php foreach ( $occurrences as $o ) :
					$label = louies_repeat_label( $o['meta'] );
					?>
					<div class="cal-row">
						<span class="cal-when">
							<?php echo esc_html( louies_format_date( $o['date'] ) ); ?>
							<?php if ( $o['time_start'] ) : ?>
								<span style="display:block;font-size:.82rem;color:var(--muted);font-family:var(--body);font-weight:500;letter-spacing:0"><?php echo wp_kses_post( louies_format_time( $o['time_start'], $o['time_end'] ) ); ?></span>
							<?php endif; ?>
						</span>

						<span class="cal-what">
							<a href="<?php echo esc_url( get_permalink( $o['post_id'] ) ); ?>"><?php echo esc_html( get_the_title( $o['post_id'] ) ); ?></a>
							<?php if ( $o['price'] ) : ?>
								<span style="color:var(--brass);font-weight:700;margin-left:.5rem"><?php echo esc_html( $o['price'] ); ?></span>
							<?php endif; ?>
							<?php if ( $label ) : ?>
								<span class="cal-extra"><?php echo esc_html( $label ); ?></span>
							<?php endif; ?>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>

		<?php if ( $grouped && $months < 12 ) : ?>
			<p style="margin-top:1rem">
				<a class="btn btn-ghost btn-sm" href="<?php echo esc_url( add_query_arg( 'months', 12, get_permalink() ) ); ?>"><?php esc_html_e( 'Show the next 12 months', 'louies' ); ?></a>
			</p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
