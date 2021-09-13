<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Course_Query_Mb;
use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Mobile_Course_Filter {
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
		if ( ! isset( $request['page'] ) ) {

			return Edumall_Mobile_Utils::get_respone( $data, 400 );
		}


		$array_filter = array();
		if ( isset( $request['filter_course-category'] ) ) {
			$array_filter['filter_course-category'] = $request['filter_course-category'];
		}
		if ( isset( $request['price_type'] ) ) {
			$array_filter['price_type'] = $request['price_type'];
		}
		if ( isset( $request['level'] ) ) {
			$array_filter['level'] = $request['level'];
		}
		if ( isset( $request['filter_course-language'] ) ) {
			$array_filter['filter_course-language'] = $request['filter_course-language'];
		}
		if ( isset( $request['duration'] ) ) {
			$array_filter['duration'] = $request['duration'];
		}
		if ( isset( $request['orderby'] ) ) {
			$array_filter['orderby'] = $request['orderby'];
		}
		if ( isset( $request['instructor'] ) ) {
			$array_filter['instructor'] = $request['instructor'];
		}
		if ( isset( $request['rating_filter'] ) ) {
			$array_filter['rating_filter'] = $request['rating_filter'];
		}

		$data['all_course'] = Edumall_Course_Query_Mb::instance()->get_course_filtering( $array_filter, 8, $request['page'] );


		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function filter_widget( $request ) {
		$user_role         = Edumall_Mobile_Utils::role_user();
		$data              = array();
		$data['user_role'] = $user_role;


		$array_filter = array();
		if ( isset( $request['filter_course-category'] ) ) {
			$array_filter['filter_course-category'] = $request['filter_course-category'];
		}
		if ( isset( $request['price_type'] ) ) {
			$array_filter['price_type'] = $request['price_type'];
		}
		if ( isset( $request['level'] ) ) {
			$array_filter['level'] = $request['level'];
		}
		if ( isset( $request['filter_course-language'] ) ) {
			$array_filter['filter_course-language'] = $request['filter_course-language'];
		}
		if ( isset( $request['duration'] ) ) {
			$array_filter['duration'] = $request['duration'];
		}
		if ( isset( $request['orderby'] ) ) {
			$array_filter['orderby'] = $request['orderby'];
		}
		if ( isset( $request['instructor'] ) ) {
			$array_filter['instructor'] = $request['instructor'];
		}
		if ( isset( $request['rating_filter'] ) ) {
			$array_filter['rating_filter'] = $request['rating_filter'];
		}

		$data['data'] = Edumall_Course_Query_Mb::instance()->get_course_widget_filtering( $array_filter );

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function initialize() {
		$this->add_action_filter();
	}

	private function add_action_filter() {
		add_action( 'rest_api_init', [ $this, 'register_route_filter' ] );
		add_action( 'rest_api_init', [ $this, 'register_route_filter_widget' ] );


	}

	public function register_route_filter() {
		register_rest_route( EM_ENDPOINT, '/course_filter', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'index' ),
			'permission_callback' => '__return_true',

		) );
	}

	public function register_route_filter_widget() {
		register_rest_route( EM_ENDPOINT, '/course_widget_filter', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'filter_widget' ),
			'permission_callback' => '__return_true',

		) );
	}

}


