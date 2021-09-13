<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Tutor_DashBoard_Controller;
use edumallmobile\framework\Edumall_Tutor_Shortcode;
use edumallmobile\framework\Instructor_Controller;
use edumallmobile\utils\Edumall_Mobile_Utils;

class   Edumall_Dashboard {
	protected static $instance = null;


	public function __construct() {

	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function wishlist($request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($user_role != 0) {
			$user=Edumall_Mobile_Utils::edumall_mobile_get_user();
			$value = Edumall_Tutor_DashBoard_Controller::instance()->wishlist($user->ID);


			if(count($value)<= 0) {
				$data['message'] = html_entity_decode(translate( 'You haven\'t any courses on the wishlist yet.', 'edumall' ));
				$data['empty'] = true;
			}
			else {
				$data['wishlist'] = $value;
				$data['empty'] = false;
			}

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function enrolled_courses( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($user_role != 0) {
			$user=Edumall_Mobile_Utils::edumall_mobile_get_user();
			$value = Edumall_Tutor_DashBoard_Controller::instance()->enrolled_courses($user->ID);
			if(count($value)<= 0) {
				$data['message'] = html_entity_decode(translate( 'You didn\'t purchased any courses.', 'edumall' ));
				$data['empty'] = true;
			}
			else {
				$data['enrolled-courses'] = $value;
				$data['empty'] = false;
			}

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function profile( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($user_role != 0) {
			$user=Edumall_Mobile_Utils::edumall_mobile_get_user();

				$value = Edumall_Tutor_DashBoard_Controller::instance()->profile($user->ID,$user_role);
				$data['dashboard'] = $value;


			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function settings( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($user_role != 0) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			if($user_role == 1 )
			{
				$value = Edumall_Tutor_DashBoard_Controller::instance()->settings($user->ID,1);
				$data['dashboard'] = $value;
			}
			else if($user_role == 2)
			{
				$value = Edumall_Tutor_DashBoard_Controller::instance()->settings($user->ID,2);
				$data['dashboard'] = $value;
			}


			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}


	public function update_payment( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if ( ! isset( $request['key'] )
		     || ! isset( $request['method_name'] )
		     || ! isset( $request['fromfields'] )
		)
		{

			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}
		if($user_role == 2) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['message'] = Edumall_Tutor_DashBoard_Controller::instance()->tutor_save_withdraw_account_mb($user->ID,$request);
			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function update_settings( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if ( ! isset( $request['firstName'] )
		     || ! isset( $request['lastName'] )
		     || ! isset( $request['jobTitle'] )
		     || ! isset( $request['bio'] )
		     || ! isset( $request['twitter'] )
		     || ! isset( $request['facebook'] )
		     || ! isset( $request['linkedin'] )
		     || ! isset( $request['youtube'])
		     || ! isset( $request['github'])
		     || ! isset( $request['instagram'])
		     || ! isset( $request['pinterest'])
		     || ! isset( $request['displayname'] )
		     || ! isset( $request['phonenumber'] )
		     || ! isset( $request['imagecoverphoto'] )
		     || ! isset( $request['namecoverphoto'] )
		     || ! isset( $request['imagetypecoverphoto'] )
		     || ! isset( $request['imageavatar'] )
		     || ! isset( $request['nameavatar'] )
		     || ! isset( $request['imagetypeavatar'] )
			)
		{

			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}
		if($user_role != 0) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['profile'] = Edumall_Tutor_DashBoard_Controller::instance()->update_user_profile_mb($user->ID,$request);

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function reset_password($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if ( ! isset( $request['previous_password'] )
		     || ! isset( $request['new_password'] )
		     || ! isset( $request['confirm_new_password'])
		)
		{

			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}
		if( $user_role != 0) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['pass'] = Edumall_Tutor_DashBoard_Controller::instance()->tutor_reset_password_mb($user,$request);

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function zoom_connect($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if( $user_role == 2) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['msg'] = Edumall_Tutor_DashBoard_Controller::instance()->tutor_check_api_connection($user->ID);

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;

		$object->msg = translate( 'Please activate tutor-pro', 'tutor-pro' ) ;
		$data['msg'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function zoom_update($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if ( ! isset( $request['api_key']) || !isset($request['api_secret']))
		{
			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}
		if( $user_role == 2) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_DashBoard_Controller::instance()->tutor_check_save_zoom_api($user->ID,$request);

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = '';
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function become_instructor($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if( $user_role == 1) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Instructor_Controller::instance()->apply_instructor_mb($user->ID);

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = __( 'Permission Denied', 'edumall' );
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function	update_review_modal($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		if( $user_role == 1) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_DashBoard_Controller::instance()->update_review_modal($request,$user->ID);

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = __( 'Permission Denied', 'edumall' );
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function	get_my_courses($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		if( $user_role == 2) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_DashBoard_Controller::instance()->get_my_courses($request,$user->ID);

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = __( 'Permission Denied', 'edumall' );
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function	delete_dashboard_course($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		if( $user_role == 2) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_DashBoard_Controller::instance()->delete_dashboard_course($request,$user->ID);

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = __( 'Permission Denied', 'edumall' );
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function	tutor_my_quizz_attempt($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		if( $user_role != 0) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_DashBoard_Controller::instance()->tutor_my_quizz_attempt($request,$user->ID);
			$data['type'] =1;
			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = __( 'Permission Denied', 'edumall' );
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}


	public function	tutor_dashboard_purchase_history($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		if( $user_role != 0) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_DashBoard_Controller::instance()->tutor_dashboard_purchase_history($request,$user->ID);
			$data['type'] =1;
			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = __( 'Permission Denied', 'edumall' );
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}


	public function	tutor_dashboard_earning($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		if( $user_role == 2) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_DashBoard_Controller::instance()->tutor_dashboard_earning($request,$user->ID);
			$data['type'] =1;
			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = __( 'Permission Denied', 'edumall' );
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}


	public function	tutor_dashboard_withdrawals($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		if( $user_role == 2) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_DashBoard_Controller::instance()->tutor_dashboard_withdrawals($request,$user->ID);
			$data['type'] =1;
			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = __( 'Permission Denied', 'edumall' );
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function	tutor_quizz_attempts($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		if( $user_role == 2) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_DashBoard_Controller::instance()->tutor_quizz_attempts($request,$user->ID);
			$data['type'] =1;
			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = __( 'Permission Denied', 'edumall' );
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function	tutor_dashboard_question_answer($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		if( $user_role == 2) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_DashBoard_Controller::instance()->tutor_dashboard_question_answer($request,$user->ID);
			$data['type'] =1;
			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = __( 'Permission Denied', 'edumall' );
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function	tutor_dashboard_assignment($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		if( $user_role == 2) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_DashBoard_Controller::instance()->tutor_dashboard_assignment($request,$user->ID);
			$data['type'] =1;
			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
		$object = new \stdClass();
		$object->type =0;
		$object->msg = __( 'Permission Denied', 'edumall' );
		$data['data'] = $object;
		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}



	public function initialize() {

		$this->add_action_dashboard();
	}

	private function add_action_dashboard() {
		add_action( 'rest_api_init', [ $this, 'register_route_my_course' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_wish_list' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_profile' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_settings' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_update_settings'] );
		add_action( 'rest_api_init', [ $this, 'register_route_reset_password'] );
		add_action( 'rest_api_init', [ $this, 'register_route_update_payment'] );
		add_action( 'rest_api_init', [ $this, 'register_route_check_api_connection'] );
		add_action( 'rest_api_init', [ $this, 'register_route_save_api_connection'] );
		add_action( 'rest_api_init', [ $this, 'register_route_become_an_instructor'] );
		add_action( 'rest_api_init', [ $this, 'register_route_update_review_modal'] );
		add_action( 'rest_api_init', [ $this, 'register_route_get_my_courses'] );
		add_action( 'rest_api_init', [ $this, 'register_route_delete_dashboard_course'] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_my_quizz_attempt'] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_dashboard_purchase_history'] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_dashboard_earning'] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_dashboard_withdrawals'] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_quizz_attempts'] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_dashboard_question_answer'] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_dashboard_assignment'] );

	}

	public function register_route_become_an_instructor() {
		register_rest_route( EM_ENDPOINT, '/dashboard/become-instructor', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'become_instructor' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_save_api_connection() {
		register_rest_route( EM_ENDPOINT, '/dashboard/zoom-update', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'zoom_update' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_check_api_connection() {
		register_rest_route( EM_ENDPOINT, '/dashboard/zoom-connect', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'zoom_connect' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_update_payment() {
		register_rest_route( EM_ENDPOINT, '/dashboard/update-payment', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'update_payment' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_reset_password() {
		register_rest_route( EM_ENDPOINT, '/dashboard/reset-password', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'reset_password' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_update_settings() {
		register_rest_route( EM_ENDPOINT, '/dashboard/update-profile', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'update_settings' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_settings() {
		register_rest_route( EM_ENDPOINT, '/dashboard/settings', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'settings' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_my_course() {
		register_rest_route( EM_ENDPOINT, '/dashboard/enrolled-courses', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'enrolled_courses' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_wish_list() {
		register_rest_route( EM_ENDPOINT, '/dashboard/wishlist', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'wishlist' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_profile() {
		register_rest_route( EM_ENDPOINT, '/dashboard/profile', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'profile' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_update_review_modal() {
		register_rest_route( EM_ENDPOINT, '/dashboard/update_review_modal', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'update_review_modal' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_get_my_courses() {
		register_rest_route( EM_ENDPOINT, '/dashboard/get_my_courses', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_my_courses' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_delete_dashboard_course() {
		register_rest_route( EM_ENDPOINT, '/dashboard/delete_dashboard_course', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'delete_dashboard_course' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_tutor_my_quizz_attempt() {
		register_rest_route( EM_ENDPOINT, '/dashboard/tutor_my_quizz_attempt', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_my_quizz_attempt' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_tutor_dashboard_purchase_history() {
		register_rest_route( EM_ENDPOINT, '/dashboard/tutor_dashboard_purchase_history', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_dashboard_purchase_history' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_tutor_dashboard_earning() {
		register_rest_route( EM_ENDPOINT, '/dashboard/tutor_dashboard_earning', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_dashboard_earning' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_tutor_dashboard_withdrawals() {
		register_rest_route( EM_ENDPOINT, '/dashboard/tutor_dashboard_withdrawals', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_dashboard_withdrawals' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_tutor_quizz_attempts() {
		register_rest_route( EM_ENDPOINT, '/dashboard/tutor_quizz_attempts', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_quizz_attempts' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}


	public function register_route_tutor_dashboard_question_answer() {
		register_rest_route( EM_ENDPOINT, '/dashboard/tutor_dashboard_question_answer', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_dashboard_question_answer' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}


	public function register_route_tutor_dashboard_assignment() {
		register_rest_route( EM_ENDPOINT, '/dashboard/tutor_dashboard_assignment', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_dashboard_assignment' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}





	public function permission_login() {
		return Edumall_Mobile_Utils::is_user_login();
	}



}

