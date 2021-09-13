<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Zoom_Controller;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Zoom {
	protected static $instance = null;


	public function __construct() {
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function tutor_zoom_meeting_modal_content( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($user_role !=0 ){
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Zoom_Controller::instance()->tutor_zoom_meeting_modal_content( $request ,$user->ID);
			$data['type'] = 1;
		}
		else {
			$data['type'] = 0;
			$data['msg'] = 'Access Denied';
		}
		return Edumall_Mobile_Utils::get_respone( $data, 200 );

	}

	public function tutor_zoom_save_meeting( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($user_role !=0 ){
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Zoom_Controller::instance()->tutor_zoom_save_meeting( $request ,$user->ID);
			$data['type'] = 1;
		}
		else {
			$data['type'] = 0;
			$data['msg'] = 'Access Denied';
		}
		return Edumall_Mobile_Utils::get_respone( $data, 200 );

	}

	public function tutor_zoom_delete_meeting( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($user_role !=0 ){
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Zoom_Controller::instance()->tutor_zoom_delete_meeting( $request ,$user->ID);
			$data['type'] = 1;
		}
		else {
			$data['type'] = 0;
			$data['msg'] = 'Access Denied';
		}
		return Edumall_Mobile_Utils::get_respone( $data, 200 );

	}


	public function initialize() {
		//require_once dirname( __FILE__ ) . '/utils/utils.php';
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_zoom_meeting_modal_content' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_zoom_save_meeting' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_zoom_delete_meeting' ] );


	}



	public function register_route_tutor_zoom_meeting_modal_content() {
		register_rest_route( EM_ENDPOINT, '/zoom/tutor_zoom_meeting_modal_content', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_zoom_meeting_modal_content' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}


	public function register_route_tutor_zoom_save_meeting() {
		register_rest_route( EM_ENDPOINT, '/zoom/tutor_zoom_delete_meeting', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_zoom_delete_meeting' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_tutor_zoom_delete_meeting() {
		register_rest_route( EM_ENDPOINT, '/zoom/tutor_zoom_save_meeting', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_zoom_save_meeting' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}


	public function permission_login() {
		return Edumall_Mobile_Utils::is_user_login();
	}

}

