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
$order_common = get_query_var('order_common', '');
if($order_common!=''){
	$delimeter = '0x0x0x0x';
	$delimeter1 = '1x1x1x1x';

	$parameters = explode($delimeter,$order_common);
	$arr = array();
	$i = 0;
	$billing_arr = [];
	$shipping_arr = [];
	$shipping_lines_arr = [];
	$shipping_lines_arr_child = [];
	$leng_line_item = 0;
	$line_items = [];
	$meta_data =[];
	$meta_data_child =[];
	$has_coupon_code = false;
	$meta_data =[];
	$coupon_lines=[];
	$coupon_lines_child=[];
	foreach($parameters as $param){
		if($i==0 || $i==1 ) {
			$ele            = explode( '=', $param );

			$arr[ $ele[0] ] = $ele[1];

		}

		if($i == 2){
			$ele            = explode( '=', $param );
			if($ele[1] == 'true') {
				$arr[ $ele[0] ] = true;
			}
			else {
				$arr[ $ele[0] ] = false;
			}
		}

		if($i == 3 || $i == 4 || $i == 5 || $i == 6 || $i == 7 || $i == 8 || $i == 9 || $i == 10){
			$ele            = explode( '=', $param );
			$billing_arr[ substr($ele[0],8) ] = $ele[1];
		}
		if($i == 11 || $i == 12 || $i == 13 || $i == 14 || $i == 15 || $i == 16 || $i == 17 ){
			$ele            = explode( '=', $param );
			$shipping_arr[ substr($ele[0],9) ] = $ele[1];
		}
		if($i==18 || $i==19 ||  $i==20) {
			$ele            = explode( '=', $param );
			$shipping_lines_arr_child[ $ele[0]] = $ele[1];
		}
		if($i==21) {
			$ele            = explode( '=', $param );
			if($ele[0] == 'length_line_items'){
				$leng_line_item =  (int)$ele[1];
			}
			else{
				echo Woo_Utils::get_respone(400,$data);
			}

		}


		if($i >21 ){
			if($i < 22 +$leng_line_item ){
				$line_item = [];
				$ele            = explode( '=', $param );
				$childs = explode( $delimeter1, $ele[1]);

				$j=0;
				$has_variation = false;
				foreach ($childs as $child){

					if($j==0 || $j==3)
					{
						$line_a            = explode( '!', $child );
						$line_item[$line_a[0]] =(int)$line_a[1];
					}
					if($j==1){
						$line_a            = explode( '!', $child );
						if($line_a=='true'){
							$has_variation = true;
						}
					}
					if($j==2) {
						if($has_variation){
							$line_a            = explode( '!', $child );
							$line_item['variation_id'] =(int)$line_a[1];
						}
					}


					$j++;

				}

				$line_items[] = $line_item;
			}
		}
		if($i>21) {
			if ( $i == 22 + $leng_line_item || $i == 22 + $leng_line_item + 1 ) {

				$ele = explode( '=', $param );
				if ( $i == 22 + $leng_line_item ) {

					$meta_data_child['key'] = $ele[1];
				}
				if ( $i == 22 + $leng_line_item + 1 ) {

					$meta_data_child['value'] = $ele[1];
				}
			}

			if ( $i == 22 + $leng_line_item + 2 ) {

				$ele                = explode( '=', $param );
				$arr['customer_id'] = $ele[1];
			}

			if ( $i == 22 + $leng_line_item + 3 ) {
				$ele = explode( '=', $param );
				if ( $ele[1] == 'true' ) {
					$has_coupon_code = true;
				}
			}
			if ( $i == 22 + $leng_line_item + 4 || $i == 22 + $leng_line_item + 5 || $i == 22 + $leng_line_item + 6 ) {
				if ( $has_coupon_code ) {
					$ele = explode( '=', $param );
					if ( $i == 22 + $leng_line_item + 4 ) {
					//	$coupon_lines['id'] = $ele[1];
					}
					if ( $i == 22 + $leng_line_item + 5 ) {
						$coupon_lines_child['code'] = $ele[1];
					}
					if ( $i == 22 + $leng_line_item + 6 ) {
						//$coupon_lines['discount'] = $ele[1];
					}
				}
			}
		}

			$i++;

	}
	$arr['billing'] = $billing_arr;
	$arr['shipping'] = $shipping_arr;
	$shipping_lines_arr[] = $shipping_lines_arr_child;
	$arr['shipping_lines'] = $shipping_lines_arr;
	$arr['line_items'] = $line_items;
	$meta_data[] = $meta_data_child;
	$arr['meta_data'] = $meta_data;
	if ( $has_coupon_code ) {
		$coupon_lines[] = $coupon_lines_child;

		$arr['coupon_lines'] = $coupon_lines;
	}
	//$data['arr'] = $arr;
	$object_order = $woocommerce->post('orders', $arr);
	$data['data'] =$object_order;
	$data['date_created'] = date('M d, Y', strtotime($object_order->date_created));
	echo Woo_Utils::get_respone(200,$data);
}
else{
	echo Woo_Utils::get_respone(400,$data);
}







