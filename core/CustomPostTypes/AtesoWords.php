<?php
/**
 * Register Ateso Word Post Type and associated Taxonomies
 *
 * @package Ateso_Dictionary\CustomPostTypes
 */

namespace Ateso_Dictionary\CustomPostTypes;

use Ateso_Dictionary\Base;

/**
 * Trait for registering Custom Post Types and Taxonomies.
 *
 * @package Ateso_Dictionary\CustomPostTypes
 */
class AtesoWords {

	use Register;

	/**
	 * Constructor to initialize the post type and taxonomy.
	 */
	public function __construct() {
		$this->init_post_type_and_taxonomy();
	}

	/**
	 * Register the Ateso Word post type and taxonomy.
	 *
	 * @return void
	 */
	public function register_post_type() {
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
			'description'         => __( 'Ateso words translated to English', 'ateeng'),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_admin_bar'   => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'can_export'          => true,
			'exclude_from_search' => false,
			'hierarchical'        => false,
			'menu_icon'           => 'dashicons-location-alt',
			'capability_type'     => 'post',
			'rewrite'             => array( 'slug' => 'ateso-words' ),
			'taxonomies'          => array( 'category' ),
			'supports'            => array( 'title', 'custom-fields', 'thumbnail' ),
		);

		$this->register_custom_post_type( 'ateso-words', $args );
	}

	/**
	 * Register the Ateso Part of Speech taxonomy.
	 *
	 * @return void
	 */
	public function register_taxonomy() {
		$labels = array(
			'name'              => __( 'Parts of Speech', 'ateeng' ),
			'singular_name'     => __( 'Part of Speech', 'ateeng' ),
			'search_items'      => __( 'Search Parts of Speech', 'ateeng' ),
			'all_items'         => __( 'All Parts of Speech', 'ateeng' ),
			'edit_item'         => __( 'Edit Part of Speech', 'ateeng' ),
			'update_item'       => __( 'Update Part of Speech', 'ateeng' ),
			'add_new_item'      => __( 'Add New Part of Speech', 'ateeng' ),
			'new_item_name'     => __( 'New Part of Speech', 'ateeng' ),
			'menu_name'         => __( 'Parts of Speech', 'ateeng' ),
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

		$this->register_custom_taxonomy( 'ate-eng_part', array( 'ateso-words' ), $args );
	}
}
