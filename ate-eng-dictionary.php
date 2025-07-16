<?php
/**
 * Plugin Name: Ateso-English Dictionary
 * Plugin URI:  https://ocenchris.com
 * Description: Ateso-English Bilingual Dictionary
 * Author:      Ocen Chris
 * Author URI:  https://ocenchris.com
 * Version:     1.0.0
 * License:     1.0.0
 * text-domain: ateeng
 *
 * @package atesoenglish.
 */

// If this file is called directly, abort!!!
defined( 'ABSPATH' ) || die( 'No Access!' );

define( 'ATESO_ENG_VERSION', '0.3.0' );

// Require once the Composer Autoload.
if ( file_exists( __DIR__ . '/lib/autoload.php' ) ) {
	require_once __DIR__ . '/lib/autoload.php';
}

/**
 * The code that runs during plugin activation.
 *
 * @return void
 */
function activate_ateso_eng_plugin() {
	ATESO_ENG\Base\Activate::activate();
}
register_activation_hook( __FILE__, 'activate_ateso_eng_plugin' );

/**
 * The code that runs during plugin deactivation.
 *
 * @return void
 */
function deactivate_ateso_eng_plugin() {
	ATESO_ENG\Base\Deactivate::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_ateso_eng_plugin' );

/**
 * Initialize all the core classes of the plugin.
 */
if ( class_exists( 'ATESO_ENG\\Init' ) ) {
	ATESO_ENG\Init::register_services();
}
