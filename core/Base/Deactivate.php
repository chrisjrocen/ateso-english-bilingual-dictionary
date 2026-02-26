<?php
/**
 * Plugin Activation methods.
 *
 * @package  ATESO_ENG
 */

namespace ATESO_ENG\Base;

/**
 * Run plugin activation methods.
 */
class Deactivate extends BaseController {

	/**
	 * Runs on activation hook.
	 *
	 * @return void
	 */
	public static function deactivate() {
		flush_rewrite_rules();
		delete_transient( 'ateso_dict_wotd' );
	}
}
