<?php
/**
 * @package AtesoEngDictionary
 */

namespace ATESO_ENG\Admin;

use ATESO_ENG\Base\BaseController;

/**
 * Asset Manager Class
 *
 * Conditionally enqueues admin scripts and styles only on relevant screens.
 * Optimizes performance by loading assets only when needed.
 */
class AssetManager extends BaseController {

	/**
	 * Register the service
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue admin assets conditionally
	 *
	 * @param string $hook The current admin page hook
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on post edit screens
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		// Get current screen
		$screen = get_current_screen();

		// Only load for ateso-words post type
		if ( ! $screen || 'ateso-words' !== $screen->post_type ) {
			return;
		}

		// Enqueue admin JavaScript
		wp_enqueue_script(
			'ateso-admin-metabox',
			$this->plugin_url . 'assets/js/admin-metabox.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		// Localize script with translations and settings
		wp_localize_script(
			'ateso-admin-metabox',
			'atesoAdminMetabox',
			array(
				'confirmRemove' => __( 'Are you sure you want to remove this example?', 'ateso-eng-dictionary' ),
				'addExample'    => __( 'Add Example', 'ateso-eng-dictionary' ),
			)
		);

		// Enqueue admin CSS
		wp_enqueue_style(
			'ateso-admin-metabox',
			$this->plugin_url . 'assets/css/admin-metabox.css',
			array(),
			$this->version
		);
	}
}
