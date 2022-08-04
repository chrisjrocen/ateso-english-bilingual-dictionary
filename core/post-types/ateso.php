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
		'name'               => __( 'Ateso Words' ),
		'singular_name'      => __( 'Ateso Word' ),
		'add_new'            => __( 'New Ateso Word' ),
		'add_new_item'       => __( 'Add New Ateso Word' ),
		'edit_item'          => __( 'Edit Ateso Word' ),
		'new_item'           => __( 'New Ateso Word' ),
		'view_item'          => __( 'View Ateso Word' ),
		'search_items'       => __( 'Search Ateso Words' ),
		'not_found'          => __( 'No Ateso Word Found' ),
		'not_found_in_trash' => __( 'No Ateso Word found in Trash' ),
	);
	$args = array(
		'labels'       => $labels,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'has_archive'         => true,
		'public'              => true,
		'publicly_queryable'  => true,
		'can_export'          => true,
		'show_in_rest'        => true,
		'exclude_from_search' => false,
		'menu_icon'           => 'dashicons-location-alt',
		'hierarchical' => false,
		'supports'     => array( 
			'title',
			'editor',
			'excerpt',
			'custom-fields',
			'thumbnail',
			'page-attributes',
		),

	);
	register_post_type( 'ateso-words', $args );
}
add_action( 'init', 'ate_eng_register_post_type' );
