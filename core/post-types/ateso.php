<?php
/**
 * Add Custom Post Type.
 *
 * @package atesoenglish.
 */

/*****************************************************************************
Register an extra post type for content with taxonomy
 *****************************************************************************/

function ate_eng_register_post_type() {
	// Ateso Word 
	$labels = array(
		'name'               => __( 'Ateso Words', 'ateeng' ),
		'singular_name'      => __( 'Ateso Word', 'ateeng' ),
		'add_new'            => __( 'New Ateso Word', 'ateeng' ),
		'add_new_item'       => __( 'Add New Ateso Word', 'ateeng' ),
		'edit_item'          => __( 'Edit Ateso Word', 'ateeng' ),
		'new_item'           => __( 'New Ateso Word', 'ateeng' ),
		'view_item'          => __( 'View Ateso Word', 'ateeng' ),
		'search_items'       => __( 'Search Ateso Words', 'ateeng' ),
		'not_found'          => __( 'No Ateso Word Found', 'ateeng' ),
		'not_found_in_trash' => __( 'No Ateso Word found in Trash', 'ateeng' ),
	);
	$args = array(
		'label'               => __( 'Ateso Words', 'ateeng' ),
		'labels'              => $labels,
		'description'         => __( 'Ateso words translated to english', 'ateeng'),
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'has_archive'         => false,
		'public'              => true,
		'publicly_queryable'  => true,
		'can_export'          => true,
		'show_in_rest'        => true,
		'exclude_from_search' => false,
		'hierarchical'        => false,
		'menu_icon'           => 'dashicons-location-alt',
		'capability_type'     => 'post',
		'rewrite'             => array( 'slug' => 'ateso-words' ),
		'supports'            => array( 
			'title',
			'editor',
			'custom-fields',
			'thumbnail',
		),

	);
	register_post_type( 'ateso-words', $args );
}
add_action( 'init', 'ate_eng_register_post_type' );
