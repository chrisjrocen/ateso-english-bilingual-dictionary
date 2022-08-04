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

if ( 'DICTIONARY_CUSTOM_URL' ) {
	define( 'DICTIONARY_CUSTOM_URL', plugin_dir_url( __FILE__ ) );
}

require_once( plugin_dir_path( __FILE__ ) . '/core/post-types/ateso.php' );
require_once( plugin_dir_path( __FILE__ ) . '/functions.php' );
require_once( plugin_dir_path( __FILE__ ) . '/core/taxonomies/parts.php' );
require_once( plugin_dir_path( __FILE__ ) . '/core/post-types/ateso-archive.php' );
require_once( plugin_dir_path( __FILE__ ) . '/core/post-types/ateso-words-shortcode.php' );
