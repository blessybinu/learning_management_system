<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Tutor_Course_Builder_Controller;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Mobile_Course_Builder {
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

		if($data['user_role'] == 2) {
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_load_template_before();
		}else
		{
			$object = new \stdClass();
			$object->type = 0;
			$object->msg ='';
			$data['data'] = $object;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function save_course( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_add_course_builder_mb($request,$user->ID);
		}else
		{
			$object = new \stdClass();
			$object->type = 0;
			$object->msg ='';
			$data['data'] = $object;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function get_course( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if(!isset($request['course_id'])) {
			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_get_course_builder_mb($request,$user->ID);
		}else
		{
			$object = new \stdClass();
			$object->type = 0;
			$object->msg ='';
			$data['data'] = $object;
		}

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function add_course_topic( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {

			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_add_course_topic_mb($request,$user->ID);
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

	public function tutor_delete_topic( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_delete_topic($request);
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

	public function tutor_update_topic( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_update_topic($request,$user->ID);
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

	public function tutor_get_lession( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_load_edit_lesson_modal($request,$user->ID);
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

	public function tutor_update_create_lession( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();

			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_modal_create_or_update_lesson($request,$user->ID);
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

	public function tutor_delete_lession( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_delete_lesson_by_id($request,$user->ID);
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

	public function save_video_attachments($request) {
		$data              = array();
		$user_role         = Edumall_Mobile_Utils::role_user();

		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$attach_id = Edumall_Mobile_Utils::save_video_mp4(true);
			$object = new \stdClass();
			if($attach_id){

				$object->type = 1;
				$object->msg ='';
				$object->id =$attach_id;
			}
			else {
				$object->type = 0;
				$object->msg ='';
			}
			$data['data'] = $object;
		}
		else if($data['user_role'] == 1){
			$object       = new \stdClass();
			$object->type = 0;
			if(isset($request['student'])){
				if($request['student']=='approve'){
					$attach_id = Edumall_Mobile_Utils::save_video_mp4(true);

					if($attach_id){

						$object->type = 1;
						$object->msg ='';
						$object->id =$attach_id;
					}
					else {
						$object->type = 0;
						$object->msg ='';
					}

				}

			}
			$data['data'] = $object;

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

	public function delete_attachments_id($request) {

		$data              = array();
		$user_role         = Edumall_Mobile_Utils::role_user();

		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$attach_id = Edumall_Mobile_Utils::delete_attachment_id($request['id']);
			$object = new \stdClass();
			if($attach_id){
				$object->type = 1;
				$object->msg ='';

			}
			else {
				$object->type = 0;
				$object->msg ='';
			}
			$data['data'] = $object;
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


	public function tutor_get_assignment( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_load_assignments_builder_modal($request,$user->ID);
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

	public function tutor_update_create_assignment( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();

			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_modal_create_or_update_assignment($request,$user->ID);

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


	public function tutor_create_quiz( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();

			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_create_quiz_and_load_modal($request,$user->ID);

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

	public function tutor_get_quiz( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->get_quizz($request,$user->ID);

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

	public function tutor_update_quiz( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();

			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_quiz_builder_quiz_update($request,$user->ID);

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

	public function quiz_builder_get_question_form($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();

			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_quiz_builder_get_question_form($request,$user->ID);

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

	public function tutor_quiz_modal_update_settings($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();


			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_quiz_modal_update_settings($request,$user->ID);

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

	public function tutor_mark_answer_as_correct($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();

			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_mark_answer_as_correct($request,$user->ID);

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

	public function tutor_quiz_builder_get_answers_by_question($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();

			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_quiz_builder_get_answers_by_question($request,$user->ID);

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

	public function tutor_save_quiz_answer_options($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;



		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_save_quiz_answer_options($request,$user->ID);

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

	public function tutor_quiz_add_question_answers($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();

			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_quiz_add_question_answers($request,$user->ID);

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

	public function tutor_load_edit_quiz_modal($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();

			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_load_edit_quiz_modal($request,$user->ID);

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

	public function tutor_quiz_builder_question_delete($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();

			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_quiz_builder_question_delete($request,$user->ID);

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

	public function tutor_quiz_modal_update_question($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;



		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();


			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_quiz_modal_update_question($request,$user->ID);

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

	public function tutor_quiz_edit_question_answer($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;



		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();


			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_quiz_edit_question_answer($request,$user->ID);

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

	public function tutor_update_quiz_answer_options($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_update_quiz_answer_options($request,$user->ID);
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

	public function tutor_quiz_builder_delete_answer($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_quiz_builder_delete_answer($request,$user->ID);
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


	public function detach_instructor_from_course($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;

		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->detach_instructor_from_course($request,$user->ID);
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


	public function tutor_add_instructors_to_course($request) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if($data['user_role'] == 2) {
			$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['data'] = Edumall_Tutor_Course_Builder_Controller::instance()->tutor_add_instructors_to_course($request,$user->ID);
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


	public function initialize() {

		$this->add_action_add_new_course();
		$this->add_action_save_new_course();
		$this->add_action_add_course_topic();
		$this->add_action_get_course();
		$this->add_action_tutor_delete_topic();
		$this->add_action_tutor_update_topic();
		$this->add_action_tutor_get_lession();
		$this->add_action_tutor_update_create_lession();
		$this->add_action_tutor_delete_lession();
		$this->add_action_save_video_attachments();
		$this->add_action_delete_attachments_id();
		$this->add_action_get_assignment();
		$this->add_action_tutor_update_create_assignment();
		$this->add_action_tutor_create_quiz();
		$this->add_action_tutor_get_quiz();
		$this->add_action_tutor_update_quiz();
		$this->add_action_quiz_builder_get_question_form();
		$this->add_action_tutor_quiz_modal_update_settings();
		$this->add_action_tutor_mark_answer_as_correct();
		$this->add_action_tutor_quiz_builder_get_answers_by_question();
		$this->add_action_tutor_save_quiz_answer_options();
		$this->add_action_tutor_quiz_add_question_answers();
		$this->add_action_tutor_load_edit_quiz_modal();
		$this->add_action_tutor_quiz_builder_question_delete();
		$this->add_action_tutor_quiz_builder_question_delete();
		$this->add_action_tutor_quiz_modal_update_question();
		$this->add_action_tutor_quiz_edit_question_answer();
		$this->add_action_tutor_update_quiz_answer_options();
		$this->add_action_tutor_quiz_builder_delete_answer();
		$this->add_action_detach_instructor_from_course();
		$this->add_action_tutor_add_instructors_to_course();



		//$this->add_action_save_video();
	}
/*
	private function add_action_save_video() {
		add_action( 'rest_api_init', [ $this, 'register_route_save_video' ] );
	}


	public function register_route_save_video() {
		register_rest_route( EM_ENDPOINT, '/save-video', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'save_video' ),
			'permission_callback' => '__return_true',

		) );
	}*/

	private function add_action_get_course() {
		add_action( 'rest_api_init', [ $this, 'register_route_get_course' ] );
	}


	public function register_route_get_course() {
		register_rest_route( EM_ENDPOINT, '/get_course', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'get_course' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_add_course_topic() {
		add_action( 'rest_api_init', [ $this, 'register_route_add_course_topic' ] );
	}


	public function register_route_add_course_topic() {
		register_rest_route( EM_ENDPOINT, '/topic/add', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'add_course_topic' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_add_new_course() {
		add_action( 'rest_api_init', [ $this, 'register_route_add_new_course' ] );
	}


	public function register_route_add_new_course() {
		register_rest_route( EM_ENDPOINT, '/add-new-course', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'index' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_save_new_course() {
		add_action( 'rest_api_init', [ $this, 'register_route_save_new_course' ] );
	}

	public function register_route_save_new_course() {
		register_rest_route( EM_ENDPOINT, '/save-new-course', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'save_course' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_delete_topic() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_delete_topic' ] );
	}

	public function register_route_tutor_delete_topic() {
		register_rest_route( EM_ENDPOINT, '/topic/delete', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_delete_topic' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_update_topic() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_update_topic' ] );
	}

	public function register_route_tutor_update_topic() {
		register_rest_route( EM_ENDPOINT, '/topic/update', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_update_topic' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_get_lession() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_get_lession' ] );
	}

	public function register_route_tutor_get_lession() {
		register_rest_route( EM_ENDPOINT, '/lession', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_get_lession' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_update_create_lession() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_update_create_lession' ] );
	}

	public function register_route_tutor_update_create_lession() {
		register_rest_route( EM_ENDPOINT, '/lession/update', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_update_create_lession' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_delete_lession() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_delete_lession' ] );
	}

	public function register_route_tutor_delete_lession() {
		register_rest_route( EM_ENDPOINT, '/lession/delete', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_delete_lession' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_save_video_attachments() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_save_video_attachments' ] );
	}

	public function register_route_tutor_save_video_attachments() {
		register_rest_route( EM_ENDPOINT, '/save-video-attachments', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'save_video_attachments' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_delete_attachments_id() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_delete_attachments_id' ] );
	}

	public function register_route_tutor_delete_attachments_id() {
		register_rest_route( EM_ENDPOINT, '/delete-attachments', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'delete_attachments_id' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_get_assignment() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_get_assignment' ] );
	}

	public function register_route_tutor_get_assignment() {
		register_rest_route( EM_ENDPOINT, '/assignment', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_get_assignment' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_update_create_assignment() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_update_create_assignment' ] );
	}

	public function register_route_tutor_update_create_assignment() {
		register_rest_route( EM_ENDPOINT, '/assignment/update', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_update_create_assignment' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_create_quiz() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_create_quiz' ] );
	}

	public function register_route_tutor_create_quiz() {
		register_rest_route( EM_ENDPOINT, '/quiz/create', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_create_quiz' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_get_quiz() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_get_quiz' ] );
	}

	public function register_route_tutor_get_quiz() {
		register_rest_route( EM_ENDPOINT, '/quiz', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_get_quiz' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_update_quiz() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_update_quiz' ] );
	}

	public function register_route_tutor_update_quiz() {
		register_rest_route( EM_ENDPOINT, '/quiz/update', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_update_quiz' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_quiz_builder_get_question_form() {
		add_action( 'rest_api_init', [ $this, 'register_route_quiz_builder_get_question_form' ] );
	}

	public function register_route_quiz_builder_get_question_form() {
		register_rest_route( EM_ENDPOINT, '/quiz/question_form', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'quiz_builder_get_question_form' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_quiz_modal_update_settings() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_quiz_modal_update_settings' ] );
	}

	public function register_route_tutor_quiz_modal_update_settings() {
		register_rest_route( EM_ENDPOINT, '/quiz/quiz_modal_update_settings', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_quiz_modal_update_settings' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_mark_answer_as_correct() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_mark_answer_as_correct' ] );
	}

	public function register_route_tutor_mark_answer_as_correct() {
		register_rest_route( EM_ENDPOINT, '/quiz/mark_answer_as_correct', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_mark_answer_as_correct' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_quiz_builder_get_answers_by_question() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_quiz_builder_get_answers_by_question' ] );
	}

	public function register_route_tutor_quiz_builder_get_answers_by_question() {
		register_rest_route( EM_ENDPOINT, '/quiz/get_answers_by_question', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_quiz_builder_get_answers_by_question' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_save_quiz_answer_options() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_save_quiz_answer_options' ] );
	}

	public function register_route_tutor_save_quiz_answer_options() {
		register_rest_route( EM_ENDPOINT, '/quiz/save_quiz_answer_options', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_save_quiz_answer_options' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}


	private function add_action_tutor_quiz_add_question_answers() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_quiz_add_question_answers' ] );
	}

	public function register_route_tutor_quiz_add_question_answers() {
		register_rest_route( EM_ENDPOINT, '/quiz/add_question_answers', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_quiz_add_question_answers' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_load_edit_quiz_modal() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_load_edit_quiz_modal' ] );
	}

	public function register_route_tutor_load_edit_quiz_modal() {
		register_rest_route( EM_ENDPOINT, '/quiz/load_edit_quiz_modal', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_load_edit_quiz_modal' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_quiz_builder_question_delete() {
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_quiz_builder_question_delete' ] );
	}

	public function register_route_tutor_quiz_builder_question_delete() {
		register_rest_route( EM_ENDPOINT, '/quiz/quiz_builder_question_delete', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_quiz_builder_question_delete' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_quiz_modal_update_question (){
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_quiz_modal_update_question' ] );
	}

	public function register_route_tutor_quiz_modal_update_question() {
		register_rest_route( EM_ENDPOINT, '/quiz/quiz_modal_update_question', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_quiz_modal_update_question' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}


	private function add_action_tutor_quiz_edit_question_answer (){
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_quiz_edit_question_answer' ] );
	}

	public function register_route_tutor_quiz_edit_question_answer() {
		register_rest_route( EM_ENDPOINT, '/quiz/tutor_quiz_edit_question_answer', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_quiz_edit_question_answer' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}


	private function add_action_tutor_update_quiz_answer_options (){
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_update_quiz_answer_options' ] );
	}

	public function register_route_tutor_update_quiz_answer_options() {
		register_rest_route( EM_ENDPOINT, '/quiz/tutor_update_quiz_answer_options', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_update_quiz_answer_options' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_tutor_quiz_builder_delete_answer (){
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_quiz_builder_delete_answer' ] );
	}

	public function register_route_tutor_quiz_builder_delete_answer() {
		register_rest_route( EM_ENDPOINT, '/quiz/tutor_quiz_builder_delete_answer', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_quiz_builder_delete_answer' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}

	private function add_action_detach_instructor_from_course (){
		add_action( 'rest_api_init', [ $this, 'register_route_detach_instructor_from_course' ] );
	}

	public function register_route_detach_instructor_from_course() {
		register_rest_route( EM_ENDPOINT, '/instructor/detach_instructor_from_course', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'detach_instructor_from_course' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}


	private function add_action_tutor_add_instructors_to_course (){
		add_action( 'rest_api_init', [ $this, 'register_route_tutor_add_instructors_to_course' ] );
	}

	public function register_route_tutor_add_instructors_to_course() {
		register_rest_route( EM_ENDPOINT, '/instructor/add_instructors_to_course', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'tutor_add_instructors_to_course' ),
			'permission_callback' => array( $this, 'permission_login' ),

		) );
	}





	public function permission_login() {
		return Edumall_Mobile_Utils::is_user_login();
	}



}

