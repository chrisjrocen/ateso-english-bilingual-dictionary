<?php
/**********************************************************************************
	 Register taxonomy - genre
 **********************************************************************************/
function ate_eng_register_taxonomy() {
	// genre
	$labels = array(
		'name'              => __('Genres'),
		'singular_name'     => __('Genre'),
		'search_items'      => __('Search genres'),
		'all_items'         => __('All genres'),
		'edit_item'         => __('Edit genre'),
		'update_item'       => __('Update genre'),
		'add_new_item'      => __('Add New genre'),
		'new_item_name'     => __('New genre Name'),
		'menu_name'         => __('Genres'),
	);

	$args = array(
		'labels'            => $labels,
		'hierarchical'      => true,
		'sort'              => true,
		'args'              => array( 'orderby' => 'term_order' ),
		'rewrite'           => array( 'slug' => 'genres' ),
		'show_admin_column' => true,
		'show_in_rest'      => true,
	);

	register_taxonomy( 'ate-eng_genre', array( 'ate-eng_Ateso Word' ), $args );
}
add_action( 'init', 'ate_eng_register_taxonomy' );
