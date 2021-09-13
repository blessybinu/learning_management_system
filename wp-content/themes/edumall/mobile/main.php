<?php


use edumallmobile\woocommerce\Edumall_Mobile_Woo;

defined( 'ABSPATH' ) || exit;
define( 'EDUMALL_MOBILE_DIR', get_template_directory() . DS . 'mobile' );
define( 'EDUMALL_MOBILE_INCLUDES_DIR', EDUMALL_MOBILE_DIR . DS . 'includes' );
define( 'EM_ENDPOINT', 'edumall_mobile/v1' );

require_once EDUMALL_MOBILE_INCLUDES_DIR . DS . 'base-plugin.php';
require_once EDUMALL_MOBILE_DIR . DS . 'woocommerce/woo_base.php';


add_action( 'init', 'init_main' );

function init_main() {
	$plugin = edumallmobile\Edumall_Mobile_Base_Plugin::instance();
	$plugin->initialize();
	$woo_edumall = Edumall_Mobile_Woo::instance();

	$woo_edumall->initialize();
}

