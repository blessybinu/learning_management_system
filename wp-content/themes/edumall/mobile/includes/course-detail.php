<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Tutor_Detail_Controller;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Mobile_Course_Detail {
	protected static $instance = null;


	public function __construct() {
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function index( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if ( ! isset( $request['post_id'] ) ) {

			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}
		if($user_role !=0 ){
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['course_detail'] = Edumall_Tutor_Detail_Controller::instance()->detail( $request['post_id'] ,$user->ID);
		}
		else {
			$data['course_detail'] = Edumall_Tutor_Detail_Controller::instance()->detail( $request['post_id'] ,0);
		}



		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function add_to_wishlist($request){

		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if ( ! isset( $request['course_id'] ) ) {

			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}
		if($user_role !=0 ){
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Detail_Controller::instance()->tutor_course_add_to_wishlist( $request,$user->ID);
			$data['type'] = 1;
		}
		else {
			$data['type'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function enroll_now($request){

		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if ( ! isset( $request['course_id'] ) ) {

			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}
		if($user_role !=0 ){
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Detail_Controller::instance()->enroll_now($request['course_id'],$user->ID);
			$data['type'] = 1;
		}
		else {
			$data['type'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}


	public function initialize() {
		//require_once dirname( __FILE__ ) . '/utils/utils.php';
		$this->add_action_home();
		$this->add_action_add_to_wishlist();
		$this->add_action_enroll_now();
	}

	private function add_action_home() {
		add_action( 'rest_api_init', [ $this, 'register_route_course_detail' ] );
	}


	public function register_route_course_detail() {
		register_rest_route( EM_ENDPOINT, '/course/detail', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'index' ),
			'permission_callback' => '__return_true',

		) );
	}

	private function add_action_add_to_wishlist() {
		add_action( 'rest_api_init', [ $this, 'register_route_course_detail_add_to_wishlist' ] );
	}


	public function register_route_course_detail_add_to_wishlist() {
		register_rest_route( EM_ENDPOINT, '/course/detail/addtowishlist', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'add_to_wishlist' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_enroll_now() {
		add_action( 'rest_api_init', [ $this, 'register_route_enroll_now' ] );
	}


	public function register_route_enroll_now() {
		register_rest_route( EM_ENDPOINT, '/course/detail/enroll', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'enroll_now' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}


	public function permission_login() {
		return Edumall_Mobile_Utils::is_user_login();
	}

}

