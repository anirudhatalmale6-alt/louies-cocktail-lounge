<?php
/**
 * Admin list tweaks, so "All Events" reads like a diary instead of a blog roll.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'manage_louies_event_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['louies_when']   = __( 'When', 'louies' );
			$new['louies_repeat'] = __( 'Repeats', 'louies' );
			$new['louies_price']  = __( 'Price', 'louies' );
		}
	}
	unset( $new['date'] );
	return $new;
} );

add_action( 'manage_louies_event_posts_custom_column', function ( $col, $post_id ) {
	$m = louies_event_meta( $post_id );

	if ( 'louies_when' === $col ) {
		if ( ! $m['louies_date'] ) {
			echo '&mdash;';
			return;
		}

		// For a repeating night the stored date is only where the series began.
		// Showing "Thu, Jan 1" next to "Every Saturday" reads like a mistake, so
		// show the next date it actually runs.
		$show = louies_format_date( $m['louies_date'] );
		if ( 'none' !== $m['louies_repeat'] ) {
			$today = louies_today();
			$next  = louies_event_dates( $post_id, $today, $today->modify( '+1 year' ) );
			$show  = $next
				? 'Next: ' . louies_format_date( $next[0]->format( 'Y-m-d' ) )
				: 'Finished';
		}

		echo esc_html( $show );
		if ( $m['louies_time_start'] ) {
			echo '<br><span style="color:#666">' . wp_kses_post( louies_format_time( $m['louies_time_start'], $m['louies_time_end'] ) ) . '</span>';
		}
	}

	if ( 'louies_repeat' === $col ) {
		$label = louies_repeat_label( $m );
		if ( ! $label ) {
			echo '<span style="color:#999">' . esc_html__( 'One-off', 'louies' ) . '</span>';
			return;
		}
		echo esc_html( $label );
		if ( $m['louies_repeat_until'] ) {
			echo '<br><span style="color:#666">until ' . esc_html( louies_format_date( $m['louies_repeat_until'] ) ) . '</span>';
		}
	}

	if ( 'louies_price' === $col ) {
		echo $m['louies_price'] ? esc_html( $m['louies_price'] ) : '&mdash;';
	}
}, 10, 2 );

add_filter( 'manage_edit-louies_event_sortable_columns', function ( $cols ) {
	$cols['louies_when'] = 'louies_when';
	return $cols;
} );

add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() || ! $q->is_main_query() || 'louies_event' !== $q->get( 'post_type' ) ) {
		return;
	}
	if ( ! $q->get( 'orderby' ) || 'louies_when' === $q->get( 'orderby' ) ) {
		$q->set( 'meta_key', '_louies_date' );
		$q->set( 'orderby', 'meta_value' );
		$q->set( 'order', $q->get( 'order' ) ? $q->get( 'order' ) : 'DESC' );
	}
} );

add_filter( 'manage_louies_menu_item_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['louies_price'] = __( 'Price', 'louies' );
		}
	}
	unset( $new['date'] );
	return $new;
} );

add_action( 'manage_louies_menu_item_posts_custom_column', function ( $col, $post_id ) {
	if ( 'louies_price' === $col ) {
		$p = get_post_meta( $post_id, '_louies_price', true );
		echo $p ? esc_html( $p ) : '&mdash;';
	}
}, 10, 2 );

/**
 * A short "how to" on the Events list. Bar staff, not developers, use this screen.
 */
add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-louies_event' !== $screen->id ) {
		return;
	}
	echo '<div class="notice notice-info"><p><strong>' . esc_html__( 'Adding a night:', 'louies' ) . '</strong> '
		. esc_html__( 'click Add Event, type the name, pick the date and time, and if it happens every week tick "Every week" and choose the days. You only ever create it once.', 'louies' )
		. '</p></div>';
} );
