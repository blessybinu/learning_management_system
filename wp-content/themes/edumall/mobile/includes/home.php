<?php

namespace edumallmobile;

use edumallmobile\framework\Edumall_Tutor_Shortcode;
use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Mobile_Home {
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
		$data['avartar'] = '';
		if($user_role!=0)
		{
			$user=Edumall_Mobile_Utils::edumall_mobile_get_user();
			$data['avartar'] = Edumall_Mobile_Utils::get_avatar_mb( $user->ID, '32x32' );
		}

		$category_options   = Edumall_Mobile_Utils::get_course_categories();
		$data['category']   = $category_options;
		$data['featured']   = Edumall_Tutor_Shortcode::instance()->get_courses( 'featured' );
		$i                  = 0;
		$category_list_name = array();
		$category_list_ids = [];
		foreach ( $category_options as $item ) {
			if ( $i < 4 ) {
				$data[ 'list' . $i ]  = Edumall_Tutor_Shortcode::instance()->get_courses( 'by_category', 8, 'grid', $item->id );
				$category_list_name[] = esc_html( $item->name );
				$category_list_ids[] =  $item->id ;
			} else {
				break;
			}
			$i++;
		}
		$data['category_list_name'] = $category_list_name;
		$data['category_list_ids'] = $category_list_ids;

		$data['list_student_view'] = Edumall_Tutor_Shortcode::instance()->get_courses( 'popular' );

		return Edumall_Mobile_Utils::get_respone( $data, 200 );


	}

	public function initialize() {
		//require_once dirname( __FILE__ ) . '/utils/utils.php';
		$this->add_action_home();
	}

	private function add_action_home() {
		add_action( 'rest_api_init', [ $this, 'register_route_home' ] );
	}

	public function register_route_home() {
		register_rest_route( EM_ENDPOINT, '/home', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'index' ),
			'permission_callback' => '__return_true',

		) );
	}
}
