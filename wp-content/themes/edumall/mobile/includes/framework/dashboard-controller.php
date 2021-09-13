<?php

namespace edumallmobile\framework;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Tutor_DashBoard_Controller extends \Edumall_Tutor
{
	private $zoom_api_key = 'tutor_zoom_api';
	private $settings_key = 'tutor_zoom_settings';
    protected static $instance = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function enrolled_courses($user_id)
    {

        $data = array();
        $my_courses = tutor_utils()->get_enrolled_courses_by_user($user_id);

        global $post;
        if ($my_courses && $my_courses->have_posts()) {
            while ($my_courses->have_posts()) :
                $my_courses->the_post();
                $object = new \stdClass();
                $object->id = get_the_ID();
                $object->name = get_the_title();
                $object->rating = tutor_utils()->get_course_rating()->rating_avg;
                $object->totalLession = number_format_i18n(tutor_utils()->get_lesson_count_by_course());
                $object->completeLession = tutor_utils()->get_completed_lesson_count_by_course(get_the_ID(), $user_id);
                $object->totalTime = '';
                $disable_course_duration = get_tutor_option('disable_course_duration');
                $course_duration         = \Edumall_Tutor::instance()->get_course_duration_context();
                if (! empty($course_duration) && ! $disable_course_duration) {
                    $object->totalTime = $course_duration;
                }
                $object->urlThumnails =     \Edumall_Image::get_the_post_thumbnail_url([

                    'size' => '480x295',
                ]);

                $object->percentCompleted = Edumall_Mobile_Utils::get_course_completed_percent_mb(get_the_ID(), $user_id);
                $data[] = $object;
            endwhile;
        }
        wp_reset_postdata();

        return $data;
    }

    public function wishlist($user_id)
    {
        $data = array();
        global $post;
        $wishlists = tutor_utils()->get_wishlist($user_id);
        if (is_array($wishlists) && count($wishlists)) :
            global $edumall_course;
            $edumall_course_clone = $edumall_course;
            foreach ($wishlists as $post) :
                setup_postdata($post);
                $edumall_course = new \Edumall_Course();
                /**
                 * Setup course object.
                 */
                $object               = new \stdClass();
                $object->idCourse     = $post->ID;
                $object->permalink    = get_permalink($post->ID);
                $object->courseName   = get_the_title($post->ID);
                $category             = \Edumall_Tutor::instance()->get_the_category();
                $link                 = get_term_link($category);
                $object->idCategory   = $category->term_id;
                $object->categoryName = esc_html($category->name);
                $object->categoryLink = esc_url($link);
                $object->isBestseller = $edumall_course->is_featured();
                $object->isDiscount   = false;
                $object->discount     = '';
                if (! empty($edumall_course->on_sale_text())) {
                    $object->isDiscount = true;
                    $object->discount   = $edumall_course->on_sale_text();
                }
                $object->level      = Edumall_Mobile_Utils::get_level_label($post->ID);
                $object->authorName = '';
                $instructors        = $edumall_course->get_instructors();

                if (! empty($instructors)) {
                    $first_instructor   = $instructors[0];
                    $object->authorName = esc_html($first_instructor->display_name);
                }
                $object->fixedPrice = Edumall_Mobile_Utils::getPriceOfCourses($post->ID, 0);
                $object->isFree     = true;
                if ($object->fixedPrice > 0) {
                    $object->isFree = false;
                }
                $object->salePrice = 0;
                if (Edumall_Mobile_Utils::is_course_on_sale($post->ID)) {
                    $object->salePrice = Edumall_Mobile_Utils::getPriceOfCourses($post->ID, 1);
                }
                $object->urlThumnails = \Edumall_Image::get_the_post_thumbnail_url(array( 'size' => '226x150' ));
                $object->rating       = '0.00';
                $object->totalRating  = 0;
                $course_rating        = $edumall_course->get_rating();
                $rating_count         = intval($course_rating->rating_count);
                if ($rating_count > 0) {
                    $object->rating      = $course_rating->rating_avg;
                    $object->totalRating = intval($course_rating->rating_count);
                }
                $data[] = $object;
            endforeach;
            wp_reset_postdata();
            $edumall_course = $edumall_course_clone;
        endif;


        return $data;
    }

	/*
    * type: student,instructor
    * user_id: id_user
    */
    public function settings($user_id,$type)
    {
        $data = array();
	    $user = get_userdata($user_id);
        $data['profile'] = $this->get_profile_settings($user);


        if($type == 2)
        {
	        $data['withdrawal'] = $this->get_withdrawal_setting($user_id);
	        $data['zoom'] = $this->get_zoom_setting($user_id);
        }

        return $data;
    }

	public function get_zoom_setting($user_id) {
		$object = new \stdClass();
		$object->api_key ='';
		$object->api_secret = '';
		$array = $this->get_api_zoom($user_id);
		if($array)
		{
			$object->api_key =$array['api_key'];
			$object->api_secret = $array['api_secret'];
		}
		return $object;

	}


    public function get_withdrawal_setting($user_id) {
    	$data = array();
	    $tutor_withdrawal_methods = tutor_withdrawal_methods();
	    if ( tutor_utils()->count( $tutor_withdrawal_methods ) ){
		    $saved_account  = tutor_utils()->get_user_withdraw_method($user_id);
		    $method_selected = tutor_utils()->avalue_dot( 'withdraw_method_key', $saved_account);

		    $min_withdraw_amount = tutor_utils()->get_option( 'min_withdraw_amount' );
		    foreach ( $tutor_withdrawal_methods as $method_id => $method )  {
		    	$object = new \stdClass();
			    $object->key = $method_id;
			    $object->methodName = tutor_utils()->avalue_dot( 'method_name', $method );
			    $object->minWithDrawal = strip_tags(tutor_utils()->tutor_price( $min_withdraw_amount ));
			    $object->isSelected = false;
			    if($method_selected == $method_id)
			    {
				    $object->isSelected = true;
			    }

			    $fields_input = array();
			    $form_fields = tutor_utils()->avalue_dot( 'form_fields', $method );


			    if ( tutor_utils()->count( $form_fields ) ) {
				    foreach ( $form_fields as $field_name => $field ) {
					    $object_filed = new \stdClass();
					    $object_filed->fieldName =$field_name;
					    if ( ! empty( $field['label'] ) ) {

						    $object_filed->label = $field['label'];


					    }
					    if($method_selected == $method_id) {
						    $object_filed->value = tutor_utils()->avalue_dot( $field_name . ".value", $saved_account );
					    }
					    else {
						    $object_filed->value = '';
					    }

					    if ( ! empty( $field['desc'] ) ) {
						    $object_filed->desc = $field['desc'];
					    }

					    $fields_input[] = $object_filed;
				    }
				    $object->fromfields = $fields_input;
			    }
			    $data[] =$object;
		    }


	    }
	    return $data;
    }

    /**
	 * Save Withdraw Method Data
	 *
	 * @since v.1.2.0
	 */

	public function tutor_save_withdraw_account_mb($user_id,$request){
		//Checking nonce

		$saved_data = array();
		$saved_data['withdraw_method_key'] = $request['key'];
		$saved_data['withdraw_method_name'] = $request['method_name'];

		foreach ($request['fromfields'] as $input ){
			$saved_data[$input['fieldName']]['value'] = esc_sql( sanitize_text_field($input['value']) );
			$saved_data[$input['fieldName']]['label'] = $input['label'] ;
		}

		$success = update_user_meta($user_id, '_tutor_withdraw_method_data', $saved_data);
		$msg = '';
		if(!$success)
		{
			$msg = apply_filters('tutor_withdraw_method_set_failer_msg', __('Withdrawal account information saved failed!', 'tutor'));
		}
		else {
			$msg = apply_filters( 'tutor_withdraw_method_set_success_msg', __( 'Withdrawal account information saved successfully!', 'tutor' ) );
		}
		$object  = new \stdClass();
		$object->msg =$msg;

		return $object;
	}

    public function get_profile_settings($user) {
		$object = new \stdClass();
		$object->firstname =  $user->first_name;
		$object->lastname =  $user->last_name;
		$object->jobtitle = strip_tags(get_user_meta( $user->ID, '_tutor_profile_job_title', true ));
		$object->phonenumber = strip_tags(get_user_meta( $user->ID, 'phone_number', true ));
		$object->bio = strip_tags( get_user_meta( $user->ID, '_tutor_profile_bio', true ) );

		$tutor_user_social_icons = tutor_utils()->tutor_user_social_icons();

		foreach ( $tutor_user_social_icons as $key => $social_icon ) {
			$object->$key = get_user_meta( $user->ID, $key, true );
		}

		$public_display                     = array();
		$public_display['display_nickname'] = $user->nickname;
		$public_display['display_username'] = $user->user_login;

		if ( ! empty( $user->first_name ) ) {
			$public_display['display_firstname'] = $user->first_name;
		}

		if ( ! empty( $user->last_name ) ) {
			$public_display['display_lastname'] = $user->last_name;
		}

		if ( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
			$public_display['display_firstlast'] = $user->first_name . ' ' . $user->last_name;
			$public_display['display_lastfirst'] = $user->last_name . ' ' . $user->first_name;
		}

		if ( ! in_array( $user->display_name, $public_display ) ) { // Only add this if it isn't duplicated elsewhere
			$public_display = array( 'display_displayname' => $user->display_name ) + $public_display;
		}

		$public_display = array_map( 'trim', $public_display );
		$public_display = array_unique( $public_display );
		$temp = array();
		foreach ($public_display as $key=>$value){
			$object1= new \stdClass();
			$object1->key = $key;
			$object1->value = $value;
			$temp [] =$object1;
		}
		$object->public_display_list = $temp;

		$profile_placeholder = \Edumall_Helper::placeholder_avatar_src();
		$profile_photo_src   = $profile_placeholder;
		$profile_photo_id    = get_user_meta( $user->ID, '_tutor_profile_photo', true );
		if ( $profile_photo_id ) {
			$url = wp_get_attachment_image_url( $profile_photo_id, 'full' );
			! empty( $url ) ? $profile_photo_src = $url : 0;
		}
		$object->avatar_url = $profile_photo_src;


		$cover_placeholder = tutor()->url . 'assets/images/cover-photo.jpg';
		$cover_photo_src   = $cover_placeholder;
		$cover_photo_id    = get_user_meta( $user->ID, '_tutor_cover_photo', true );
		if ( $cover_photo_id ) {
			$url = wp_get_attachment_image_url( $cover_photo_id, 'full' );
			!
			empty( $url ) ? $cover_photo_src = $url : 0;
		}
		$object->cover_photo_url = $cover_photo_src;

		return $object;
	}

	public function profile_instructors($user_id)
    {

    }

	public function profile($user_id,$user_role)
	{
		$data = array();
		$data['quantity'] = Edumall_Woo_Controller::instance()->get_quantity_from_cart($user_id);
		$data['dashboard'] =$this->dashboard_profile($user_id,$user_role);
		$data['myprofile'] =$this->dashboard_my_profile($user_id);
		$reviews_obj = $this->dashboard_reviews($user_id);

		if(!$reviews_obj) {
			$data['review_type'] = 0;
			$data['review_message'] = translate( 'You haven\'t given any reviews yet.', 'edumall' );
		}
		else
		{
			$data['review_type'] = 1;
			$data['reviews'] = $reviews_obj;
		}
		if($user_role == 2) {
			$data['recieved_reviews'] = $this->received_reviews( $user_id );
			$data['my_courses'] = $this->get_my_courses([], $user_id );
		}

		return $data;
	}



    public function dashboard_profile($user_id,$user_role)
    {

        $enrolled_course   = tutor_utils()->get_enrolled_courses_by_user($user_id);
        $completed_courses = tutor_utils()->get_completed_courses_ids_by_user($user_id);

        $enrolled_course_count  = $enrolled_course ? $enrolled_course->post_count : 0;
        $completed_course_count = count($completed_courses);
        $active_course_count    = $enrolled_course_count - $completed_course_count;
        $active_course_count    = $active_course_count > 0 ? $active_course_count : 0;
        $object = new \stdClass();
        $object->enrolled_courses= number_format_i18n($enrolled_course_count);
        $object->active_courses= number_format_i18n($active_course_count);
        $object->completed_courses= number_format_i18n($completed_course_count);
	    if($user_role == 2) {
		    $total_students = tutor_utils()->get_total_students_by_instructor( $user_id );
		    $my_courses     = tutor_utils()->get_courses_by_instructor( $user_id, 'publish' );
		    $earning_sum    = tutor_utils()->get_earning_sum($user_id);
		    $my_course_count = count( $my_courses );
		    $object->total_students = number_format_i18n( $total_students );
		    $object->my_course_count = number_format_i18n( $my_course_count ) ;
		    $object->earning_sum1 = $earning_sum;
		    $object->earning_sum = Edumall_Mobile_Utils::tutor_price( $earning_sum->instructor_amount );

	    }

        return $object;
    }

    public function dashboard_my_profile($user_id)
    {

        $user = get_userdata($user_id);
        $rdate                 = wp_date('D d M Y, h:i:s a', strtotime($user->user_registered));
        $fname                 = $user->first_name;
        $lname                 = $user->last_name;
        $uname                 = $user->user_login;
        $email                 = $user->user_email;
        $phone                 = get_user_meta($user_id, 'phone_number', true);
        $bio                   = nl2br(strip_tags(get_user_meta($user_id, '_tutor_profile_bio', true)));
        $job_title             = strip_tags(get_user_meta($user_id, '_tutor_profile_job_title', true));
        $avatar                = Edumall_Mobile_Utils::get_avatar_mb( $user_id, '32x32' );

        $object = new \stdClass();
        $object->rdate     = $rdate    ;
        $object->fname     = $fname    ;
        $object->lname     = $lname    ;
        $object->uname     = $uname    ;
        $object->email     = $email    ;
        $object->phone     = $phone    ;
        $object->bio       = $bio      ;
        $object->job_title = $job_title;
	    $object->avatar = $avatar;
        return $object;
    }

	public function received_reviews($user_id)
	{

		$user = get_userdata($user_id);
		$per_page     = tutils()->get_option( 'pagination_per_page', 20 );
		$reviews = tutor_utils()->get_reviews_by_instructor( $user_id, 0, $per_page );


		if ( $reviews->count ) {
			foreach ( $reviews->results as $review ) {
				$review->avatar          = Edumall_Mobile_Utils::get_avatar_mb(  $review->user_id, '32x32' );
				$review->date            = sprintf( esc_html__( '%s ago', 'edumall' ), human_time_diff( strtotime( $review->comment_date ) ) );
				$review->comment_content = wp_strip_all_tags(wpautop( stripslashes( $review->comment_content ) ));
				$review->title = get_the_title( $review->comment_post_ID );

			}
		}
		else {
			$reviews = [];
			$reviews['count'] = 0;
		}


		return $reviews;
	}

    public function dashboard_reviews($user_id)
    {

        $reviews = tutor_utils()->get_reviews_by_user($user_id);

        if (empty($reviews)) {
            return false;
        }
        $data = [];

        foreach ($reviews as $review) {
            $name = get_the_title($review->comment_post_ID);
            $starview = $review->rating;
            $time = sprintf(esc_html__('%s ago', 'edumall'), human_time_diff(strtotime($review->comment_date)));
            $id = $review->comment_post_ID;
            $comment = wpautop(stripslashes($review->comment_content));
            $url_thumnail = Edumall_Tutor_Shortcode::instance()->get_the_post_thumbnail_mb([
            	'post_id' => $review->comment_post_ID,
            'size'    => '150x92',
            ]);
            $object = new \stdClass();
	        $object->name = $name;
	        $object->starview = $starview;
	        $object->time = $time;
	        $object->id = $id;
	        $object->comment = strip_tags($comment);
	        $object->url_thumnail = $url_thumnail;
	        $object->comment_ID = $review->comment_ID;
	        $data[] = $object;
        }

        return $data;
    }


	public  function getImageType($imagetype) {
		switch($imagetype){
			case 'jpg':
				return 'image/jpeg';
			case 'png':
				return 'image/png';
			case 'gif':
				return 'image/gif';
			case 'jpeg':
				return 'image/jpeg';
			default:
				return 'text';
		}
	}

	public  function update_user_profile_mb($user_id,$request){
		$first_name     = sanitize_text_field($request['firstName']);
		$last_name      = sanitize_text_field($request['lastName']);
		$phone_number   = sanitize_text_field($request['phonenumber']);
		$tutor_profile_bio =  sanitize_text_field($request['bio']);
		$display_name   = sanitize_text_field($request['displayname']);

		$userdata = array(
			'ID'            =>  $user_id,
			'first_name'    =>  $first_name,
			'last_name'     =>  $last_name,
			'display_name'  =>  $display_name,
		);
		$user_id  = wp_update_user( $userdata );

		if ( ! is_wp_error( $user_id ) ) {
			update_user_meta($user_id, 'phone_number', $phone_number);
			update_user_meta($user_id, '_tutor_profile_bio', $tutor_profile_bio);

			$tutor_user_social = tutils()->tutor_user_social_icons();
			foreach ($tutor_user_social as $key => $social){
				$user_social_value = sanitize_text_field($request[$key]);
				if($user_social_value){
					update_user_meta($user_id, $key, $user_social_value);
				}else{
					delete_user_meta($user_id, $key);
				}
			}
		}

		$urlCoverPhoto = '';
		$urlCoverAvatar = '';
		if($request['imagecoverphoto']!='') {
			$cover_photo_boyd = base64_decode( $request['imagecoverphoto'] );
			$url_coverPhoto   = $this->update_user_profile_photo_mb( $user_id, $cover_photo_boyd, $request['namecoverphoto'], $request['imagetypecoverphoto'], 'cover_photo' );
			if ( $url_coverPhoto ) {
				$urlCoverPhoto = $url_coverPhoto;
			}
		}
		else{
			$this->delete_existing_user_photo($user_id, 'cover_photo');
		}

		if($request['imageavatar']!='') {
			$cover_photo_boydi = base64_decode( $request['imageavatar'] );
			$url_coverAvatar   = $this->update_user_profile_photo_mb( $user_id, $cover_photo_boydi, $request['nameavatar'], $request['imagetypeavatar'], 'cover_avatar' );
			if ( $url_coverAvatar ) {
				$urlCoverAvatar = $url_coverAvatar;
			}
		}else {
			$this->delete_existing_user_photo($user_id, 'cover_avatar');
		}

		$object = new \stdClass();
		$object->cover_photo = $urlCoverPhoto;
		$object->cover_avatar = $urlCoverAvatar;
		return $object;

	}

	public function update_user_profile_photo_mb($user_id,$filebody,$name,$imagetype,$type){

		$meta_key = $type=='cover_photo' ? '_tutor_cover_photo' : '_tutor_profile_photo';

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}


		$upload_dir = wp_upload_dir();

		$decoded = $filebody;
		$hashed_filename = md5( $name . microtime() ) . '_' . $name;
		$upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
		$fullFileName = $upload_path . $hashed_filename.'.'.$imagetype;
		file_put_contents( $fullFileName, $decoded );

		$file             = array();
		$file['error']    = '';
		$file['tmp_name'] = $fullFileName ;
		$file['name']     = $hashed_filename.'.'.$imagetype;
		$file['type']     = $this->getImageType($imagetype);
		$file['size']     = filesize( $fullFileName );
		//var_dump($file);
		$upload_overrides = array( 'test_form' => false );
		$movefile         = wp_handle_sideload( $file, $upload_overrides );


		if ( $movefile && ! isset( $movefile['error'] ) ) {
			$file_path = tutils()->array_get( 'file', $movefile );
			$file_url  = tutils()->array_get( 'url', $movefile );

			$media_id = wp_insert_attachment( array(
				'guid'           => $file_path,
				'post_mime_type' => mime_content_type( $file_path ),
				'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_url ) ),
				'post_content'   => '',
				'post_status'    => 'inherit'
			), $file_path, 0 );

			if ($media_id) {
				// wp_generate_attachment_metadata() won't work if you do not include this file
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				// Generate and save the attachment metas into the database
				wp_update_attachment_metadata( $media_id, wp_generate_attachment_metadata( $media_id, $file_path ) );

				//Update it to user profile
				$this->delete_existing_user_photo($user_id, $type);
				update_user_meta($user_id, $meta_key, $media_id );

				return $file_url;
			}
		}

		return false;

	}

	public function delete_existing_user_photo($user_id, $type){
		$meta_key = $type=='cover_photo' ? '_tutor_cover_photo' : '_tutor_profile_photo';
		$photo_id = get_user_meta($user_id, $meta_key, true);
		is_numeric($photo_id) ? wp_delete_attachment( $photo_id, true) : 0;
		delete_user_meta( $user_id, $meta_key);
	}

	/**
	 * @param $user
	 * @param $request
	 *
	 *
	 * @return object
	 *
	 * Reset user's password
	 */

	public function tutor_reset_password_mb($user,$request) {

    	$object = new \stdClass();
		$previous_password = sanitize_text_field($request['previous_password']);
		$new_password = sanitize_text_field($request['new_password']);
		$confirm_new_password = sanitize_text_field($request['confirm_new_password']);

		$previous_password_checked = wp_check_password( $previous_password, $user->user_pass, $user->ID);

		$validation_errors = array();
		if ( ! $previous_password_checked){
			$validation_errors['incorrect_previous_password'] = __('Incorrect Previous Password', 'tutor');
		}
		if (empty($new_password)){
			$validation_errors['new_password_required'] = __('New Password Required', 'tutor');
		}
		if (empty($confirm_new_password)){
			$validation_errors['confirm_password_required'] = __('Confirm Password Required', 'tutor');
		}
		if ( $new_password !== $confirm_new_password){
			$validation_errors['password_not_matched'] = __('New password and confirm password does not matched', 'tutor');
		}
		if (count($validation_errors)){
			$object->type = 0;
			$object->message = $validation_errors;

			add_filter('tutor_reset_password_validation_errors', array($this, 'tutor_student_form_validation_errors'));

		}

		else if ($previous_password_checked && ! empty($new_password) && $new_password === $confirm_new_password){
			wp_set_password($new_password, $user->ID);
			$object->type = 1;
		}
		return $object;

	}

	public function tutor_check_api_connection($user_id) {

		$object = new \stdClass();
		if(\Edumall_Tutor_Zoom::instance()->is_activate()) {
			$users = $this->tutor_zoom_get_users_mb($user_id);
			if ( ! empty( $users ) ) {
				$object->type = 1;
				$object->msg = translate( 'Zoom successfully connected', 'tutor-pro' ) ;

			} else {
				$object->type = 0;
				$object->msg =  translate( 'Please Enter Valid Credentials', 'tutor-pro' ) ;

			}
		} else {
			$object->type = 0;
			$object->msg = translate( 'Please activate tutor-pro', 'tutor-pro' ) ;
		}
		return $object;
	}

	public function tutor_check_save_zoom_api($user_id,$request) {

		$object = new \stdClass();
		if(\Edumall_Tutor_Zoom::instance()->is_activate()) {
			do_action( 'tutor_save_zoom_api_before' );
			$api_data               = array();
			$api_data['api_key']    = $request['api_key'];
			$api_data['api_secret'] = $request['api_secret'];

			$api_data = apply_filters( 'tutor_zoom_api_input', $api_data );

			$result = update_user_meta( $user_id, $this->zoom_api_key, json_encode( $api_data ) );
			if ( $result ) {
				$object->type = 1;
				$object->msg  = __( 'Settings Updated', 'tutor-pro' );

			} else {
				$object->type = 0;
				$object->msg  = __( 'Settings Updated', 'tutor-pro' );
			}
			do_action( 'tutor_save_zoom_api_after' );
		}else {
			$object->type = 0;
			$object->msg = __( 'Please activate tutor-pro', 'tutor-pro' ) ;
		}

		return $object;
	}

	/**
	 * Get Zoom Users from Zoom API
	 * @return array
	 */
	public function tutor_zoom_get_users_mb($user_id) {
		//$api_key    = 'kyEUaRlaSX6HFbaUVQ-U9A';
		//$api_secret = 'atPLzm6tCMyIO3q3FoIi9Qh1pf644loEh3oz';
		$settings   = json_decode(get_user_meta($user_id, $this->zoom_api_key, true), true);

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
		return $users;
	}


	private function get_api_zoom($user_id,$key = null) {
		$api_data = json_decode(get_user_meta($user_id, $this->zoom_api_key, true), true);
		return $this->get_option_data($key, $api_data);
	}

	private function get_option_data($key, $data) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return false;
		}
		if ( ! $key ) {
			return $data;
		}
		if ( array_key_exists( $key, $data ) ) {
			return apply_filters( $key, $data[ $key ] );
		}
	}


	/**
	 * Custom ajax for updating review.
	 *
	 * @see \TUTOR\Ajax::tutor_update_review_modal()
	 */
	public function update_review_modal($request,$user_id) {
		global $wpdb;

		$review_id = (int) sanitize_text_field( $request['review_id']);
		$rating    = sanitize_text_field($request['rating']);
		$review    = wp_kses_post( $request['review']);

		// Get post id to add meta.
		$is_exists = $wpdb->get_row( "select comment_ID, comment_post_ID from {$wpdb->comments} WHERE comment_ID={$review_id} AND comment_type = 'tutor_course_rating' ;");


		$object = new \stdClass();
		if ( ! empty( $is_exists ) ) {
			$wpdb->update( $wpdb->comments, array( 'comment_content' => $review ),
				array( 'comment_ID' => $review_id )
			);
			$wpdb->update( $wpdb->commentmeta, array( 'meta_value' => $rating ),
				array( 'comment_id' => $review_id, 'meta_key' => 'tutor_rating' )
			);

			$this->update_course_rating( $is_exists->comment_post_ID );
			$object->type = 1;
			$object->msg = '' ;
			return $object;
		}
		$object->type = 0;
		$object->msg = '' ;
		return $object;


	}

	/**
	 * @param int $post_id
	 */
	public function update_course_rating( $post_id ) {
		/**
		 * Custom code here.
		 * Save meta for post.
		 */
		$course_rating = tutor_utils()->get_course_rating( $post_id );

		/**
		 * Set post meta
		 * Used for sorting.
		 */
		update_post_meta( $post_id, '_course_average_rating', $course_rating->rating_avg );

		/**
		 * Set post term visibility.
		 * Used for filtering.
		 */

		// Remove old rated term.
		$tags           = wp_get_post_terms( $post_id, 'course-visibility' );
		$tags_to_delete = [
			'rated-1',
			'rated-2',
			'rated-3',
			'rated-4',
			'rated-5',
		];
		$tags_to_keep   = [];
		foreach ( $tags as $t ) {
			if ( ! in_array( $t->name, $tags_to_delete ) ) {
				$tags_to_keep[] = $t->name;
			}
		}
		$int_rating_average = round( $course_rating->rating_avg );
		$current_term_rated = 'rated-' . $int_rating_average;

		$tags_to_keep[] = $current_term_rated;

		wp_set_post_terms( $post_id, $tags_to_keep, 'course-visibility', false );
	}

	/**
	 * @param int $post_id
	 */
	public function get_my_courses( $request,$user_id ) {
		$object = new \stdClass();
		$my_courses = tutor_utils()->get_courses_by_instructor( $user_id, [ 'publish', 'draft', 'pending' ] );


		$arr = [];

			if ( is_array( $my_courses ) && count( $my_courses ) ) {
				global $post;

				foreach ( $my_courses as $post ) {
					setup_postdata($post);
					$object1                = new \stdClass();
					$object1->ID            = $post->ID;
					$object1->title         = $post->post_title;
					$object1->course_rating = tutor_utils()->get_course_rating();
					$object1->avg_rating    = $object1->course_rating->rating_avg;
					$object1->rating_count  = $object1->course_rating->rating_count;

					$object1->tutor_course_img  = get_the_post_thumbnail_url($post->ID);
					$object1->total_lessons     = tutor_utils()->get_lesson_count_by_course();
					$object1->completed_lessons = tutor_utils()->get_completed_lesson_count_by_course();

					$object1->course_duration = get_tutor_course_duration_context();
					$object1->course_students = tutor_utils()->count_enrolled_users_by_course();

					$object1->status = ucwords( $post->post_status );
					$object1->status = ( $object1->status == 'Publish' ) ? __( 'Published', 'edumall' ) : $object1->status;
					$object1->price  = Edumall_Mobile_Utils::tutor_price( tutor_utils()->get_course_price( $post->ID ) );
					$arr []          = $object1;
				}
				wp_reset_postdata();
			}

		$object->arr = $arr;
		return $object;

	}


	/**
	 * Added hook to prevent trash course.
	 *
	 * @see \TUTOR\Course::tutor_delete_dashboard_course()
	 *
	 * @return mixed|void
	 */
	public function delete_dashboard_course($request,$user_id) {
		$course_id = intval( sanitize_text_field( $request['course_id'] ) );

		/**
		 * Filters whether a post trashing should take place.
		 *
		 * @since 1.2.2
		 *
		 * @param bool|null $trash Whether to go forward with trashing.
		 * @param int       $course_id
		 */


		$result = wp_trash_post( $course_id );
		$object = new \stdClass();
		if($result){
			$object->type = 1;
			$object->msg = '';
		}
		else {
			$object->type = 0;
			$object->msg = '';
		}
		return $object;

	}


	/**
	 * Quiz Attempts, I attempted to courses
	 *
	 * @since   v.1.1.2
	 *
	 * @author  Themeum
	 * @url https://themeum.com
	 *
	 *
	 * @package TutorLMS/Templates
	 * @version 1.6.4
	 */
	public function tutor_my_quizz_attempt($request,$user_id) {

		$object = new \stdClass();
		$previous_attempts = tutor_utils()->get_all_quiz_attempts_by_user($user_id);
		$attempted_count   = is_array( $previous_attempts ) ? count( $previous_attempts ) : 0;
		$static_arr = [];
		$static_arr['course_info'] = __( 'Course Info', 'edumall' );
		$static_arr['course_answer'] = __( 'Correct Answer', 'edumall' );
		$static_arr['incourse_answer'] = __( 'Incorrect Answer', 'edumall' );
		$static_arr['earned_marks'] = __( 'Earned Marks', 'edumall' );
		$static_arr['result'] = __( 'Result', 'edumall' );
		$static_arr['you_have_not_attempted']  =  __( 'You have not attempted any quiz yet.', 'edumall' );

		$object->attempted_count = $attempted_count;
		$attempt_arr = [];
		if ( $attempted_count ) {
			foreach ( $previous_attempts as $attempt ) {
				$object_attempts = new \stdClass();
				$attempt_action    = tutor_utils()->get_tutor_dashboard_page_permalink( 'my-quiz-attempts/attempts-details/?attempt_id=' . $attempt->attempt_id );
				$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
				$passing_grade     = (int) tutor_utils()->get_quiz_option( $attempt->quiz_id, 'passing_grade', 0 );
				$answers           = tutor_utils()->get_quiz_answers_by_attempt_id( $attempt->attempt_id );
				$object_attempts->attempt_action = $attempt_action;
				$object_attempts->earned_percentage = $earned_percentage;
				$object_attempts->passing_grade = $passing_grade;
				$object_attempts->course_link = get_the_permalink( $attempt->course_id );
				$object_attempts->coure_name = wp_strip_all_tags(get_the_title( $attempt->course_id ));

				$object_attempts->course_id = $attempt->course_id;
				$object_attempts->attempt_ended_at  = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $attempt->attempt_ended_at ) );
				$object_attempts->total_question  = count( $answers );
				$object_attempts->total_marks  = wp_strip_all_tags($attempt->total_marks);

				$correct   = 0;
				$incorrect = 0;
				if ( is_array( $answers ) && count( $answers ) > 0 ) {
					foreach ( $answers as $answer ) {
						if ( (bool) isset( $answer->is_correct ) ? $answer->is_correct : '' ) {
							$correct++;
						} else {
							if ( $answer->question_type === 'open_ended' || $answer->question_type === 'short_answer' ) {
							} else {
								$incorrect++;
							}
						}
					}
				}
				$object_attempts->correct = $correct;
				$object_attempts->incorrect = $incorrect;
				$object_attempts->earned_marks = wp_strip_all_tags( $attempt->earned_marks . ' (' . $passing_grade . '%)' );

				if ( $attempt->attempt_status === 'review_required' ) {
					$object_attempts->result = __( 'Under Review', 'edumall' ) ;
				} else {
					if ( $earned_percentage >= $passing_grade ) {
						$object_attempts->result = __( 'Pass', 'edumall' );

					} else {
						$object_attempts->result = __( 'Fail', 'edumall' );

					}
				}
				$attempt_arr[] =$object_attempts;

			}

		}
		$object->my_quizz_attempt = $attempt_arr;
		$object->static_arr = $static_arr;
		return $object;

	}

	/**
	 * @package TutorLMS/Templates
	 * @version 1.4.3
	 */

	public function tutor_dashboard_purchase_history($request,$user_id) {
		$object = new \stdClass();
		$orders      = Edumall_Mobile_Utils::get_orders_by_user_id($user_id);
		$monetize_by = tutils()->get_option( 'monetize_by' );
		$static_arr = [];
		$static_arr['purchase_history'] = __( 'Purchase History', 'edumall' );
		$static_arr['ID'] = __( 'ID', 'edumall' );
		$static_arr['Courses'] = __( 'Courses', 'edumall' );
		$static_arr['Amount'] = __( 'Amount', 'edumall' );
		$static_arr['Status'] = __( 'Status', 'edumall' );
		$static_arr['Date'] = __( 'Date', 'edumall' );
		$static_arr['No_Purchase'] = __( 'No purchase history available.', 'edumall' );
		$arr_purchase = [];

		if ( tutils()->count( $orders ) ){
			foreach ( $orders as $order ) {
				$object_purchase = new \stdClass();
				if ( $monetize_by === 'wc' ) {
					$wc_order = wc_get_order( $order->ID );
					$price    = wp_strip_all_tags(Edumall_Mobile_Utils::tutor_price($wc_order->get_total() ));
					$status   =wp_strip_all_tags(tutils()->order_status_context( $order->post_status ));
					$object_purchase->get_total = $wc_order->get_total()  ;
					$object_purchase->price = $price ;
					$object_purchase->status = $status ;
				} else if ( $monetize_by === 'edd' ) {
					$edd_order = wp_strip_all_tags(edd_get_payment( $order->ID ));
					$price     = wp_strip_all_tags(edd_currency_filter( edd_format_amount( $edd_order->total ), edd_get_payment_currency_code( $order->ID ) ));
					$status    = $edd_order->status_nicename;
					$object_purchase->price = $price ;
					$object_purchase->status = $status ;
				}
				$object_purchase->order_id = '#'.$order->ID;

				$courses = tutils()->get_course_enrolled_ids_by_order_id( $order->ID );
				$object_purchase->title ='';
				if ( tutils()->count( $courses ) ) {
					$title = '';
					foreach ( $courses as $course ) {
						$title .= get_the_title( $course['course_id'] ) ;
					}
					$object_purchase->title = $title;
				}
				$object_purchase->date  = wp_date( get_option( 'date_format' ), strtotime( $order->post_date ) );

				$arr_purchase[] = $object_purchase;
			}

		}
		$object->purchase_history = $arr_purchase;

		$object->static_arr = $static_arr;
		return $object;

	}


	public function tutor_dashboard_earning($request,$user_id) {
		$object = new \stdClass();
		global $wpdb;

		$instructor_id = $user_id;

		$earning_sum = tutor_utils()->get_earning_sum($instructor_id);
		$object->has_earning = true;

		 if ( ! $earning_sum ) {
		 	$object->has_earning = false;
			return $object;
		 }

		$complete_status = tutor_utils()->get_earnings_completed_statuses();
		$complete_status = "'" . implode( "','", $complete_status ) . "'";

		/**
		 * Getting the last week.
		 */
		$start_date = date( "Y-m-01" );
		$end_date   = date( "Y-m-t" );

		/**
		 * Format Date Name
		 */
		$begin    = new \DateTime( $start_date );
		$end      = new \DateTime( $end_date . ' + 1 day' );
		$interval = \DateInterval::createFromDateString( '1 day' );
		$period   = new \DatePeriod( $begin, $interval, $end );

		$datesPeriod = array();
		foreach ( $period as $dt ) {

			$datesPeriod[ $dt->format( "Y-m-d" ) ] = 0;

		}



		/**
		 * Query This Month.
		 */

		$salesQuery = $wpdb->get_results( "
              SELECT SUM(instructor_amount) as total_earning,
              DATE(created_at)  as date_format
              from {$wpdb->prefix}tutor_earnings
              WHERE user_id = {$user_id} AND order_status IN({$complete_status})
              AND (created_at BETWEEN '{$start_date}' AND '{$end_date}')
              GROUP BY date_format
              ORDER BY created_at ASC ;" );

		$total_earning = wp_list_pluck( $salesQuery, 'total_earning' );
		$queried_date  = wp_list_pluck( $salesQuery, 'date_format' );
		$dateWiseSales = array_combine( $queried_date, $total_earning );



		$chartData = array_merge( $datesPeriod, $dateWiseSales );

		$arr_chart = [];
		foreach ( $chartData as $key => $salesCount ) {
			unset( $chartData[ $key ] );
			$formatDate               = date( 'd', strtotime( $key ) );
			$chartData[ $formatDate ] = $salesCount;
			$object_chart = new \stdClass();
			$object_chart->key = (int)$formatDate ;
			$object_chart->value =$salesCount;

				$arr_chart[] = $object_chart;
		}
		$object->chartData = $arr_chart;

		$object->my_balance = Edumall_Mobile_Utils::tutor_price( $earning_sum->balance );
		$object->my_earning = Edumall_Mobile_Utils::tutor_price( $earning_sum->instructor_amount );
		$object->all_time_sale = Edumall_Mobile_Utils::tutor_price( $earning_sum->course_price_total );
		$object->all_time_withdraws = Edumall_Mobile_Utils::tutor_price( $earning_sum->withdraws_amount );
		$object->deducted_commissions = Edumall_Mobile_Utils::tutor_price( $earning_sum->admin_amount  );
		$object->deduct_fees_amount = $earning_sum->deduct_fees_amount;
		if($earning_sum->deduct_fees_amount > 0){
			$object->deducted_fees = Edumall_Mobile_Utils::tutor_price( $earning_sum->deduct_fees_amount  );
		}
		$object->month_date = date( "F" );



		$static_arr = [];
		$static_arr['purchase_history'] = __( 'Purchase History', 'edumall' );


		$object->static_arr = $static_arr;
		return $object;

	}


	public function tutor_dashboard_withdrawals($request,$user_id) {
		$object = new \stdClass();
		$earning_sum                   = tutor_utils()->get_earning_sum($user_id);
		$min_withdraw                  = tutor_utils()->get_option( 'min_withdraw_amount' );
		$formatted_min_withdraw_amount = Edumall_Mobile_Utils::tutor_price( $min_withdraw );

		$saved_account        = tutor_utils()->get_user_withdraw_method($user_id);
		$withdraw_method_name = tutor_utils()->avalue_dot( 'withdraw_method_name', $saved_account );


		$object->min_withdraw = $min_withdraw;
		$object->formatted_min_withdraw_amount = $formatted_min_withdraw_amount;

		$object->withdraw_method_name = $withdraw_method_name;
		$object->withdraw_method_key = tutor_utils()->avalue_dot( 'withdraw_method_key', $saved_account );

		$balance_formatted     = Edumall_Mobile_Utils::tutor_price( $earning_sum->balance );
		$is_balance_sufficient = $earning_sum->balance >= $min_withdraw;
		$all_histories         = tutor_utils()->get_withdrawals_history( $user_id, array(
			'status' => array(
				'pending',
				'approved',
				'rejected',
			),
		) );

		$object->balance_formatted = $balance_formatted;
		$object->is_balance_sufficient = $is_balance_sufficient;

		$image_base   = tutor()->url . 'assets/images/';
		$method_icons = array(
			'bank_transfer_withdraw' => $image_base . 'icon-bank.svg',
			'echeck_withdraw'        => $image_base . 'icon-echeck.svg',
			'paypal_withdraw'        => $image_base . 'icon-paypal.svg',
		);
		$object->method_icons =$method_icons;

		$status_message = array(
			'rejected' => esc_html__( 'Please contact the site administrator for more information.', 'edumall' ),
			'pending'  => esc_html__( 'Withdrawal request is pending for approval, please hold tight.', 'edumall' ),
		);
		$object->status_message =$status_message;



		$currency_symbol = '';
		if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
			$currency_symbol = get_woocommerce_currency_symbol();
		} else if ( function_exists( 'edd_currency_symbol' ) ) {
			$currency_symbol = edd_currency_symbol();
		}
		$object->currency_symbol = $currency_symbol;


		 if ( $earning_sum->balance >= $min_withdraw ) {
			 $object->has_withdrawals = true;

		 }
		 else{
			 $object->has_withdrawals = false;
		 }

		 $withdaraw_history_arr = [];

		if ( tutor_utils()->count( $all_histories->results ) ){
			foreach ( $all_histories->results as $withdraw_history ){
				$object_history = new \stdClass();
				$method_data  = maybe_unserialize( $withdraw_history->method_data );
				$method_key   = $method_data['withdraw_method_key'];
				$method_title = '';
				switch ( $method_key ) {
					case 'bank_transfer_withdraw':
						$method_title = $method_data['account_number']['value'];
						$method_title = substr_replace( $method_title, '****', 2, strlen( $method_title ) - 4 );

						$object_history->method_title = $method_title;

						break;
					case 'paypal_withdraw':
						$method_title = $method_data['paypal_email']['value'];
						$email_base   = substr( $method_title, 0, strpos( $method_title, '@' ) );
						$method_title = substr_replace( $email_base, '****', 2, strlen( $email_base ) - 3 ) . substr( $method_title, strpos( $method_title, '@' ) );
						$object_history->method_title = $method_title;



						break;
				}

				$object_history->withdraw_method_name  = tutor_utils()->avalue_dot( 'withdraw_method_name', $method_data );
				$object_history->request_on  = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $withdraw_history->created_at ) );
				$object_history->amount = Edumall_Mobile_Utils::tutor_price( $withdraw_history->amount );
				$object_history->first_withdraw_status =  __( ucfirst( $withdraw_history->status ), 'edumall' );
				if ( $withdraw_history->status !== 'approved' && isset( $status_message[ $withdraw_history->status ] ) ) {

					$object_history->status_msg = __( $status_message[ $withdraw_history->status ] );

				}
				$withdaraw_history_arr[] = $object_history;


			}
		}
		$object->withdraw_history = $withdaraw_history_arr;
		return $object;

	}


	public function tutor_quizz_attempts($request,$user_id) {
		$object = new \stdClass();
		$per_page     = 20;
		$current_page = max( 1, $request['current_page'] );
		$offset       = ( $current_page - 1 ) * $per_page;
		$object->current_page = $current_page;
		$course_id           = tutor_utils()->get_assigned_courses_ids_by_instructors($user_id);
		$quiz_attempts       = tutor_utils()->get_quiz_attempts_by_course_ids( $offset, $per_page, $course_id );
		$quiz_attempts_count = tutor_utils()->get_total_quiz_attempts_by_course_ids( $course_id );
		$object->quiz_attempts_count = $quiz_attempts_count;
		$object->total_page = ceil( $quiz_attempts_count / $per_page );
		$attempt_arr= [];
		if ( $quiz_attempts_count )
		{

			foreach ( $quiz_attempts as $attempt ) {
				$object_at =  new \stdClass();
				$attempt_action    = tutor_utils()->get_tutor_dashboard_page_permalink( 'quiz-attempts/quiz-reviews/?attempt_id=' . $attempt->attempt_id );
				$earned_percentage = $attempt->earned_marks > 0 ? ( number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks ) ) : 0;
				$passing_grade     = tutor_utils()->get_quiz_option( $attempt->quiz_id, 'passing_grade', 0 );
				$answers           = tutor_utils()->get_quiz_answers_by_attempt_id( $attempt->attempt_id );
				$object_at->attempt_action = $attempt_action;
				$object_at->passing_grade = $passing_grade;

				$object_at->courselink = get_the_permalink( $attempt->course_id );
				$object_at->course_title =  wp_strip_all_tags(get_the_title( $attempt->course_id ));
				$object_at->course_id =   $attempt->course_id ;
				$object_at->attempt_date  = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $attempt->attempt_ended_at ) );

				$object_at->total_questions  = count( $answers );
				$object_at->total_marks  = $attempt->total_marks ;
				$object_at->display_name  = wp_strip_all_tags($attempt->display_name) ;
				$object_at->user_email  = wp_strip_all_tags($attempt->user_email );

				$correct   = 0;
				$incorrect = 0;
				if ( is_array( $answers ) && count( $answers ) > 0 ) {
					foreach ( $answers as $answer ) {
						if ( (bool) isset( $answer->is_correct ) ? $answer->is_correct : '' ) {
							$correct++;
						} else {
							if ( $answer->question_type === 'open_ended' || $answer->question_type === 'short_answer' ) {
							} else {
								$incorrect++;
							}
						}
					}
				}
				$object_at->correct  = $correct;
				$object_at->incorrect  = $incorrect;
				$object_at->earnmarks =  wp_strip_all_tags( $attempt->earned_marks . ' (' . $passing_grade . '%)' );
				if ( $attempt->attempt_status === 'review_required' ) {
					$object_at->status = __( 'Under Review', 'edumall' );
				} else {
					if ( $earned_percentage >= $passing_grade ) {
						$object_at->status = __( 'Pass', 'edumall' );

					} else {
						$object_at->status = __( 'Fail', 'edumall' );
					}
				}

				$attempt_arr[] = $object_at;

			}



		}
		$object->attempts = $attempt_arr;

		return $object;

	}


	public function tutor_dashboard_question_answer($request,$user_id) {
		$object = new \stdClass();

		$per_page     = 10;
		$current_page = max( 1,$request['current_page'] );
		$offset       = ( $current_page - 1 ) * $per_page;
		$object->current_page = $current_page;
		$total_items = $this->get_total_qa_question($user_id);
		$object->total_page = ceil( $total_items / $per_page );
		$questions   = $this->get_qa_questions( $user_id,$offset, $per_page );
		$question_answer_arr = [];

		if ( tutils()->count( $questions ) ){
			foreach ( $questions as $question ){
				$object_qa = new \stdClass();
				$questioner_profile_url = tutor_utils()->profile_url( $question->user_id );
				$object_qa->questioner_profile_url = $questioner_profile_url ;
				$object_qa->comment_ID = $question->comment_ID;
				$object_qa->question_title = __( $question->question_title );
				$object_qa->avatar = Edumall_Mobile_Utils::get_avatar_mb( $question->user_id, 52 );
				$object_qa->display_name = __( $question->display_name );
				$object_qa->comment_date =  wp_strip_all_tags( sprintf( __( '%s ago', 'edumall' ), human_time_diff( strtotime
				( $question->comment_date ) ) ) );
				$object_qa->comment_title = wp_strip_all_tags( $question->post_title );
				$object_qa->total_answer = wp_strip_all_tags( number_format_i18n( $question->answer_count ) );

				$question_answer_arr [] = $object_qa;

			}
		}
		$object->question_answer = $question_answer_arr;


		return $object;

	}

	public function tutor_dashboard_assignment($request,$user_id) {
		global $wpdb;
		$object = new \stdClass();
		$per_page     = 20;
		$current_page = max( 1,$request['current_page'] );
		$offset       = ( $current_page - 1 ) * $per_page;
		$object->current_page = $current_page;

		$assignments  = tutor_utils()->get_assignments_by_instructor( $user_id, compact( 'per_page', 'offset' ) );
		$object->total_page = 0;
		$assignment_arr = [];
		if ( $assignments->count ){
			$object->total_page = ceil( $assignments->count / $per_page );
			foreach ( $assignments->results as $item ) {
				$object_a = new \stdClass();
				$max_mark      = tutor_utils()->get_assignment_option( $item->ID, 'total_mark' );
				$course_id     = tutor_utils()->get_course_id_by_assignment( $item->ID );
				$course_url    = tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments/course' );
				$submitted_url = tutor_utils()->get_tutor_dashboard_page_permalink( 'assignments/submitted' );
				$comment_count = $wpdb->get_var( "SELECT COUNT(comment_ID) FROM {$wpdb->comments} WHERE comment_type = 'tutor_assignment' AND comment_post_ID = $item->ID" );

				$object_a->total_submit = number_format_i18n( $comment_count );
				$object_a->title = get_the_title( $course_id );
				$object_a->total_mark = $max_mark;
				$object_a->course_url = $course_url;
				$object_a->submitted_url = $submitted_url;
				$object_a->id = $item->ID;

				$assignment_arr[] = $object_a;
			}
		}
		$object->assignments = $assignment_arr;


		return $object;

	}


	/**
	 * @param string $search_term
	 *
	 * @return int
	 *
	 * Get total number of Q&A questions
	 *
	 * @since v.1.0.0
	 */
	public function get_total_qa_question( $user_id,$search_term = '' ) {
		global $wpdb;

		wp_set_current_user( $user_id );
		$course_type = tutor()->course_post_type;
		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		$in_question_id_query = '';
		/**
		 * Get only assinged  courses questions if current user is a
		 */
		if ( ! current_user_can( 'administrator' ) && current_user_can( tutor()->instructor_role ) ) {

			$get_course_ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT ID
				FROM 	{$wpdb->posts}
				WHERE 	post_author = %d
						AND post_type = %s
						AND post_status = %s
				",
				$user_id,
				$course_type,
				'publish'
			) );

			$get_assigned_courses_ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT meta_value
				FROM	{$wpdb->usermeta}
				WHERE 	meta_key = %s
						AND user_id = %d
				",
				'_tutor_instructor_course_id',
				$user_id
			) );

			$my_course_ids = array_unique( array_merge( $get_course_ids, $get_assigned_courses_ids ) );

			if ( tutils()->count( $my_course_ids ) ) {
				$implode_ids = implode( ',', $my_course_ids );
				$in_question_id_query = " AND {$wpdb->comments}.comment_post_ID IN($implode_ids) ";
			}
		}

		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT({$wpdb->comments}.comment_ID) 
			FROM	{$wpdb->comments}
					INNER JOIN {$wpdb->commentmeta}
					ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id 
			WHERE 	comment_type = %s 
					AND comment_parent = 0 {$in_question_id_query}
					AND {$wpdb->commentmeta}.meta_value LIKE %s;
			",
			'tutor_q_and_a',
			$search_term
		) );

		return (int) $count;
	}

	/**
	 * @param int $start
	 * @param int $limit
	 * @param string $search_term
	 *
	 * @return array|null|object
	 *
	 *
	 * Get question and answer query
	 *
	 * @since v.1.0.0
	 */
	public function get_qa_questions( $user_id,$start = 0, $limit = 10, $search_term = '' ) {
		global $wpdb;


		wp_set_current_user( $user_id );
		$course_type = tutor()->course_post_type;
		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		$in_question_id_query = '';
		/**
		 * Get only assinged  courses questions if current user is a
		 */
		if ( ! current_user_can( 'administrator' ) && current_user_can( tutor()->instructor_role ) ) {

			$get_course_ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT ID
				FROM 	{$wpdb->posts}
				WHERE 	post_author = %d
						AND post_type = %s
						AND post_status = %s
				",
				$user_id,
				$course_type,
				'publish'
			) );

			$get_assigned_courses_ids = $wpdb->get_col( $wpdb->prepare(
				"SELECT meta_value
				FROM	{$wpdb->usermeta}
				WHERE 	meta_key = %s
						AND user_id = %d
				",
				'_tutor_instructor_course_id',
				$user_id
			) );

			$my_course_ids = array_unique( array_merge( $get_course_ids, $get_assigned_courses_ids ) );

			if ( tutils()->count( $my_course_ids ) ) {
				$implode_ids = implode( ',', $my_course_ids );
				$in_question_id_query = " AND {$wpdb->comments}.comment_post_ID IN($implode_ids) ";
			}
		}

		$query = $wpdb->get_results( $wpdb->prepare(
			"SELECT {$wpdb->comments}.comment_ID, 
					{$wpdb->comments}.comment_post_ID, 
					{$wpdb->comments}.comment_author, 
					{$wpdb->comments}.comment_date, 
					{$wpdb->comments}.comment_content, 
					{$wpdb->comments}.user_id, 
					{$wpdb->commentmeta}.meta_value as question_title, 
					{$wpdb->users}.display_name, 
					{$wpdb->posts}.post_title, 
					(	SELECT  COUNT(answers_t.comment_ID) 
						FROM 	{$wpdb->comments} answers_t 
						WHERE 	answers_t.comment_parent = {$wpdb->comments}.comment_ID
					) AS answer_count
			FROM 	{$wpdb->comments}
					INNER JOIN {$wpdb->commentmeta}
							ON {$wpdb->comments}.comment_ID = {$wpdb->commentmeta}.comment_id
					INNER JOIN {$wpdb->posts}
							ON {$wpdb->comments}.comment_post_ID = {$wpdb->posts}.ID
					INNER JOIN {$wpdb->users}
							ON {$wpdb->comments}.user_id = {$wpdb->users}.ID
			WHERE  	{$wpdb->comments}.comment_type = %s
					AND {$wpdb->comments}.comment_parent = 0
					AND {$wpdb->commentmeta}.meta_value LIKE %s
					{$in_question_id_query}
			ORDER BY {$wpdb->comments}.comment_ID DESC 
			LIMIT %d, %d;
			",
			'tutor_q_and_a',
			$search_term,
			$start,
			$limit
		) );

		return $query;
	}

}
