<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Woo_Controller;
use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Mobile_Class_Wc_Ajax {
	protected static $instance = null;


	public function __construct() {
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function wc_variation_product( $request ) {
		$data              = array();
		$data['data'] = Edumall_Woo_Controller::instance()->get_variation_product((int)$request['course_id'] );
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}


	public function wc_coupon_code( $request ) {
		$data              = array();
		if(isset($request['code']))
		{
			$code = Edumall_Woo_Controller::instance()->get_coupon_code($request['code'] );
			if($code) {
				$data['data'] =$code;
				$data['type'] = 1;
			}else {
				$data['type'] = 0;
			}
		}
		else {
			$data['type'] = 0;
		}
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function get_checkout_info( $request ) {
		$data              = array();
		$data ['data']= Edumall_Woo_Controller::instance()->get_checkout_info();

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function get_shipping_info( $request ) {

		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($user_role!=0) {
			$user          = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data ['data'] = Edumall_Woo_Controller::instance()->get_shipping_info( $user->ID );
			$data ['type'] = 1;
		}
		else {
			$data ['type'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function get_country( $request ) {

		$data = [];
		$data ['data']= Edumall_Woo_Controller::instance()->get_user_geo_country();

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function get_user_role($request){
		$data = [];
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data['user_role'] = $user_role;
		if($user_role!=0) {
			$user              = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['customer_id'] = $user->ID;

		}else {
			$data['customer_id'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );

	}

	public function initialize() {
		$this->add_action();
	}

	private function add_action() {
		add_action( 'rest_api_init', [ $this, 'register_route_wc_variation_product' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_wc_coupon_code' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_get_shipping_info' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_get_checkout_info' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_get_country' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_get_user_role' ] );
	}


	public function register_route_wc_variation_product() {
		register_rest_route( EM_ENDPOINT, '/woo/variation', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'wc_variation_product' ),
			'permission_callback' => '__return_true',

		) );
	}


	public function register_route_wc_coupon_code() {
		register_rest_route( EM_ENDPOINT, '/woo/coupon_code', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'wc_coupon_code' ),
			'permission_callback' => '__return_true',

		) );
	}

	public function register_route_get_shipping_info() {
		register_rest_route( EM_ENDPOINT, '/woo/get_shipping_info', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'get_shipping_info' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}


	public function register_route_get_checkout_info() {
		register_rest_route( EM_ENDPOINT, '/woo/get_checkout_info', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_checkout_info' ),
			'permission_callback' => '__return_true',

		) );
	}

	public function register_route_get_country() {
		register_rest_route( EM_ENDPOINT, '/woo/get_country', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_country' ),
			'permission_callback' => '__return_true',

		) );
	}


	public function register_route_get_user_role() {
		register_rest_route( EM_ENDPOINT, '/get_user_role', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_user_role' ),
			'permission_callback' => '__return_true',

		) );
	}

	public function permission_login() {
		return Edumall_Mobile_Utils::is_user_login();
	}



}

