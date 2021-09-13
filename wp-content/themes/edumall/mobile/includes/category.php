<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Tutor_Shortcode;
use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Mobile_Category {
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
		if ( ! isset( $request['cat_id'] ) ) {

			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}

		$data['cat']                 = Edumall_Tutor_Shortcode::instance()->get_tax_name( $request['cat_id'] );
		$data['popular']             = Edumall_Tutor_Shortcode::instance()->get_courses( 'popular_by_catergory', 8, 'grid', $request['cat_id'] );
		$data['featured']            = Edumall_Tutor_Shortcode::instance()->get_featured_courses_by_current_tax_mobile( $request['cat_id'] );
		$data['popular_topics']      = Edumall_Tutor_Shortcode::instance()->get_popular_topics_by_current_tax_mobile( $request['cat_id'] );
		$data['popular_instructors'] = Edumall_Tutor_Shortcode::instance()->get_popular_instructors_by_current_tax_mobile( $request['cat_id'] );
		$data['sub_category']        = Edumall_Tutor_Shortcode::instance()->get_sub_category_by_current_tax_mobile( $request['cat_id'] );
		$data['all_course']          = Edumall_Tutor_Shortcode::instance()->get_all_courses_by_current_tax_mobile( $request['cat_id'], 0 );


		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function search_phrase( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if ( ! isset( $request['page'] ) || ! isset( $request['search'] ) ) {

			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}

		$data['all_course'] = Edumall_Tutor_Shortcode::instance()->get_search_courses( $request['search'], 8, $request['page'] );

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function category_pagination( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;
		if ( ! isset( $request['page'] ) || ! isset( $request['cat_id'] ) ) {

			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}


		$data['cat']        = Edumall_Tutor_Shortcode::instance()->get_tax_name( $request['cat_id'] );
		$data['all_course'] = Edumall_Tutor_Shortcode::instance()->get_all_courses_by_current_tax_mobile( $request['cat_id'], $request['page'] );

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function get_category_search( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		$category_options = Edumall_Mobile_Utils::get_course_categories();
		$data['category'] = $category_options;

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function initialize() {
		$this->add_action_category();
	}

	private function add_action_category() {
		add_action( 'rest_api_init', [ $this, 'register_route_category' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_category_pagination' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_get_category_search' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_search_word' ] );

	}

	public function register_route_category() {
		register_rest_route( EM_ENDPOINT, '/archive', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'index' ),
			'permission_callback' => '__return_true',

		) );
	}

	public function register_route_category_pagination() {
		register_rest_route( EM_ENDPOINT, '/archive/page', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'category_pagination' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function register_route_get_category_search() {
		register_rest_route( EM_ENDPOINT, '/search', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_category_search' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function register_route_search_word() {
		register_rest_route( EM_ENDPOINT, '/search/phrase', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'search_phrase' ),
			'permission_callback' => '__return_true',
		) );
	}

}

