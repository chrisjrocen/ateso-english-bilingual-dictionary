<?php
/**
 * Base Controller.
 *
 * @package ATESO_ENG
 */

namespace ATESO_ENG\Base;

/**
 * Base Controller used for central setup and vars.
 */
class BaseController {

	/**
	 * Plugin Path
	 *
	 * @var string
	 */
	public $plugin_path;

	/**
	 * Plugin URL
	 *
	 * @var string
	 */
	public $plugin_url;

	/**
	 * Plugin Reference
	 *
	 * @var string
	 */
	public $plugin;

	/**
	 * Declare all the variables for the class.
	 */
	public function __construct() {

		// Generic Variables.
		$this->plugin_path = trailingslashit( plugin_dir_path( dirname( __DIR__, 1 ) ) );
		$this->plugin_url  = trailingslashit( plugin_dir_url( dirname( __DIR__, 1 ) ) );
		$this->plugin      = plugin_basename( dirname( __DIR__, 2 ) ) . '/ateso-eng-dictionary.php';
	}
}
