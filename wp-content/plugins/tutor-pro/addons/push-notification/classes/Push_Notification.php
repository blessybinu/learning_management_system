<?php

/**
 * Tutor Push Notification
 */

namespace TUTOR_PN;

use \Minishlink\WebPush\VAPID;

class Push_Notification {

    private $browser_key = 'tutor_pn_browser_key';
    private $sub_key = 'tutor_pn_subscriptions';

    public function __construct() {
        
        add_action( 'init', array($this, 'load_service_worker') );
		add_filter( 'tutor/options/attr', array($this, 'add_options'));

        add_action( 'wp_enqueue_scripts', array($this, 'load_scrips') );
        add_action( 'admin_enqueue_scripts', array($this, 'load_scrips') );

        add_action( 'wp_ajax_tutor_pn_save_subscription', array($this, 'save_subscription') );
        add_action( 'wp_logout', array($this, 'purge_browser_id') );
        add_action( 'wp_login', array($this, 'purge_browser_id') );
        add_filter( 'tutor_localize_data', array($this, 'supply_pn_data') );
        
		add_action( 'tutor_announcement_editor/after', array($this, 'notification_checkbox_for_announcement') );

        add_action( 'wp_footer', array($this, 'permission_screen') );
        add_action( 'admin_footer', array($this, 'permission_screen') );
    }

    public function load_scrips() {
        // Service worker should always be registered regardless of login state
        wp_enqueue_script('tutor-pn-registrar', TUTOR_PN()->url . 'assets/js/registrar.js');
        wp_enqueue_style('tutor-pn-registrar-css', TUTOR_PN()->url . 'assets/css/permission.css');
    }

    public function load_service_worker() {
        $uri = explode('/', $_SERVER['REQUEST_URI']);

        if(end($uri)=='tutor-push-notification.js') {
            $file = TUTOR_PN()->path . '/assets/js/tutor-push-notification.js';
            header('Content-Type: text/javascript');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }

    protected function load_web_push() {
        require_once tutor_pro()->path . '/vendor/autoload.php';
    }

    private function updateSubscription($user_id, $key, $subscription=null) {

        $subs = get_user_meta( $user_id, $this->sub_key, true );
        !is_array($subs) ? $subs=array() : 0;

        if(!$subscription) {
            if(isset($subs[$key])) {
                unset($subs[$key]);
            }
        } else {
            $subs[$key] = $subscription;
        }
        
        update_user_meta( $user_id, $this->sub_key, $subs );
    }

    private function get_current_subscription() {
        if(isset($_COOKIE[$this->browser_key])) {
            $subs = get_user_meta( get_current_user_id(), $this->sub_key, true );
            return isset($subs[$_COOKIE[$this->browser_key]]) ? $subs[$_COOKIE[$this->browser_key]] : null;
        }
    }

    protected function get_subscriptions($user_id) {
        $subs = get_user_meta( $user_id, $this->sub_key, true );
        return is_array($subs) ? $subs : array();
    }

    public function purge_browser_id($user_id) {
        if(isset( $_COOKIE[$this->browser_key] )) {
            $this->updateSubscription($user_id, $_COOKIE[$this->browser_key]);
            setcookie($this->browser_key, "", time() - 3600, '/');
        }        
    }

    protected function get_vapid_keys() {
        
        $vapid_keys = get_option( 'tutor_pn_vapid_keys' );
        $home_url = get_home_url();

        // Use home_url to make sure current site url is used
        // Because in some cases users move their site one domain to another
        if(!is_array($vapid_keys) || !isset($vapid_keys[$home_url])) {
            $this->load_web_push();

            try {
                $vapid_keys = array($home_url => VAPID::createVapidKeys());
                update_option( 'tutor_pn_vapid_keys', $vapid_keys );
            }
            catch(\Exception $e) {
                return null;
            }
        }

        return $vapid_keys[$home_url];
    }

    public function supply_pn_data($_tutorobject) {

        $keys = $this->get_vapid_keys();
        $_tutorobject['tutor_pn_vapid_key'] = $keys ? $keys['publicKey'] : null;
        $_tutorobject['tutor_pn_client_id'] = get_current_user_id();
        $_tutorobject['tutor_pn_subscription_saved'] = $this->get_current_subscription() ? 'yes' : 'no';
        
        return $_tutorobject;
    }

    public function save_subscription() {

        $key = isset( $_COOKIE[$this->browser_key] ) ? $_COOKIE[$this->browser_key] : 'pn_' . microtime(true);

        $subscription = @json_decode(stripslashes($_POST['subscription']), true);
        $this->updateSubscription(get_current_user_id(), $key, $subscription);
        
        setcookie($this->browser_key, $key,  time() + (5 * 365 * 24 * 60 * 60), '/');
    }


	public function add_options($attr){
		$attr['tutor_push_notification'] = array(
			'label' => __( 'Push Notification', 'tutor-pro' ),
			'sections'    => array(
				'general' => array(
					'label' => __('Push Notification Events', 'tutor-pro'),
					'desc' => __('Push notification recipient selections', 'tutor-pro'),
					'fields' => array(
                        'tutor_pn_to_students' => array(
							'type' => 'checkbox',
                            'label' => 'Student Notifications',
                            'options' => array(
								'course_enrolled' 				=> __('Course Enrolled', 'tutor-pro'),
								'remove_from_course' 			=> __('Remove from Course', 'tutor-pro'),
								'assignment_graded' 			=> __('Assignment Graded', 'tutor-pro'),
                                'new_announcement_posted' 		=> __('New Announcement Posted', 'tutor-pro'),
                                'after_question_answered' 		=> __('Q&A Message Answered', 'tutor-pro'),
                                'feedback_submitted_for_quiz' 	=> __('Feedback submitted for Quiz Attempt', 'tutor-pro'),
                                'enrollment_expired' 			=> __('Course enrolment expired', 'tutor-pro'),
                            ),
							'desc' => __('Notifications to send to students', 'tutor-pro'),
                        ),
						'tutor_pn_to_instructors' => array(
							'type' => 'checkbox',
                            'label' => 'Instructor Notifications',
							'options' => array(
								'instructor_application_accepted' => __('Instructor Application Accepted ', 'tutor-pro'),
								'instructor_application_rejected' => __('Instructor Application Rejected', 'tutor-pro'),
                            ),
							'desc' => __('Notifications to send to instructor', 'tutor-pro'),
						),
						'tutor_pn_to_admin' => array(
							'type' => 'checkbox',
                            'label' => 'Admin Notifications',
							'options' => array(
								'instructor_application_received' => __('Instructor Application Received', 'tutor-pro'),
                            ),
							'desc' => __('Notifications to send to admin', 'tutor-pro'),
						),
					),
				),
			),
		);
		return $attr;
	}

	public function notification_checkbox_for_announcement() {

		$notify_checked = tutils()->get_option('tutor_pn_to_students.new_announcement_posted');

		if ($notify_checked) : ?>
			<div class="tutor-option-field-row">
				<div class="tutor-form-group">
					<label>
						<input type="checkbox" name="tutor_push_notify_students" checked="checked">
						<?php _e('Send push notification to all students of this course.', 'tutor-pro'); ?>
					</label>
				</div>
			</div>
		<?php endif;
	}

    public function permission_screen() {

        global $wp_query;
        
        $is_course = false;

        if(function_exists('get_queried_object')) {
            $object = get_queried_object();
            $is_course = is_object( $object ) && isset( $object->post_type ) && $object->post_type == tutor()->course_post_type;
        }

        if(
            tutils()->is_tutor_dashboard() || 
            $is_course ||
            is_front_page() || (
                $wp_query && 
                is_object($wp_query) && 
                is_array($wp_query->query_vars) && 
                isset($wp_query->query_vars['pagename']) && 
                $wp_query->query_vars['pagename'] == 'dashboard'
            )
        ) {
            ?>
            <div id="tutor-pn-permission">
                <div>
                    
                    <p><i class="tutor-icon-notifications-button"></i><?php _e('Want to receive push notifications for all major on-site activities?', 'tutor-pro'); ?></p>
                    <div>
                        <button id="tutor-pn-enable"><?php _e('Enable Notifications', 'tutor-pro'); ?></button>
                        <button id="tutor-pn-dont-ask"><?php _e('Never', 'tutor-pro'); ?></button>
                        <span id="tutor-pn-close">✕</span>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}