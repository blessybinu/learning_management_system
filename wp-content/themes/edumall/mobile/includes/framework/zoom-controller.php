<?php

namespace edumallmobile\framework;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Zoom_Controller {

	private $api_key;
	private $settings_key;
	private $zoom_meeting_post_type;
	private $zoom_meeting_base_slug;
	public $zoom_meeting_post_meta;

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->api_key = 'tutor_zoom_api';
			self::$instance->settings_key = 'tutor_zoom_settings';
			self::$instance->zoom_meeting_post_type = 'tutor_zoom_meeting';
			self::$instance->zoom_meeting_base_slug = 'tutor-zoom-meeting';
			self::$instance->zoom_meeting_post_meta = '_tutor_zm_data';
		}

		return self::$instance;
	}

	public function tutor_zoom_meeting_modal_content($request,$user_id) {
		$object = new \stdClass();
		$static_arr = [];
		$meeting_id = (int) sanitize_text_field($request['meeting_id']);
		$topic_id   = (int) sanitize_text_field($request['topic_id']);
		$course_id  = (int) sanitize_text_field($request['course_id']);

		$post = null;
		$meeting_data = null;
		$object->meeting_id = $meeting_id ;
		$object->topic_id = $topic_id ;
		$object->meeting_id = $meeting_id ;
		$object->course_id = $course_id;
		$object->has_meeting_data = false;

		if ($meeting_id) {

			$post = get_post($meeting_id);
			$meeting_start  = get_post_meta($meeting_id, '_tutor_zm_start_datetime', true);
			$meeting_data   = get_post_meta($meeting_id, $this->zoom_meeting_post_meta, true);
			$meeting_data   = json_decode($meeting_data, true);
			if($meeting_data){
				$object->has_meeting_data = true;
			}
			$object->meeting_start = $meeting_start;
			$object->meeting_data = $meeting_data;

		}

		$start_date     = '';
		$start_time     = '';
		$host_id        = !empty($meeting_data) ? $meeting_data['host_id'] : '';
		$title          = !empty($meeting_data) ? wp_strip_all_tags($meeting_data['topic']) : '';
		$summary        = !empty($post) ? $post->post_content : '';
		$timezone       = !empty($meeting_data) ? $meeting_data['timezone'] : '';
		$duration       = !empty($meeting_data) ? $meeting_data['duration'] : 60;
		$duration_unit  = !empty($post) ? get_post_meta($meeting_id, '_tutor_zm_duration_unit', true) : 'min';
		$password       = !empty($meeting_data) ? $meeting_data['password'] : '';
		$auto_recording = !empty($meeting_data) ? $meeting_data['settings']['auto_recording'] : $this->get_settings($user_id,'auto_recording');

		$object->start_date = $start_date;
		$object->start_time = $start_time;
		$object->host_id = $host_id;
		$object->title = $title;
		$object->summary = $summary;
		$object->timezone = $timezone;
		$object->duration = $duration;
		$object->duration_unit = $duration_unit;
		$object->password = $password;
		if($auto_recording)
		{
			$object->auto_recording = $auto_recording;
		}
		else{
			$object->auto_recording = [];
		}


		if (!empty($meeting_data)) {
			$input_date = \DateTime::createFromFormat('Y-m-d H:i:s', $meeting_start);
			$start_date = $input_date->format('d/m/Y');
			$start_time = $input_date->format('h:i A');
			$duration   = ($duration_unit == 'hr') ? $duration / 60 : $duration;
			$object->start_date = $start_date;
			$object->start_time = $start_time;
			$object->duration = $duration;
		}

		$meeting_host = $this->get_users_options($user_id);


		$meeting_host_arr = [];
		 foreach ($meeting_host as $id => $host) {
		 	$object_host = new \stdClass();
		 	$object_host->id = $id;
		 	$object_host->name = $host;
		 	$meeting_host_arr [] = $object_host;
		 }



		$object->meeting_host =$meeting_host_arr;

		$timezone_options = tutor_zoom_get_timezone_options();
		$timezone_arr = [];
		foreach ($timezone_options as $id => $host) {
			$object_host = new \stdClass();
			$object_host->id = $id;
			$object_host->name = $host;
			$timezone_arr [] = $object_host;
		}

		$object->timezone_options =$timezone_arr;

		$static_arr['meeting_host'] = __('Meeting Host', 'tutor-pro');
		$static_arr['meeting_name'] = __('Meeting Name', 'tutor-pro');
		$static_arr['meeting_summary'] = __('Meeting Summary', 'tutor-pro');
		$static_arr['meeting_time'] = __('Meeting Time', 'tutor-pro');
		$static_arr['meeting_duration'] = __('Meeting Duration', 'tutor-pro');
		$static_arr['meeting_minutes'] = __('Minutes', 'tutor-pro');
		$static_arr['meeting_hours'] = __('Hours', 'tutor-pro');
		$static_arr['meeting_no_recordings'] = __('No Recordings', 'tutor-pro');
		$static_arr['meeting_locals'] = __('Local', 'tutor-pro');
		$static_arr['meeting_cloud'] = __('Cloud',  'tutor-pro');
		$static_arr['meeting_cloud'] = __('Password',  'tutor-pro');
		$static_arr['meeting_success'] = __('Success',  'tutor-pro');
		$static_arr['meeting_has_been_save'] = __('Meeting has been saved',  'tutor-pro');
		$static_arr['meeting_error'] = __('Error',  'tutor-pro');
		$static_arr['meeting_request_error'] = __('Request Error',  'tutor-pro');
		$static_arr['meeting_save_meeting'] = __('Save Meeting',  'tutor-pro');

		$object->static_arr = $static_arr;

		return $object;


	}


	/**
	 * Get Zoom Users
	 * @return array
	 */
	public function get_users_options($user_id) {
		$users = $this->tutor_zoom_get_users($user_id);
		if (!empty($users)) {
			foreach ($users as $user) {
				$first_name         = $user['first_name'];
				$last_name          = $user['last_name'];
				$email              = $user['email'];
				$id                 = $user['id'];
				$user_list[$id]   = $first_name . ' ' . $last_name . ' (' . $email . ')';
			}
		} else {
			return array();
		}
		return $user_list;
	}


	/**
	 * Get Zoom Users from Zoom API
	 * @return array
	 */
	public function tutor_zoom_get_users($user_id) {


		$settings       = json_decode(get_user_meta($user_id, $this->api_key, true), true);



		if ($user_id) {

			$api_key    = (!empty($settings['api_key'])) ? $settings['api_key'] : '';
			$api_secret = (!empty($settings['api_secret'])) ? $settings['api_secret'] : '';




			if (!empty($api_key) && !empty($api_secret)) {
				$users = array();
				$users_data = tutils()->get_package_object( true, '\Zoom\Endpoint\Users', $api_key, $api_secret );



				$users_list = $users_data->userlist();
				if (!empty($users_list) && !empty($users_list['users'])) {
					$users = $users_list['users'];

				}
			} else {
				$users = array();
			}

		}else {

		}
		return $users;
	}


	private function get_settings($user_id,$key = null) {

		$settings_data = json_decode(get_user_meta($user_id, $this->settings_key, true), true);
		return $this->get_option_data($key, $settings_data);
	}

	private function get_option_data($key, $data) {
		if (empty($data) || !is_array($data)) {
			return false;
		}
		if (!$key) {
			return $data;
		}
		if (array_key_exists($key, $data)) {
			return apply_filters($key, $data[$key]);
		}
	}

	/**
	 * Save meeting
	 */
	public function tutor_zoom_save_meeting($request,$user_id) {

		$object = new \stdClass();
		$static_arr = [];
		$meeting_id = (int) sanitize_text_field($request['meeting_id']);
		$topic_id   = (int) sanitize_text_field($request['topic_id']);
		$course_id  = (int) sanitize_text_field($request['course_id']);


		$settings   = json_decode(get_user_meta($user_id, $this->api_key, true), true);

		$api_key    = (!empty($settings['api_key'])) ? $settings['api_key'] : '';
		$api_secret = (!empty($settings['api_secret'])) ? $settings['api_secret'] : '';
		if (!empty($api_key) && !empty($api_secret)) {

			$host_id            = !empty($request['meeting_host']) ? sanitize_text_field($request['meeting_host']) : '';
			$title              = !empty($request['meeting_title']) ? sanitize_text_field($request['meeting_title']) : '';
			$summary            = !empty($request['meeting_summary']) ? sanitize_text_field($request['meeting_summary']) : '';
			$timezone           = !empty($request['meeting_timezone']) ? sanitize_text_field($request['meeting_timezone']) : '';
			$start_date         = !empty($request['meeting_date']) ? sanitize_text_field($request['meeting_date']) : '';
			$start_time         = !empty($request['meeting_time']) ? sanitize_text_field($request['meeting_time']) : '';

			$input_duration     = !empty($request['meeting_duration']) ? intval($request['meeting_duration']) : 60;
			$duration_unit      = !empty($request['meeting_duration_unit']) ? $request['meeting_duration_unit'] : 'min';
			$password           = !empty($request['meeting_password']) ? sanitize_text_field($request['meeting_password']) : '';

			$join_before_host   = ($this->get_settings($user_id,'join_before_host')) ? true : false;
			$host_video         = ($this->get_settings($user_id,'host_video')) ? true : false;
			$participants_video = ($this->get_settings($user_id,'participants_video')) ? true : false;
			$mute_participants  = ($this->get_settings($user_id,'mute_participants')) ? true : false;
			$enforce_login      = ($this->get_settings($user_id,'enforce_login')) ? true : false;
			$auto_recording     = !empty($request['auto_recording']) ? sanitize_text_field($request['auto_recording']) : '';


			$input_date = \DateTime::createFromFormat('d/m/Y h:i A', $start_date . ' ' . $start_time);
			$meeting_start =  $input_date->format('Y-m-d\TH:i:s');

			$duration = ($duration_unit == 'hr') ? $input_duration * 60 : $input_duration;
			$data = array(
				'topic'         => $title,
				'type'          => 2,
				'start_time'    => $meeting_start,
				'timezone'      => $timezone,
				'duration'      => $duration,
				'password'      => $password,
				'settings'      => array(
					'join_before_host'  => $join_before_host,
					'host_video'        => $host_video,
					'participant_video' => $participants_video,
					'mute_upon_entry'   => $mute_participants,
					'auto_recording'    => $auto_recording,
					'enforce_login'     => $enforce_login,
				)
			);


			//save post
			$post_content = array(

				'ID'            => ($meeting_id) ? $meeting_id : 0,
				'post_author'   =>$user_id,
				'post_title'    => $title,
				'post_name'     => sanitize_title($title),
				'post_content'  => $summary,
				'post_type'     => $this->zoom_meeting_post_type,
				'post_parent'   => ($topic_id) ? $topic_id : $course_id,
				'post_status'   => 'publish'
			);




			//save zoom meeting
			if (!empty($api_key) && !empty($api_secret) && !empty($host_id)) {

				$post_id      = wp_insert_post($post_content);
				$post = get_post($post_id);

				$meeting_data = get_post_meta($post_id, $this->zoom_meeting_post_meta, true);
				$meeting_data = json_decode($meeting_data, true);
				$zoom_endpoint = tutils()->get_package_object( true, '\Zoom\Endpoint\Meetings', $api_key, $api_secret );
				if (!empty($meeting_data) && isset($meeting_data['id'])) {

					$zoom_endpoint->update($meeting_data['id'], $data);
					$saved_meeting = $zoom_endpoint->meeting($meeting_data['id']);

				} else {
					$saved_meeting = $zoom_endpoint->create($host_id, $data);
					update_post_meta($post_id, '_tutor_zm_for_course', $course_id);
					update_post_meta($post_id, '_tutor_zm_for_topic', $topic_id);

				}
				 update_post_meta($post_id, '_tutor_zm_start_date', $input_date->format('Y-m-d'));
				 update_post_meta($post_id, '_tutor_zm_start_datetime', $input_date->format('Y-m-d H:i:s'));
				 update_post_meta($post_id, '_tutor_zm_duration', $input_duration);
				 update_post_meta($post_id, '_tutor_zm_duration_unit', $duration_unit);
				 update_post_meta($post_id, $this->zoom_meeting_post_meta, json_encode($saved_meeting));

			}

			$object->type =1;
			$object->post_id =$post_id;

			$object->msg =__('Meeting Successfully Saved', 'tutor-pro');

			return $object;
		} else {
			$object->type =0;
			$object->msg =__('Invalid Api Credentials', 'tutor-pro');
			return $object;
		}
		$object->type =1;
		return $object;
	}

	public function get_zoom_meetings($course_id,$user_id) {
		$zoom_meetings = get_tutor_zoom_meetings(array(
			'author'    =>  $user_id,
			'course_id' => $course_id
		));
		$zoom_arr = [];
		if (!empty($zoom_meetings)) {
			foreach ($zoom_meetings as $meeting){

				$tzm_start      = get_post_meta($meeting->ID, '_tutor_zm_start_datetime', true);
				$meeting_data   = get_post_meta($meeting->ID, $this->zoom_meeting_post_meta, true);
				$meeting_data   = json_decode($meeting_data, true);

				if(!$tzm_start) {
					continue;
				}
				$object =  new \stdClass();
				$input_date = \DateTime::createFromFormat('Y-m-d H:i:s', $tzm_start);

				$object->input_date     = $input_date;
				$object->start_date     = $input_date->format('j M, Y');
				$object->start_time     = $input_date->format('h:i A');
				$object->title =  $meeting->post_title;
				$object->meetin_data_id =  $meeting_data['id'];
				$object->meeting_password = $meeting_data['password'];
				$object->start_url = $meeting_data['start_url'];
				$object->meeting_id = $meeting->ID;
				$zoom_arr[] =$object ;
			}


		}
		return $zoom_arr;
	}

	public function tutor_zoom_delete_meeting($request,$user_id) {
		$object = new \stdClass();
		$post_id    = (int) sanitize_text_field($request['meeting_id']);
		$settings   = json_decode(get_user_meta($user_id, $this->api_key, true), true);
		$api_key    = (!empty($settings['api_key'])) ? $settings['api_key'] : '';
		$api_secret = (!empty($settings['api_secret'])) ? $settings['api_secret'] : '';
		if (!empty($api_key) && !empty($api_secret)) {
			$meeting_data = get_post_meta($post_id, $this->zoom_meeting_post_meta, true);
			$meeting_data = json_decode($meeting_data, true);

			$zoom_endpoint = tutils()->get_package_object( true, '\Zoom\Endpoint\Meetings', $api_key, $api_secret );
			$zoom_endpoint->remove($meeting_data['id']);

			wp_delete_post($post_id, true);


			$object = new \stdClass();
			$object->type =1;

		} else {
			$object = new \stdClass();
			$object->type =0;
		}
		return $object;
	}




}
