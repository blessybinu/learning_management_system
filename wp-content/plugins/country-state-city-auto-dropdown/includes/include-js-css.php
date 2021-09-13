<?php
/* Include all js and css files */
function tc_csca_embedCssJs() {
	wp_enqueue_script( 'tc_csca-country-auto-script', TC_CSCA_URL . 'assets/js/script.min.js', array( 'jquery' ) );
	wp_localize_script( 'tc_csca-country-auto-script', 'tc_csca_auto_ajax', array( 'ajax_url' => admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('tc_csca_ajax_nonce')) );
	}
add_action( 'wp_enqueue_scripts', 'tc_csca_embedCssJs' );