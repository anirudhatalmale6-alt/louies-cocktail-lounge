<?php
/**
 * Turning stored events into dated occurrences.
 *
 * An event is stored once. A weekly event stores its weekdays and an optional
 * "repeats until" date; the dates themselves are worked out on the fly, so
 * nobody ever has to create 52 posts for karaoke night.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Every meta key this plugin owns, with its default.
 */
function louies_event_fields() {
	return array(
		'_louies_date'          => '',
		'_louies_time_start'    => '',
		'_louies_time_end'      => '',
		'_louies_price'         => '',
		'_louies_ticket_url'    => '',
		'_louies_repeat'        => 'none',
		'_louies_weekdays'      => '',
		'_louies_monthly_nth'   => '1',
		'_louies_monthly_day'   => '5',
		'_louies_repeat_until'  => '',
		'_louies_exceptions'    => '',
		'_louies_featured'      => '',
	);
}

/**
 * Turn the stored "0,3,5" weekday string into a list of integers.
 *
 * Worth its own function because the obvious one-liner is wrong. Weekdays are
 * PHP's 0-6 with SUNDAY AS ZERO, and array_filter() with no callback throws
 * away every falsy value - so a plain array_filter() silently deletes Sunday.
 * A Sunday-only event then produced no occurrences at all, vanished from the
 * weekly grid, and showed its own checkbox unticked in the editor. Three
 * different symptoms, one dropped zero.
 *
 * @param string $raw Comma separated weekday numbers.
 * @return int[] Weekday numbers, 0-6, in order, no duplicates.
 */
function louies_weekday_list( $raw ) {
	$days = array();
	foreach ( explode( ',', (string) $raw ) as $piece ) {
		$piece = trim( $piece );
		if ( '' === $piece || ! is_numeric( $piece ) ) {
			continue;
		}
		$day = (int) $piece;
		if ( $day >= 0 && $day <= 6 && ! in_array( $day, $days, true ) ) {
			$days[] = $day;
		}
	}
	sort( $days );
	return $days;
}

function louies_event_meta( $post_id ) {
	$out = array();
	foreach ( louies_event_fields() as $key => $default ) {
		$value = get_post_meta( $post_id, $key, true );
		$out[ ltrim( $key, '_' ) ] = ( '' === $value || null === $value ) ? $default : $value;
	}
	return $out;
}

function louies_timezone() {
	return wp_timezone();
}

function louies_today() {
	return current_datetime()->setTime( 0, 0, 0 );
}

/**
 * The nth given weekday of a month. $nth is 1-5 or 'last'.
 */
function louies_nth_weekday_of_month( $year, $month, $weekday, $nth ) {
	$tz    = louies_timezone();
	$first = new DateTimeImmutable( sprintf( '%04d-%02d-01', $year, $month ), $tz );

	if ( 'last' === $nth ) {
		$last   = $first->modify( 'last day of this month' );
		$offset = ( (int) $last->format( 'w' ) - (int) $weekday + 7 ) % 7;
		return $last->modify( "-{$offset} days" );
	}

	$offset = ( (int) $weekday - (int) $first->format( 'w' ) + 7 ) % 7;
	$date   = $first->modify( '+' . ( $offset + ( ( (int) $nth - 1 ) * 7 ) ) . ' days' );

	// A "5th Tuesday" does not exist every month.
	if ( (int) $date->format( 'n' ) !== (int) $month ) {
		return null;
	}
	return $date;
}

/**
 * All dates a single event lands on between two dates.
 *
 * @return DateTimeImmutable[]
 */
function louies_event_dates( $post_id, DateTimeImmutable $from, DateTimeImmutable $to ) {
	$meta = louies_event_meta( $post_id );
	$tz   = louies_timezone();

	if ( empty( $meta['louies_date'] ) ) {
		return array();
	}

	$start = DateTimeImmutable::createFromFormat( 'Y-m-d|', $meta['louies_date'], $tz );
	if ( ! $start ) {
		return array();
	}

	$until = null;
	if ( ! empty( $meta['louies_repeat_until'] ) ) {
		$until = DateTimeImmutable::createFromFormat( 'Y-m-d|', $meta['louies_repeat_until'], $tz );
	}

	// An open-ended repeat still needs a horizon, or the loop never ends.
	$horizon = $to;
	if ( $until && $until < $horizon ) {
		$horizon = $until;
	}

	$skip = array_filter( array_map( 'trim', explode( ',', (string) $meta['louies_exceptions'] ) ) );
	$out  = array();

	switch ( $meta['louies_repeat'] ) {

		case 'weekly':
			$days = louies_weekday_list( $meta['louies_weekdays'] );
			if ( ! $days ) {
				$days = array( (int) $start->format( 'w' ) );
			}
			$cursor = $from > $start ? $from : $start;
			while ( $cursor <= $horizon ) {
				if ( in_array( (int) $cursor->format( 'w' ), $days, true ) && $cursor >= $start ) {
					$out[] = $cursor;
				}
				$cursor = $cursor->modify( '+1 day' );
			}
			break;

		case 'monthly':
			$cursor = ( $from > $start ? $from : $start )->modify( 'first day of this month' );
			$stop   = $horizon->modify( 'first day of next month' );
			while ( $cursor < $stop ) {
				$hit = louies_nth_weekday_of_month(
					(int) $cursor->format( 'Y' ),
					(int) $cursor->format( 'n' ),
					(int) $meta['louies_monthly_day'],
					$meta['louies_monthly_nth']
				);
				if ( $hit && $hit >= $start && $hit >= $from && $hit <= $horizon ) {
					$out[] = $hit;
				}
				$cursor = $cursor->modify( '+1 month' );
			}
			break;

		default: // 'none' - a one-off.
			if ( $start >= $from && $start <= $to ) {
				$out[] = $start;
			}
			break;
	}

	if ( $skip ) {
		$out = array_values( array_filter( $out, function ( $d ) use ( $skip ) {
			return ! in_array( $d->format( 'Y-m-d' ), $skip, true );
		} ) );
	}

	return $out;
}

/**
 * Every occurrence of every published event in a window, sorted by when it starts.
 *
 * @param string $from  Y-m-d, inclusive.
 * @param string $to    Y-m-d, inclusive.
 * @param array  $args  limit (int), type (event-type slug), featured_only (bool),
 *                      unique (bool - only the soonest date per event),
 *                      skip_weekly (bool - leave the weekly regulars out).
 * @return array[] Each: post_id, post, date (Y-m-d), sort (Y-m-d H:i), time_start, time_end, price, is_repeat.
 */
function louies_get_occurrences( $from, $to, $args = array() ) {
	$args = wp_parse_args( $args, array(
		'limit'         => 0,
		'type'          => '',
		'featured_only' => false,
		'unique'        => false,
		'skip_weekly'   => false,
	) );

	$tz        = louies_timezone();
	$from_date = DateTimeImmutable::createFromFormat( 'Y-m-d|', $from, $tz );
	$to_date   = DateTimeImmutable::createFromFormat( 'Y-m-d|', $to, $tz );
	if ( ! $from_date || ! $to_date || $to_date < $from_date ) {
		return array();
	}

	$query = array(
		'post_type'      => 'louies_event',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	);

	if ( $args['type'] ) {
		$query['tax_query'] = array( array(
			'taxonomy' => 'louies_event_type',
			'field'    => 'slug',
			'terms'    => $args['type'],
		) );
	}

	if ( $args['featured_only'] ) {
		$query['meta_query'] = array( array( 'key' => '_louies_featured', 'value' => '1' ) );
	}

	$occurrences = array();

	foreach ( get_posts( $query ) as $post ) {
		$meta = louies_event_meta( $post->ID );

		if ( $args['skip_weekly'] && 'weekly' === $meta['louies_repeat'] ) {
			continue;
		}

		$dates = louies_event_dates( $post->ID, $from_date, $to_date );
		if ( $args['unique'] ) {
			$dates = array_slice( $dates, 0, 1 );
		}

		foreach ( $dates as $date ) {
			$time = $meta['louies_time_start'] ? $meta['louies_time_start'] : '00:00';
			$occurrences[] = array(
				'post_id'    => $post->ID,
				'post'       => $post,
				'date'       => $date->format( 'Y-m-d' ),
				'sort'       => $date->format( 'Y-m-d' ) . ' ' . $time,
				'time_start' => $meta['louies_time_start'],
				'time_end'   => $meta['louies_time_end'],
				'price'      => $meta['louies_price'],
				'ticket_url' => $meta['louies_ticket_url'],
				'is_repeat'  => 'none' !== $meta['louies_repeat'],
				'meta'       => $meta,
			);
		}
	}

	usort( $occurrences, function ( $a, $b ) {
		return strcmp( $a['sort'], $b['sort'] );
	} );

	if ( $args['limit'] > 0 ) {
		$occurrences = array_slice( $occurrences, 0, (int) $args['limit'] );
	}

	return $occurrences;
}

/**
 * The next N occurrences from today onwards.
 */
function louies_upcoming( $limit = 8, $args = array() ) {
	$today = louies_today();
	return louies_get_occurrences(
		$today->format( 'Y-m-d' ),
		$today->modify( '+1 year' )->format( 'Y-m-d' ),
		array_merge( $args, array( 'limit' => $limit ) )
	);
}

/**
 * "Thu, Jul 2" style. Adds the year when it isn't the current one.
 */
function louies_format_date( $ymd, $long = false ) {
	$tz   = louies_timezone();
	$date = DateTimeImmutable::createFromFormat( 'Y-m-d|', $ymd, $tz );
	if ( ! $date ) {
		return '';
	}
	$this_year = $date->format( 'Y' ) === current_datetime()->format( 'Y' );
	if ( $long ) {
		return $date->format( $this_year ? 'l, F j' : 'l, F j, Y' );
	}
	return $date->format( $this_year ? 'D, M j' : 'D, M j, Y' );
}

/**
 * "9:00 pm - 1:30 am". Times are stored as 24h so sorting works.
 */
function louies_format_time( $start, $end = '' ) {
	$fmt = function ( $hm ) {
		if ( ! $hm ) {
			return '';
		}
		$t = DateTimeImmutable::createFromFormat( 'H:i', $hm, louies_timezone() );
		return $t ? strtolower( $t->format( 'g:i a' ) ) : $hm;
	};
	$a = $fmt( $start );
	$b = $fmt( $end );
	if ( $a && $b ) {
		return $a . ' &ndash; ' . $b;
	}
	return $a ? $a : $b;
}

/**
 * Plain-English version of the repeat rule, for the event page.
 */
function louies_repeat_label( $meta ) {
	$names = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

	if ( 'weekly' === $meta['louies_repeat'] ) {
		$days = louies_weekday_list( $meta['louies_weekdays'] );
		sort( $days );
		$labels = array();
		foreach ( $days as $d ) {
			if ( isset( $names[ $d ] ) ) {
				$labels[] = $names[ $d ];
			}
		}
		if ( ! $labels ) {
			return 'Every week';
		}
		if ( count( $labels ) === 7 ) {
			return 'Every day';
		}
		// "Every Wednesday", "Every Friday & Saturday", "Every Wed, Thu & Fri".
		return 'Every ' . wp_sprintf_l( '%l', $labels );
	}

	if ( 'monthly' === $meta['louies_repeat'] ) {
		$nth = 'last' === $meta['louies_monthly_nth']
			? 'last'
			: ( array( 1 => 'first', 2 => 'second', 3 => 'third', 4 => 'fourth', 5 => 'fifth' )[ (int) $meta['louies_monthly_nth'] ] ?? 'first' );
		$day = $names[ (int) $meta['louies_monthly_day'] ] ?? 'day';
		return 'The ' . $nth . ' ' . $day . ' of every month';
	}

	return '';
}
