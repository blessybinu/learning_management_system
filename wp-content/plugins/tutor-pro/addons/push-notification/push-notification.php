<?php
/*
Plugin Name: Tutor Push Notification
Plugin URI: https://www.themeum.com/product/tutor-certificate
Description: Users will get web push notification 
Author: Themeum
Version: 1.0.0
Author URI: http://themeum.com
Requires at least: 4.5
Tested up to: 4.9
Text Domain: tutor-push-notification
Domain Path: /languages/
*/
if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * Defined the tutor main file
 */
define('TUTOR_PN_VERSION', '1.0.0');
define('TUTOR_PN_FILE', __FILE__);

/**
 * Showing config for addons central lists
 */
add_filter('tutor_addons_lists_config', 'tutor_pn_config');
function tutor_pn_config($config){
	$newConfig = array(
		'name'          => __('Push Notification', 'tutor-pro'),
		'description'   => __('Users will get push notification on specified events.', 'tutor-pro'),
	);

	$basicConfig = (array) TUTOR_PN();
	$newConfig = array_merge($newConfig, $basicConfig);

	$config[plugin_basename( TUTOR_PN_FILE )] = $newConfig;
	return $config;
}

if ( ! function_exists('TUTOR_PN')) {
	function TUTOR_PN() {
		$info = array(
			'path'              => plugin_dir_path( TUTOR_PN_FILE ),
			'url'               => plugin_dir_url( TUTOR_PN_FILE ),
			'basename'          => plugin_basename( TUTOR_PN_FILE ),
			'version'           => TUTOR_PN_VERSION,
			'nonce_action'      => 'tutor_nonce_action',
			'nonce'             => '_wpnonce',
		);
		return (object) $info;
	}
}

include 'classes/init.php';
$tutor = new TUTOR_PN\init();
$tutor->run(); //Boom