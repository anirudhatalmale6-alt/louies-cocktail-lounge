<?php
/**
 * Tests for the recurrence engine.
 *
 * Run from the WordPress root:  wp eval-file tests/recurrence-tests.php
 *
 * These matter more than they look. The whole reason this site does not pay an
 * annual licence for The Events Calendar Pro is that the repeat logic lives
 * here, so if it is wrong the bar advertises nights it isn't open for.
 */

if ( ! defined( 'WP_CLI' ) ) {
	die( "Run this through wp-cli.\n" );
}

// wp eval-file runs this file inside a function, so top-level variables here are
// NOT globals - `global $pass` in t_ok() would bind to a different, empty one.
// The counters have to be reached explicitly, or the run reports "0 passed,
// 0 failed" and exits 0 no matter how many assertions fail.
$GLOBALS['t_pass'] = 0;
$GLOBALS['t_fail'] = 0;

function t_ok( $label, $got, $want ) {
	$g = is_array( $got ) ? implode( ',', $got ) : (string) $got;
	$w = is_array( $want ) ? implode( ',', $want ) : (string) $want;
	if ( $g === $w ) {
		$GLOBALS['t_pass']++;
		WP_CLI::log( "  ok   {$label}" );
	} else {
		$GLOBALS['t_fail']++;
		WP_CLI::log( "  FAIL {$label}\n        got:  {$g}\n        want: {$w}" );
	}
}

/** Make a throwaway event, run it, delete it. */
function t_dates( $meta, $from, $to ) {
	$id = wp_insert_post( array(
		'post_type'   => 'louies_event',
		'post_title'  => 'test event',
		'post_status' => 'publish',
	) );
	foreach ( louies_event_fields() as $key => $default ) {
		update_post_meta( $id, $key, $meta[ ltrim( $key, '_' ) ] ?? $default );
	}
	$tz  = wp_timezone();
	$out = louies_event_dates(
		$id,
		DateTimeImmutable::createFromFormat( 'Y-m-d|', $from, $tz ),
		DateTimeImmutable::createFromFormat( 'Y-m-d|', $to, $tz )
	);
	wp_delete_post( $id, true );
	return array_map( function ( $d ) { return $d->format( 'Y-m-d' ); }, $out );
}

WP_CLI::log( "\nlouies_weekday_list - the zero problem" );

// Sunday is 0, and array_filter() would eat it. This is the bug that made a
// Sunday event invisible in three separate places at once.
t_ok( 'sunday alone survives',        louies_weekday_list( '0' ),       array( 0 ) );
t_ok( 'sunday inside a list',         louies_weekday_list( '0,3,6' ),   array( 0, 3, 6 ) );
t_ok( 'empty string is no days',      louies_weekday_list( '' ),        array() );
t_ok( 'spaces are tolerated',         louies_weekday_list( ' 1 , 5 ' ), array( 1, 5 ) );
t_ok( 'duplicates collapse',          louies_weekday_list( '2,2,2' ),   array( 2 ) );
t_ok( 'out of range is dropped',      louies_weekday_list( '0,7,9' ),   array( 0 ) );
t_ok( 'junk is dropped',              louies_weekday_list( 'x,,3' ),    array( 3 ) );
t_ok( 'order is normalised',          louies_weekday_list( '6,0,3' ),   array( 0, 3, 6 ) );

WP_CLI::log( "\nWeekly" );

t_ok(
	'sunday-only weekly produces sundays',
	t_dates( array( 'louies_date' => '2026-01-01', 'louies_repeat' => 'weekly', 'louies_weekdays' => '0' ), '2026-09-01', '2026-09-30' ),
	array( '2026-09-06', '2026-09-13', '2026-09-20', '2026-09-27' )
);

t_ok(
	'karaoke wed-sat, one week',
	t_dates( array( 'louies_date' => '2026-01-01', 'louies_repeat' => 'weekly', 'louies_weekdays' => '3,4,5,6' ), '2026-09-07', '2026-09-13' ),
	array( '2026-09-09', '2026-09-10', '2026-09-11', '2026-09-12' )
);

t_ok(
	'nothing before the start date',
	t_dates( array( 'louies_date' => '2026-09-10', 'louies_repeat' => 'weekly', 'louies_weekdays' => '4' ), '2026-09-01', '2026-09-30' ),
	array( '2026-09-10', '2026-09-17', '2026-09-24' )
);

t_ok(
	'stops on the until date',
	t_dates( array( 'louies_date' => '2026-09-07', 'louies_repeat' => 'weekly', 'louies_weekdays' => '1', 'louies_repeat_until' => '2026-09-21' ), '2026-09-01', '2026-12-31' ),
	array( '2026-09-07', '2026-09-14', '2026-09-21' )
);

t_ok(
	'skip dates are honoured',
	t_dates( array( 'louies_date' => '2026-01-01', 'louies_repeat' => 'weekly', 'louies_weekdays' => '5', 'louies_exceptions' => '2026-09-11' ), '2026-09-01', '2026-09-30' ),
	array( '2026-09-04', '2026-09-18', '2026-09-25' )
);

t_ok(
	'no weekdays ticked falls back to the start day',
	t_dates( array( 'louies_date' => '2026-09-02', 'louies_repeat' => 'weekly', 'louies_weekdays' => '' ), '2026-09-01', '2026-09-23' ),
	array( '2026-09-02', '2026-09-09', '2026-09-16', '2026-09-23' )
);

WP_CLI::log( "\nMonthly" );

t_ok(
	'geo jam - last saturday of the month',
	t_dates( array( 'louies_date' => '2026-01-31', 'louies_repeat' => 'monthly', 'louies_monthly_nth' => 'last', 'louies_monthly_day' => '6' ), '2026-09-01', '2026-12-31' ),
	array( '2026-09-26', '2026-10-31', '2026-11-28', '2026-12-26' )
);

t_ok(
	'geo jam next date is the 26th of september',
	array_slice( t_dates( array( 'louies_date' => '2026-01-31', 'louies_repeat' => 'monthly', 'louies_monthly_nth' => 'last', 'louies_monthly_day' => '6' ), '2026-09-03', '2026-12-31' ), 0, 1 ),
	array( '2026-09-26' )
);

t_ok(
	'first monday of each month',
	t_dates( array( 'louies_date' => '2026-01-01', 'louies_repeat' => 'monthly', 'louies_monthly_nth' => '1', 'louies_monthly_day' => '1' ), '2026-09-01', '2026-11-30' ),
	array( '2026-09-07', '2026-10-05', '2026-11-02' )
);

// September 2026 has only four Sundays, so a "5th Sunday" must not invent one.
t_ok(
	'a fifth sunday that does not exist is skipped',
	t_dates( array( 'louies_date' => '2026-01-01', 'louies_repeat' => 'monthly', 'louies_monthly_nth' => '5', 'louies_monthly_day' => '0' ), '2026-09-01', '2026-09-30' ),
	array()
);

WP_CLI::log( "\nOne-offs" );

t_ok(
	'a one-off appears once',
	t_dates( array( 'louies_date' => '2026-09-26', 'louies_repeat' => 'none' ), '2026-09-01', '2026-09-30' ),
	array( '2026-09-26' )
);

t_ok(
	'a one-off outside the window does not appear',
	t_dates( array( 'louies_date' => '2026-08-26', 'louies_repeat' => 'none' ), '2026-09-01', '2026-09-30' ),
	array()
);

WP_CLI::log( "\nTermination" );

$t0 = microtime( true );
$n  = count( t_dates( array( 'louies_date' => '2020-01-01', 'louies_repeat' => 'weekly', 'louies_weekdays' => '0,6' ), '2026-01-01', '2027-12-31' ) );
$el = microtime( true ) - $t0;
t_ok( 'an open-ended weekly terminates', ( $el < 2 && $n > 200 ) ? 'yes' : "no ({$n} dates in {$el}s)", 'yes' );

WP_CLI::log( "\nThe live site" );

$sun = get_posts( array( 'post_type' => 'louies_event', 'name' => 'sunday-football-and-pool', 'posts_per_page' => 1 ) );
t_ok( 'the sunday event exists', $sun ? 'yes' : 'no', 'yes' );

if ( $sun ) {
	$grid = louies_weekly_grid();
	$names = array_map( function ( $e ) { return $e['post']->post_title; }, $grid[0] );
	t_ok( 'and it lands in the sunday column', $names, array( 'Sunday Football Games and Pool' ) );
}

$next = louies_get_occurrences( '2026-09-03', '2027-03-31', array( 'unique' => true, 'skip_weekly' => true, 'limit' => 6 ) );
t_ok(
	'"next up" holds only geo jam',
	array_map( function ( $o ) { return $o['post']->post_title . ' ' . $o['date']; }, $next ),
	array( 'Geo Jam Open Mic 2026-09-26' )
);

// A self-check on the harness itself. If this run reported nothing at all, the
// counters are broken again and a green result would mean nothing.
if ( 0 === $GLOBALS['t_pass'] + $GLOBALS['t_fail'] ) {
	WP_CLI::error( 'no assertions were counted - the test harness is broken' );
}

WP_CLI::log( sprintf( "\n%d passed, %d failed\n", $GLOBALS['t_pass'], $GLOBALS['t_fail'] ) );
if ( $GLOBALS['t_fail'] ) {
	WP_CLI::error( "{$GLOBALS['t_fail']} failing" );
}
