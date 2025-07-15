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
function activate_mrksuperblocks_jobs_addon_plugin() {
	ATESO_ENG\Base\Activate::activate();
}
register_activation_hook( __FILE__, 'activate_mrksuperblocks_jobs_addon_plugin' );

/**
 * The code that runs during plugin deactivation.
 *
 * @return void
 */
function deactivate_ates_eng_plugin() {
	ATESO_ENG\Base\Deactivate::deactivate();
}
register_deactivation_hook( __FILE__, 'deactivate_ates_eng_plugin' );

/**
 * Initialize all the core classes of the plugin.
 */
if ( class_exists( 'ATESO_ENG\\Init' ) ) {
	ATESO_ENG\Init::register_services();
}




// if ( 'DICTIONARY_CUSTOM_URL' ) {
// 	define( 'DICTIONARY_CUSTOM_URL', plugin_dir_url( __FILE__ ) );
// }

// //includes
// require_once( plugin_dir_path( __FILE__ ) . '/core/post-types/ateso.php' );
// require_once( plugin_dir_path( __FILE__ ) . '/functions.php' );
// //require_once( plugin_dir_path( __FILE__ ) . '/ateso-archive.php' );

// //Hooks and Filters

// add_action( 'init', 'ate_eng_register_post_type' );

// //Shortcodes

// add_shortcode( 'ateso-words', 'render_ateso_words_page' );
