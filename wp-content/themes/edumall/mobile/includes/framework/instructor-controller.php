<?php
namespace edumallmobile\framework;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Instructor_Controller extends \Edumall_Tutor
{

	protected static $instance = null;

	public static function instance()
	{
		if (null === self::$instance) {
		self::$instance = new self();
		}

		return self::$instance;
	}

	public function apply_instructor_mb($user_id) {

		$object = new \stdClass();


			if ( tutor_utils()->is_instructor() ) {
				$object->type = 1;
				$object->msg = esc_html__( 'Already applied for instructor!', 'edumall' );

			} else {
				update_user_meta( $user_id, '_is_tutor_instructor', tutor_time() );

				$register_immediately = get_tutor_option( 'instructor_register_immediately' );

				if ( 1 == $register_immediately ) {
					/**
					 * @see \TUTOR\Instructor::instructor_approval_action()
					 */
					do_action( 'tutor_before_approved_instructor', $user_id );

					update_user_meta( $user_id, '_tutor_instructor_status', 'approved' );
					update_user_meta( $user_id, '_tutor_instructor_approved', tutor_time() );

					$instructor = new \WP_User( $user_id );
					$instructor->add_role( tutor()->instructor_role );

					//TODO: send E-Mail to this user about instructor approval, should via hook
					do_action( 'tutor_after_approved_instructor', $user_id );

					$object->type = 2;
					$object->msg = __( 'Already applied for instructor!', 'edumall' );
				} else {
					update_user_meta( $user_id, '_tutor_instructor_status', apply_filters( 'tutor_initial_instructor_status', 'pending' ) );

					$object->type = 3;
					$object->msg = __( 'Your request is sent successfully.', 'edumall' );

				}

				do_action( 'tutor_new_instructor_after', $user_id );
			}

			return $object;



	}
}