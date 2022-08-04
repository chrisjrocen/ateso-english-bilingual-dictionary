<?php
/**********************************************************************************
	 Register taxonomy - Part
 **********************************************************************************/
function ate_eng_register_taxonomy() {
	// Part
	$labels = array(
		'name'              => __('Parts of Speech'),
		'singular_name'     => __('Part of Speech'),
		'search_items'      => __('Search Parts of Speech'),
		'all_items'         => __('All Parts of Speech'),
		'edit_item'         => __('Edit Part of Speech'),
		'update_item'       => __('Update Part of Speech'),
		'add_new_item'      => __('Add New Part of Speech'),
		'new_item_name'     => __('New Part of Speech'),
		'menu_name'         => __('Parts of Speech'),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'sort'              => true,
		'args'              => array( 'orderby' => 'term_order' ),
		'rewrite'           => array( 'slug' => 'parts' ),
		'show_admin_column' => true,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'ate-eng_part', array( 'ateso-words' ), $args );
}
add_action( 'init', 'ate_eng_register_taxonomy' );
