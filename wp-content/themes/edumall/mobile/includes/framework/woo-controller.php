<?php
namespace edumallmobile\framework;


class Edumall_Woo_Controller {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public function isActive(){
		$monetize_by = tutils()->get_option('monetize_by');
		if ($monetize_by !== 'wc') {
			return false;
		}
		return true;
	}

	public function get_quantity_from_cart($user_id) {
		if($this->isActive()) {
			$session_handler = new \WC_Session_Handler();
			$session         = $session_handler->get_session( $user_id );
			$cart_items      = maybe_unserialize( $session['cart'] );

			return array_sum( wp_list_pluck( $cart_items, 'quantity' ) );
		}
		return 0;
	}


	public function get_variation_product($course_id){

		$object = new \stdClass();

		$product_id = tutor_utils()->get_course_product_id($course_id);
		$monetize_by = tutor_utils()->get_option('monetize_by');
		if ( $product_id ) {
			if ( $monetize_by === 'wc' && tutor_utils()->has_wc() ) {

				$regular_price = get_post_meta( $product_id, '_regular_price', true );
				$sale_price    = get_post_meta( $product_id, '_sale_price', true );
				$object->price =$sale_price;
				if($sale_price == 0){
					$object->price =$regular_price;
				}

			} elseif ( $monetize_by === 'edd' && tutor_utils()->has_edd() ) {
				$regular_price = get_post_meta( $product_id, 'edd_price', true );
				$sale_price    = get_post_meta( $product_id, 'edd_price', true );
				$object->price =$sale_price;
				if($sale_price == 0){
					$object->price =$regular_price;
				}
			}
		}
		$object->product_id = $product_id;
		$object->varitaion_id = 0;
		$object->has_varitaion = false;
		$product           = wc_get_product( $product_id );


		if ( $product && 'variation' === $product->get_type() ) {

			$object->varitaion_id = $product_id;
			$object->product_id   = $product->get_parent_id() ;
			$object->attributes   = $product->get_variation_attributes();
			$object->has_varitaion = true;

		}

		$course = get_post( $course_id );
		$object->name = esc_attr( $course->post_title );
		$builder_course_img_src = tutor()->url . 'assets/images/placeholder-course.jpg';
		$_thumbnail_url         = get_the_post_thumbnail_url( $course_id );
		if($_thumbnail_url){
			$object->thumbnail =  $_thumbnail_url ;
		}else {
			$object->thumbnail =  $builder_course_img_src;
		}

		return $object;

	}

	public function get_coupon_code($code){
		$object = new \stdClass();
		$couponcode= $this->get_coupon_by_code( $code);
		if($couponcode) {
			$object->coupon = $couponcode;
			return $object;
		}
		return false;

	}

	/**
	 * Get the coupon for the given code
	 *
	 * @since 2.1
	 * @param string $code the coupon code
	 * @param string $fields fields to include in response
	 * @return int|WP_Error
	 */
	public function get_coupon_by_code( $code, $fields = null ) {
		global $wpdb;

			$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' ORDER BY post_date DESC LIMIT 1;", $code ) );

			if ( is_null( $id ) ) {
				return false;
			}

			return $this->get_coupon( $id, $fields );

	}

	/**
	 * Get the coupon for the given ID
	 *
	 * @since 2.1
	 * @param int $id the coupon ID
	 * @param string $fields fields to include in response
	 * @return array|WP_Error
	 */
	public function get_coupon( $id, $fields = null ) {

			$coupon = new \WC_Coupon( $id );

			if ( 0 === $coupon->get_id() ) {
				return false;
			}

			$coupon_data = array(
				'id'                           => $coupon->get_id(),
				'code'                         => $coupon->get_code(),
				'type'                         => $coupon->get_discount_type(),
				'amount'                       => wc_format_decimal( $coupon->get_amount(), 2 ),
				'individual_use'               => $coupon->get_individual_use(),
				'product_ids'                  => array_map( 'absint', (array) $coupon->get_product_ids() ),
				'exclude_product_ids'          => array_map( 'absint', (array) $coupon->get_excluded_product_ids() ),
				'usage_limit'                  => $coupon->get_usage_limit() ? $coupon->get_usage_limit() : null,
				'usage_limit_per_user'         => $coupon->get_usage_limit_per_user() ? $coupon->get_usage_limit_per_user() : null,
				'limit_usage_to_x_items'       => (int) $coupon->get_limit_usage_to_x_items(),
				'usage_count'                  => (int) $coupon->get_usage_count(),
				'expiry_date'                  => $coupon->get_date_expires() ? $this->format_datetime( $coupon->get_date_expires()->getTimestamp() ) : null, // API gives UTC times.
				'enable_free_shipping'         => $coupon->get_free_shipping(),
				'product_category_ids'         => array_map( 'absint', (array) $coupon->get_product_categories() ),
				'exclude_product_category_ids' => array_map( 'absint', (array) $coupon->get_excluded_product_categories() ),
				'exclude_sale_items'           => $coupon->get_exclude_sale_items(),
				'minimum_amount'               => wc_format_decimal( $coupon->get_minimum_amount(), 2 ),
				'maximum_amount'               => wc_format_decimal( $coupon->get_maximum_amount(), 2 ),
				'customer_emails'              => $coupon->get_email_restrictions(),
				'description'                  => $coupon->get_description(),
			);

			return $coupon_data;

	}

	/**
	 * Format a unix timestamp or MySQL datetime into an RFC3339 datetime
	 *
	 * @since 2.1
	 * @param int|string $timestamp unix timestamp or MySQL datetime
	 * @param bool $convert_to_utc
	 * @param bool $convert_to_gmt Use GMT timezone.
	 * @return string RFC3339 datetime
	 */
	public function format_datetime( $timestamp, $convert_to_utc = false, $convert_to_gmt = false ) {
		if ( $convert_to_gmt ) {
			if ( is_numeric( $timestamp ) ) {
				$timestamp = date( 'Y-m-d H:i:s', $timestamp );
			}

			$timestamp = get_gmt_from_date( $timestamp );
		}

		if ( $convert_to_utc ) {
			$timezone = new \DateTimeZone( wc_timezone_string() );
		} else {
			$timezone = new \DateTimeZone( 'UTC' );
		}

		try {

			if ( is_numeric( $timestamp ) ) {
				$date = new \DateTime( "@{$timestamp}" );
			} else {
				$date = new DateTime( $timestamp, $timezone );
			}

			// convert to UTC by adjusting the time based on the offset of the site's timezone
			if ( $convert_to_utc ) {
				$date->modify( -1 * $date->getOffset() . ' seconds' );
			}
		} catch ( Exception $e ) {

			$date = new DateTime( '@0' );
		}

		return $date->format( 'Y-m-d\TH:i:s\Z' );
	}


	public function get_checkout_info(){
		$object = new \stdClass();
		$country = WC()->countries->get_allowed_countries();
		if($country) {
			$arr = [];
			foreach ($country as $ckey => $cvalue){
				$object_child = new \stdClass();
				$object_child->key=$ckey;
				$object_child->value=$cvalue;
				$arr[] =$object_child;

			}

			$object->countries = $arr;

		}
		else {
			$object->countries = [];
		}
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		if ( ! empty( $available_gateways ) ) {
			$payments = [];
			foreach ( $available_gateways as $gateway ) {
				$payment_object = new \stdClass();
				$payment_object->id = $gateway->id;
				$payment_object->title = $gateway->title;
				$payment_object->description = $gateway->description;
				$payments[] = $payment_object;

			}
			$object->available_gateways =$payments;
		}
		else {
			$object->available_gateways = [];
		}


		return $object;

	}



	/**
	 * Sees if the customer has entered enough data to calc the shipping yet.
	 *
	 * @return bool
	 */
	public function get_shipping_info($user_id) {
		$object = new \stdClass();
		if ( ! wc_shipping_enabled()) {
			$object->type = 0;


		}

		if ( 'yes' === get_option( 'woocommerce_shipping_cost_requires_address' ) ) {
			$object->type = 1;
			$object->address = true;
			$object->shipping = $this->shipping($user_id);
		}
		else {
			$object->type = 1;
			$object->address = false;
			$object->shipping = $this->shipping($user_id);
		}

		$country = WC()->countries->get_allowed_countries();
		if($country) {
			$arr = [];
			foreach ($country as $ckey => $cvalue){
				$object_child = new \stdClass();
				$object_child->key=$ckey;
				$object_child->value=$cvalue;
				$arr[] =$object_child;

			}

			$object->countries = $arr;

		}
		else {
			$object->countries = [];
		}
		$object->city = get_user_meta( $user_id, 'shipping_city', true );
		$object->zipcode =get_user_meta( $user_id, 'shipping_postcode', true );
		$object->country = $this->get_user_geo_country();

		return $object;
	}


	public function shipping($user_id) {

		global $woocommerce;

		$active_methods   = array();
		$values = array ('country' => $this->get_user_geo_country(),
		                 'amount'  => 100);
		WC()->session = new \WC_Session_Handler();
		WC()->session->init();
		WC()->customer = new \WC_Customer($user_id , true );
		WC()->cart = new \WC_Cart();
		$woocommerce->cart->add_to_cart('1');

		WC()->shipping->calculate_shipping($this->get_shipping_packages($values));
		$shipping_methods = WC()->shipping->packages;

		foreach ($shipping_methods[0]['rates'] as $id => $shipping_method) {
			$active_methods[] = array(  'id'        => $shipping_method->method_id,
			                            'type'      => $shipping_method->method_id,
			                            'provider'  => $shipping_method->method_id,
			                            'name'      => $shipping_method->label,
			                            'price'     => number_format($shipping_method->cost, 2, '.', ''));
		}
		return $active_methods;
	}


	public function get_shipping_packages($value) {

		// Packages array for storing 'carts'
		$packages = array();
		$packages[0]['contents']                = WC()->cart->cart_contents;
		$packages[0]['contents_cost']           = $value['amount'];
		$packages[0]['applied_coupons']         = WC()->session->applied_coupon;
		$packages[0]['destination']['country']  = $value['country'];
		$packages[0]['destination']['state']    = '';
		$packages[0]['destination']['postcode'] = '';
		$packages[0]['destination']['city']     = '';
		$packages[0]['destination']['address']  = '';
		$packages[0]['destination']['address_2']= '';


		return apply_filters('woocommerce_cart_shipping_packages', $packages);
	}

	public function get_user_geo_country(){
		$geo      = new \WC_Geolocation();
		$user_ip  = $geo->get_ip_address();
		$user_geo = $geo->geolocate_ip( $user_ip );
		$country  = $user_geo['country'];
		return $country;
		//return WC()->countries->countries[ $country ];

	}

}