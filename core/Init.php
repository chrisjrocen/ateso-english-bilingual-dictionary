<?php
/**
 * Initialize classes in the get_services method.
 *
 * @package  ATESO_ENG.
 */

namespace ATESO_ENG;

/**
 * Singleton final class pattern for Init register.
 */
final class Init {

	/**
	 * Store all the classes inside an array.
	 *
	 * @return array Full list of classes.
	 */
	public static function get_services() {
		return array(
			// Database (version checking, table upgrades).
			Database\Schema::class,

			// API.
			Api\RestController::class,

			// Admin.
			Admin\AdminMenu::class,
			Admin\EditForm::class,
			Admin\ImportPage::class,

			// Blocks.
			Blocks\DictionarySearch::class,

			// Frontend.
			Frontend\SingleWord::class,
		);
	}

	/**
	 * Loop through the classes, initialize them.
	 * Call the register() method if it exists.
	 */
	public static function register_services() {
		foreach ( self::get_services() as $class ) {
			$service = self::instantiate( $class );
			if ( method_exists( $service, 'register' ) ) {
				$service->register();
			}
		}
	}

	/**
	 * Initialize the class.
	 *
	 * @param  class $class    class from the services array.
	 * @return class instance  new instance of the class.
	 */
	private static function instantiate( $class ) {
		$service = new $class();

		return $service;
	}
}
