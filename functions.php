<?php

//Enqueue styles and scripts
  add_action( 'wp_enqueue_scripts', 'chrx_enqueue_scripts' );
function chrx_enqueue_scripts() {
	wp_enqueue_style( 'base.css', DICTIONARY_CUSTOM_URL . 'assets/build/css/base.css' , array(), '1.0.0', 'all' );
}




function get_custom_post_type_template( $archive_template ) {
	global $wp_query;

	if ( is_post_type_archive ( 'ateso-words' ) ) {
		$archive_template = dirname( __FILE__ ) . '/templates/ateso-archive.php';
	}
	return $archive_template;
}

add_filter( 'archive_template', 'get_custom_post_type_template' ) ;
