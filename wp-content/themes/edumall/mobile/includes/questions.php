<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Questions_Controller;
use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Mobile_Class_Question {
	protected static $instance = null;


	public function __construct() {
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public function get_section_lession( $request ) {

		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($user_role!=0) {
			$user          = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data ['data'] = Edumall_Questions_Controller::instance()->get_section_lession($request, $user->ID );
			$data ['type'] = 1;
		}
		else {
			$data ['type'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function mark_completed_lession( $request ) {

		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if(!isset($request['lesson_id']))
		{
			$data['lession_id'] = $request['lesson_id'];
			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}
		if($user_role!=0) {
			$user          = Edumall_Mobile_Utils::edumall_mobile_get_user();
			 Edumall_Questions_Controller::instance()->mark_lesson_complete($request, $user->ID );
			$data ['type'] = 1;
		}
		else {
			$data ['type'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}


	public function tutor_assignment_submit( $request ) {

		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($user_role!=0) {
			$user          = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data ['data'] = 	Edumall_Questions_Controller::instance()->tutor_start_assignment($request, $user);
			$data ['type'] = 1;
		}
		else {
			$data ['type'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}


	public function save_assignment_upload_file($request) {
		$data              = array();
		$user_role         = Edumall_Mobile_Utils::role_user();

		$data['user_role'] = $user_role;

		if($data['user_role'] != 0 ) {

			$data ['data'] = Edumall_Questions_Controller::instance()->handle_assignment_attachment_uploads((int)$request['assignment_id']);

		}
		else
		{
			$object = new \stdClass();
			$object->type = 0;
			$object->msg ='';
			$data['data'] = $object;
		}



		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function star_the_quiz($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($user_role!=0) {
			$user          = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data ['data'] = 	Edumall_Questions_Controller::instance()->start_the_quiz($request, $user->ID);
			$data ['type'] = 1;
		}
		else {
			$data ['type'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );

	}

	public function answering_the_quiz($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($user_role!=0) {
			$user          = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data ['data'] = 	Edumall_Questions_Controller::instance()->answering_quiz($request, $user->ID);
			$data ['type'] = 1;
		}
		else {
			$data ['type'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function get_lession_browser_qa($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($user_role!=0) {
			$user          = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data ['data'] = 	Edumall_Questions_Controller::instance()->get_lession_browser_qa($request, $user->ID);
			$data ['type'] = 1;
		}
		else {
			$data ['type'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}


	public function tutor_add_answer_bqa($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($user_role!=0) {
			$user          = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data ['data'] = 	Edumall_Questions_Controller::instance()->tutor_add_answer_bqa($request, $user->ID);
			$data ['type'] = 1;
		}
		else {
			$data ['type'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function tutor_ask_question_bqa($request){
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($user_role!=0) {
			$user          = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data ['data'] = 	Edumall_Questions_Controller::instance()->tutor_ask_question_bqa($request, $user->ID);
			$data ['type'] = 1;
		}
		else {
			$data ['type'] = 0;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}






	public function initialize() {
		$this->add_action();
	}

	private function add_action() {
		add_action( 'rest_api_init', [ $this, 'register_route_get_section_lession' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_mark_completed_lession' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_assignment_submit' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_save_assignment_upload_file' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_start_the_quiz' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_answering_quiz' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_get_lession_browser_qa' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_add_answer_bqa' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_ask_question_bqa' ] );



	}


	public function register_route_get_section_lession() {
		register_rest_route( EM_ENDPOINT, '/lessions', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'get_section_lession' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_mark_completed_lession() {
			register_rest_route( EM_ENDPOINT, '/lessions/mark_complete', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'mark_completed_lession' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_tutor_assignment_submit() {
		register_rest_route( EM_ENDPOINT, '/lessions/tutor_assignment_submit', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_assignment_submit' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function register_route_tutor_save_assignment_upload_file() {
		register_rest_route( EM_ENDPOINT, '/lessions/save_assignment_upload_file', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'save_assignment_upload_file' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function  register_route_tutor_start_the_quiz() {
		register_rest_route( EM_ENDPOINT, '/lessions/star_the_quiz', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'star_the_quiz' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function  register_route_tutor_answering_quiz() {
		register_rest_route( EM_ENDPOINT, '/lessions/answering_the_quiz', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'answering_the_quiz' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function  register_route_get_lession_browser_qa() {
		register_rest_route( EM_ENDPOINT, '/lessions/get_lession_browser_qa', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'get_lession_browser_qa' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function  register_route_tutor_add_answer_bqa() {
		register_rest_route( EM_ENDPOINT, '/lessions/tutor_add_answer_bqa', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_add_answer_bqa' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	public function  register_route_tutor_ask_question_bqa() {
		register_rest_route( EM_ENDPOINT, '/lessions/tutor_ask_question_bqa', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_ask_question_bqa' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}



	public function permission_login() {
		return Edumall_Mobile_Utils::is_user_login();
	}



}

