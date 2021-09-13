<?php

namespace edumallmobile;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Mobile_Authenticate {
	protected static $instance     = null;
	private          $const_change = ":{1}";

	public function __construct() {
	}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function login( $request ) {
		//what user can
		if ( empty( $request['user_name'] ) || empty( $request['user_pass'] ) ) {
			return Edumall_Mobile_Utils::get_respone( array(), 400 );
		}

		$result = wp_authenticate( $request['user_name'], $request['user_pass'] );

		if ( is_wp_error( $result ) ) {
			return Edumall_Mobile_Utils::get_respone( array(), 500 );
		} else {
			$token = $this->generate_token( $result->ID );
			$this->update_info_user( $result->ID, $token, date( 'Y-m-d H:i:s', strtotime( current_time( 'Y-m-d H:i:s' ) . ' +1 day' ) ) );

			$data = array(
				'token'     => $token,
				'user_role' => $this->role_user( $result->ID ),
				'avatar' => Edumall_Mobile_Utils::get_avatar_mb( $result->ID, '32x32' ),
			);

			return Edumall_Mobile_Utils::get_respone( $data, 200 );
		}
	}

	public function generate_token( $user_id ) {
		return md5( $user_id . current_time( 'Y-m-d H:i:s' ), false );
	}

	private function update_info_user( $id, $token, $duration ) {
		update_user_meta( $id, 'mobile_token', $token );
		update_user_meta( $id, 'mobile_duration', $duration );
	}

	public static function role_user( $user_id ) : int {

		$register_time = get_user_meta( $user_id, '_is_tutor_instructor', true );

		if ( empty( $register_time ) ) {
			return 1;
		}

		$instructor_status = get_user_meta( $user_id, '_tutor_instructor_status', true );

		if ( 'approved' !== $instructor_status ) {
			return 1;
		}

		return 2;

	}

	public function register( $request ) {
		$firstname  = $request['firstname'];
		$lastname   = $request['lastname'];
		$email      = $request['email'];
		$password   = $request['password'];
		$user_login = $request['username'];
		$userdata   = [
			'first_name' => $firstname,
			'last_name'  => $lastname,
			'user_login' => $user_login,
			'user_email' => $email,
			'user_pass'  => $password,
		];

		// Remove all illegal characters from email.

		$email = filter_var( $email, FILTER_SANITIZE_EMAIL );
		$msg   = esc_html__( 'Username/Email address is existing', 'edumall' );

		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$msg = esc_html__( 'A valid email address is required', 'edumall' );
		} else {
			$user_id = wp_insert_user( $userdata );

			if ( ! is_wp_error( $user_id ) ) {
				$token = $this->generate_token( $user_id );
				$this->update_info_user( $user_id, $token, date( 'Y-m-d H:i:s', strtotime( current_time( 'Y-m-d H:i:s' ) . ' +1 day' ) ) );
				$msg = esc_html__( 'Congratulations, register successful, Redirecting...', 'edumall' );

				$data = array(
					'messages' => $msg,
					'token'    => $token,

				);

				return Edumall_Mobile_Utils::get_respone( $data, 200 );
			}
		}
		$data = array(
			'messages' => $msg,
		);

		return Edumall_Mobile_Utils::get_respone( $data, 400 );
	}

	public function logout( $request ) {
		$data = array();
		$this->part_logout( $request );

		return Edumall_Mobile_Utils::get_respone( $data, 200 );
	}

	public function part_logout( $request ) {

		$user = Edumall_Mobile_Utils::edumall_mobile_get_user();
		$this->update_info_user( $user->ID, '', date( 'Y-m-d H:i:s', strtotime( current_time( 'Y-m-d H:i:s' ) ) ) );
	}

	public function lost_password( $request ) {
		$user = get_user_by( 'email', $request['user'] );

		if ( ! $user ) {
			$user = get_user_by( 'login', $request['user'] );
		}
		$data = array();
		if ( $user ) {
			//send activation code
			$code      = mt_rand( 100000, 999999 );
			$user_data = wp_update_user( array( 'ID' => $user->ID, 'user_activation_key' => $code ) );
			//send mail - after up on server

			if ( is_wp_error( $user_data ) ) {
				// There was an error; possibly this user doesn't exist.
				return Edumall_Mobile_Utils::get_respone( $data, 500 );
			} else {
				// Success!
				return Edumall_Mobile_Utils::get_respone( $data, 200 );
			}
		} else {
			return Edumall_Mobile_Utils::get_respone( $data, 500 );
		}
	}

	public function verify_code( $request ) {
		$user = get_user_by( 'email', $request['user'] );
		if ( ! $user ) {
			$user = get_user_by( 'login', $request['user'] );
		}
		$data = array();
		if ( $user ) {
			if ( $user->user_activation_key === $request['verify_code'] ) {
				$user_data = wp_update_user( array( 'ID'                  => $user->ID,
				                                    'user_activation_key' => $user->user_activation_key . $this->const_change,
				) );

				return Edumall_Mobile_Utils::get_respone( $data, 200 );
			}

			//send mail - after up on server
			return Edumall_Mobile_Utils::get_respone( $data, 500 );
		} else {
			return Edumall_Mobile_Utils::get_respone( $data, 500 );
		}
	}

	public function change_password( $request ) {
		$user = get_user_by( 'email', $request['user'] );
		if ( ! $user ) {
			$user = get_user_by( 'login', $request['user'] );
		}
		$data = array();
		if ( $user ) {
			$activate = substr( $user->user_activation_key, -4 );
			if ( $activate === $this->const_change ) {
				$user_data = wp_update_user( array( 'ID'                  => $user->ID,
				                                    'user_pass'           => $request['resetpassword'],
				                                    'user_activation_key' => '',
				) );
				if ( ! is_wp_error( $user_data ) ) {
					$this->part_logout( $request );

					return Edumall_Mobile_Utils::get_respone( $data, 200 );
				}
			}

			//send mail - after up on server
			return Edumall_Mobile_Utils::get_respone( $data, 501 );
		} else {
			return Edumall_Mobile_Utils::get_respone( $data, 500 );
		}
	}

	public function initialize() {
		//require_once dirname( __FILE__ ) . '/utils/utils.php';
		$this->add_action_login();
	}

	private function add_action_login() {
		add_action( 'rest_api_init', [ $this, 'register_route_login' ] );

		add_action( 'rest_api_init', [ $this, 'register_route_register' ] );

		add_action( 'rest_api_init', [ $this, 'register_route_logout' ] );

		add_action( 'rest_api_init', [ $this, 'register_route_lost_password' ] );

		add_action( 'rest_api_init', [ $this, 'register_route_verify_code' ] );

		add_action( 'rest_api_init', [ $this, 'register_route_change_password' ] );
	}

	public function permission_login() {
		return Edumall_Mobile_Utils::is_user_login();
	}

	public function register_route_login() {
		register_rest_route( EM_ENDPOINT, '/authenticate/login', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'login' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function register_route_register() {
		register_rest_route( EM_ENDPOINT, '/authenticate/register', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'register' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function register_route_logout() {
		register_rest_route( EM_ENDPOINT, '/authenticate/logout', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'logout' ),
			'permission_callback' => array( $this, 'permission_login' ),
		) );
	}

	public function register_route_lost_password() {
		register_rest_route( EM_ENDPOINT, '/authenticate/lost_password', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'lost_password' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function register_route_verify_code() {
		register_rest_route( EM_ENDPOINT, '/authenticate/verify_code', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'verify_code' ),
			'permission_callback' => '__return_true',
		) );
	}

	public function register_route_change_password() {
		register_rest_route( EM_ENDPOINT, '/authenticate/change_password', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'change_password' ),
			'permission_callback' => '__return_true',
		) );
	}
}
