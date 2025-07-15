<?php

/**
 * Register Registration Form Block
 *
 * @package ATESO_ENG
 */

namespace ATESO_ENG\Blocks;

use ATESO_ENG\Base\BaseController;

/**
 * Handle all the blocks required for Registration Form.
 */
class AtesoWords extends BaseController {

	/**
	 * Register function is called by default to get the class running
	 *
	 * @return void
	 */
	public function register() {
			add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Render callback for the Ateso Words block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function get_form( $attributes ) {

		do_action( 'qm/debug', $attributes );

		return 'here';
	}

	/**
	 * Register block function called by init hook
	 *
	 * @return void
	 */
	public function register_block() {
		register_block_type_from_metadata(
			$this->plugin_path . 'build/ateso-words/',
			array(
				'render_callback' => array( $this, 'get_form' ),
			)
		);
	}
}
