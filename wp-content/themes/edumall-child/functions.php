<?php
defined( 'ABSPATH' ) || exit;

/**
 * Enqueue child scripts
 */
if ( ! function_exists( 'edumall_child_enqueue_scripts' ) ) {
	function edumall_child_enqueue_scripts() {
		wp_enqueue_style( 'edumall-child-style', get_stylesheet_directory_uri() . '/style.css' );
	}
}
add_action( 'wp_enqueue_scripts', 'edumall_child_enqueue_scripts', 15 );
