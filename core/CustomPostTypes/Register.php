<?php
/**
 * Trait for registering Custom Post Types and Taxonomies.
 *
 * @package Ateso_Dictionary
 */

namespace Ateso_Dictionary\CustomPostTypes;

use Ateso_Dictionary\Base;

trait Register {

	/**
	 * Initialize hooks for registration.
	 */
	public function init_post_type_and_taxonomy() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomy' ) );

		do_action( 'qm/debug', 'registered_post_type_and_taxonomy' );
	}

	/**
	 * Registers a custom post type.
	 *
	 * @param string $post_type The post type key.
	 * @param array  $args      The arguments for the post type.
	 */
	protected function register_custom_post_type( string $post_type, array $args ) {
		register_post_type( $post_type, $args );
	}

	/**
	 * Registers a custom taxonomy.
	 *
	 * @param string       $taxonomy The taxonomy key.
	 * @param array|string $object_type Object types the taxonomy is associated with.
	 * @param array        $args     The arguments for the taxonomy.
	 */
	protected function register_custom_taxonomy( string $taxonomy, $object_type, array $args ) {
		register_taxonomy( $taxonomy, $object_type, $args );
	}
}
