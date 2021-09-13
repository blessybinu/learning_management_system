<?php
require_once('wp-load.php');
require get_template_directory(). '/mobile/woocommerce/vendor/autoload.php';
require  get_template_directory().'/mobile/woocommerce/woo_utils.php';
use Automattic\WooCommerce\Client;
use edumallmobile\woocommerce\Woo_Utils;

$woocommerce = new Client(
	site_url(),
	'ck_c2e48a171755bf8f7d54cf146f5dbca85fe7e177',
	'cs_3308a3621c93382e4f69aa41e806da887613fb51',
	[
		'wp_api'     => true,
		'version'    => 'wc/v3',
		'query_string_auth' => true,
	]
);

$data =[];
$order_id = (int)get_query_var('order_id', 0);
if($order_id!=0)
{
	$data['order_id'] = $order_id;
	$data['body'] = $woocommerce->get('orders');
}

echo Woo_Utils::get_respone(200,$data);

