<?php 

//Enqueue styles and scripts
  add_action( 'wp_enqueue_scripts', 'chrx_enqueue_scripts' );
function chrx_enqueue_scripts() {
	wp_enqueue_style( 'base.css', DICTIONARY_CUSTOM_URL . 'assets/build/css/base.css' , array(), '1.0.0', 'all' );
}
