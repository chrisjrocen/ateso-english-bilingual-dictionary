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
class Activate extends BaseController {

	/**
	 * Runs on activation hook.
	 *
	 * @return void
	 */
	public static function activate() {
		\ATESO_ENG\Database\Schema::create_tables();
		flush_rewrite_rules();
	}
}
