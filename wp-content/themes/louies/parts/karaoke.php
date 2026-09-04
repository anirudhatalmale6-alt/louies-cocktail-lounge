<?php
/**
 * The karaoke band.
 *
 * Sits directly under the hero because karaoke four nights a week is the single
 * thing this bar is best known for, and it was previously only visible as one
 * word in the hero tag list and a couple of cells in the weekly grid.
 *
 * Every fact here is read from the karaoke events themselves - nights, times
 * and the links - so changing a night in the admin changes this band too. The
 * one thing that is typed is the standfirst, which is editable copy, not data.
 */

$nights = louies_karaoke_schedule();

if ( ! $nights ) {
	return; // No karaoke events published: show nothing rather than an empty promise.
}

$days_full  = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );
$days_short = array( 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' );

$state = louies_karaoke_state( $nights );

// The payload the browser recalculates from. It carries the pre-formatted
// clock strings as well as the minute values so the JavaScript never has to
// know how this site likes its times written.
$payload = array();
foreach ( $nights as $d => $n ) {
	$payload[ $d ] = array(
		's' => louies_minutes( $n['start'] ),
		'e' => louies_minutes( $n['end'] ),
		't' => wp_strip_all_tags( louies_clock( $n['start'] ) ),
	);
}

$first     = reset( $nights );
$time_line = louies_format_time( $first['start'], $first['end'] );

// True when every karaoke night runs to the same clock, which is the normal
// case and lets the headline state one time instead of four.
$uniform = true;
foreach ( $nights as $n ) {
	if ( $n['start'] !== $first['start'] || $n['end'] !== $first['end'] ) {
		$uniform = false;
		break;
	}
}

$photo = louies_photo_url( 'karaoke_image_id', 0, 'louies-hero' );

switch ( $state['state'] ) {
	case 'on':
		$live_text = __( 'Karaoke is on right now — the mic is open', 'louies' );
		break;
	case 'tonight':
		/* translators: %s: a time such as 9:00 pm. */
		$live_text = sprintf( __( 'Karaoke tonight from %s', 'louies' ), louies_clock( $state['start'] ) );
		break;
	case 'next':
		/* translators: 1: a weekday, 2: a time such as 9:00 pm. */
		$live_text = sprintf( __( 'Next karaoke: %1$s from %2$s', 'louies' ), $days_full[ $state['day'] ], louies_clock( $state['start'] ) );
		break;
	default:
		$live_text = '';
}
?>

<section class="karaoke" id="karaoke">
	<div class="wrap karaoke-inner">

		<div class="karaoke-copy">
			<p class="eyebrow karaoke-eyebrow">
				<?php
				printf(
					/* translators: %d: a number of nights, usually four. */
					esc_html( _n( '%d night a week', '%d nights a week', count( $nights ), 'louies' ) ),
					(int) count( $nights )
				);
				?>
			</p>

			<h2 class="karaoke-title">
				<?php esc_html_e( 'Karaoke at', 'louies' ); ?>
				<span class="script"><?php esc_html_e( 'Louie\'s', 'louies' ); ?></span>
			</h2>

			<p class="karaoke-lede">
				<?php esc_html_e( 'Some of the best KJs in Sacramento, a proper stage, a lit dance floor and thousands of songs. No cover, no sign-up fee &mdash; just put your name down and sing.', 'louies' ); ?>
			</p>

			<ul class="karaoke-nights">
				<?php foreach ( $nights as $d => $n ) : ?>
					<li class="karaoke-night<?php echo ( $state['day'] === $d && 'next' !== $state['state'] ) ? ' is-now' : ''; ?>" data-dow="<?php echo (int) $d; ?>">
						<a href="<?php echo esc_url( get_permalink( $n['post_id'] ) ); ?>">
							<b><?php echo esc_html( $days_short[ $d ] ); ?></b>
							<?php if ( ! $uniform ) : ?>
								<span><?php echo wp_kses_post( louies_format_time( $n['start'], $n['end'] ) ); ?></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( $uniform ) : ?>
				<p class="karaoke-time"><?php echo wp_kses_post( $time_line ); ?> <span>&middot;</span> <?php esc_html_e( 'No cover', 'louies' ); ?></p>
			<?php else : ?>
				<p class="karaoke-time"><?php esc_html_e( 'No cover, any night', 'louies' ); ?></p>
			<?php endif; ?>

			<div class="karaoke-actions">
				<a class="btn btn-coral" href="<?php echo esc_url( home_url( '/events/' ) ); ?>"><?php esc_html_e( 'See what\'s on', 'louies' ); ?></a>
				<a class="btn btn-ghost" href="<?php echo esc_url( louies_phone_link() ); ?>"><?php esc_html_e( 'Call the bar', 'louies' ); ?></a>
			</div>
		</div>

		<figure class="karaoke-media">
			<img src="<?php echo esc_url( $photo ); ?>"
				alt="<?php esc_attr_e( 'A singer on stage at Louie\'s Cocktail Lounge with the lyrics up on the big screen', 'louies' ); ?>"
				width="1200" height="630" loading="lazy" decoding="async">

			<?php
			// Always in the markup, switched with a class - never rendered
			// conditionally. A cached page that omitted this element would give
			// the JavaScript nothing to correct, and the band would sit there
			// advertising Wednesday all weekend.
			?>
			<figcaption class="karaoke-live<?php echo ( 'on' === $state['state'] ) ? ' is-on' : ''; ?>"
				data-louies-karaoke
				data-tz="<?php echo esc_attr( louies_timezone_name() ); ?>"
				data-nights="<?php echo esc_attr( wp_json_encode( $payload ) ); ?>"
				data-days="<?php echo esc_attr( wp_json_encode( $days_full ) ); ?>"
				data-label-on="<?php esc_attr_e( 'Karaoke is on right now — the mic is open', 'louies' ); ?>"
				data-label-tonight="<?php esc_attr_e( 'Karaoke tonight from', 'louies' ); ?>"
				data-label-next="<?php esc_attr_e( 'Next karaoke:', 'louies' ); ?>"
				data-label-from="<?php esc_attr_e( 'from', 'louies' ); ?>">
				<span class="karaoke-dot"></span>
				<span class="karaoke-live-text"><?php echo wp_kses_post( $live_text ); ?></span>
			</figcaption>
		</figure>

	</div>
</section>
