<?php
/**
 * Post types and taxonomies.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function louies_register_post_types() {

	register_post_type( 'louies_event', array(
		'labels' => array(
			'name'               => __( 'Events', 'louies' ),
			'singular_name'      => __( 'Event', 'louies' ),
			'add_new'            => __( 'Add Event', 'louies' ),
			'add_new_item'       => __( 'Add Event', 'louies' ),
			'edit_item'          => __( 'Edit Event', 'louies' ),
			'new_item'           => __( 'New Event', 'louies' ),
			'view_item'          => __( 'View Event', 'louies' ),
			'search_items'       => __( 'Search Events', 'louies' ),
			'not_found'          => __( 'No events yet. Click "Add Event" to create one.', 'louies' ),
			'not_found_in_trash' => __( 'No events in the trash.', 'louies' ),
			'all_items'          => __( 'All Events', 'louies' ),
			'menu_name'          => __( 'Events', 'louies' ),
		),
		'public'        => true,
		'has_archive'   => false,
		'menu_icon'     => 'dashicons-calendar-alt',
		'menu_position' => 20,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'rewrite'       => array( 'slug' => 'event', 'with_front' => false ),
		'show_in_rest'  => true,
	) );

	register_post_type( 'louies_menu_item', array(
		'labels' => array(
			'name'               => __( 'Menu Items', 'louies' ),
			'singular_name'      => __( 'Menu Item', 'louies' ),
			'add_new'            => __( 'Add Menu Item', 'louies' ),
			'add_new_item'       => __( 'Add Menu Item', 'louies' ),
			'edit_item'          => __( 'Edit Menu Item', 'louies' ),
			'all_items'          => __( 'All Menu Items', 'louies' ),
			'not_found'          => __( 'Nothing on the menu yet.', 'louies' ),
			'menu_name'          => __( 'Food & Drink', 'louies' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'menu_icon'     => 'dashicons-list-view',
		'menu_position' => 21,
		'supports'      => array( 'title', 'page-attributes' ),
		'show_in_rest'  => false,
	) );
}
add_action( 'init', 'louies_register_post_types' );

function louies_register_taxonomies() {

	register_taxonomy( 'louies_event_type', 'louies_event', array(
		'labels' => array(
			'name'          => __( 'Event Types', 'louies' ),
			'singular_name' => __( 'Event Type', 'louies' ),
			'menu_name'     => __( 'Event Types', 'louies' ),
			'add_new_item'  => __( 'Add Event Type', 'louies' ),
		),
		'public'            => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'events/type', 'with_front' => false ),
		'show_in_rest'      => true,
	) );

	register_taxonomy( 'louies_menu_section', 'louies_menu_item', array(
		'labels' => array(
			'name'          => __( 'Menu Sections', 'louies' ),
			'singular_name' => __( 'Menu Section', 'louies' ),
			'menu_name'     => __( 'Menu Sections', 'louies' ),
			'add_new_item'  => __( 'Add Menu Section', 'louies' ),
		),
		'public'            => false,
		'show_ui'           => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
	) );
}
add_action( 'init', 'louies_register_taxonomies' );

/**
 * Events and menu items use the classic editor.
 *
 * The block editor pushes our "When & How Much" panel to the very bottom behind
 * a collapsed "Meta Boxes" drawer. On the classic screen it sits directly under
 * the title, which is where somebody adding Friday's band expects it.
 */
add_filter( 'use_block_editor_for_post_type', function ( $use, $post_type ) {
	if ( in_array( $post_type, array( 'louies_event', 'louies_menu_item' ), true ) ) {
		return false;
	}
	return $use;
}, 10, 2 );

/**
 * Order menu sections and items the way the bar wants them, not alphabetically.
 */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( 'louies_menu_item' === $q->get( 'post_type' ) ) {
		$q->set( 'orderby', 'menu_order' );
		$q->set( 'order', 'ASC' );
	}
} );
