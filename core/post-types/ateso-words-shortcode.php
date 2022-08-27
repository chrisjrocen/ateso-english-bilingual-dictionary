<?php
/**
 * Shortcode to display Ateso Words a page.
 *
 * @package atesoenglish.
 */

/**
 * Register shortcode to display Ateso Words in a page [ateso-words].
 *
 * @return void
 */


/**
 * Enqueue Styles and Scripts for Slider shortcode.
 *
 * @return void
 */
function chrx_enqueue_scripts() {
	wp_enqueue_style( 'base.css', DICTIONARY_CUSTOM_URL . 'assets/build/css/base.css' , array(), '1.0.0', 'all' );
}
