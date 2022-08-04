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
function register_ateso_words_shortcode() {
	add_shortcode( 'ateso-words', 'render_ateso_words_page' );
}

add_action( 'init', 'register_ateso_words_shortcode' );

/**
 * Enqueue Styles and Scripts for Slider shortcode.
 *
 * @return void
 */
function chrx_enqueue_scripts() {
	wp_enqueue_style( 'base.css', DICTIONARY_CUSTOM_URL . 'assets/build/css/base.css' , array(), '1.0.0', 'all' );

}

add_action( 'wp_enqueue_scripts', 'chrx_enqueue_scripts', 10 );
