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
 * @package Ateso_Dictionary.
 */

// If this file is called directly, abort!!!
defined( 'ABSPATH' ) || die( 'No Access!' );

// Require once the Composer Autoload.
if ( file_exists( __DIR__ . '/lib/autoload.php' ) ) {
	require_once __DIR__ . '/lib/autoload.php';
}

/**
 *  Runs during plugin activation.
 *
 * @return void
 */
function activate_ateso_dictionary() {
	Ateso_Dictionary\Base\Activate::activate();
}

register_activation_hook( __FILE__, 'activate_ateso_dictionary' );

/**
 *  Runs during plugin deactivation.
 *
 * @return void
 */
function deactivate_ateso_dictionary() {
	Ateso_Dictionary\Base\Deactivate::deactivate();
}

register_deactivation_hook( __FILE__, 'deactivate_ateso_dictionary' );

/**
 * Initialize all the core classes of the plugin.
 */
if ( class_exists( 'Ateso_Dictionary\\Init' ) ) {
	Ateso_Dictionary\Init::register_services();
}
