<?php

namespace edumallmobile\framework;

use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Tutor_Course_Builder_Controller {

	private $additional_meta=array(
		'_tutor_disable_qa',
		'_tutor_is_public_course'
	);
	const TAXONOMY_LANGUAGE = 'course-language';
	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	//get course builder

	public function tutor_get_course_builder_mb($request,$user_id) {
		$object = new \stdClass();
		$object->course_id = $request['course_id'];
		$course_id          = $request['course_id'];
		$post = get_post( $course_id );

		$can_publish_course = (bool) tutor_utils()->get_option( 'instructor_can_publish_course' );
		if ( ! $can_publish_course ) {
				$can_publish_course = current_user_can( 'administrator' );
		}
		$object->can_publish_course = $can_publish_course;
		$static_arr = [];
		$static_arr['exit'] = __( 'Exit', 'edumall' );
		$static_arr['course_info'] = __( 'Course Info', 'edumall' );
		$static_arr['course_title'] = __( 'Course Title', 'edumall' );
		$static_arr['course_ex'] = esc_attr( 'ex. Learn photoshop CS6 from scratch', 'edumall' );
		$static_arr['course_desc'] = __( 'Description', 'edumall' );
		$object->course_title = esc_attr( $post->post_title );
		$object->course_desc = wp_strip_all_tags($post->post_content);
		$object->settings_tab = $this->get_course_settings_tabs($post);
		$object->levels = $this->get_course_level_settings($course_id);
		$object->languages = $this->get_course_language_builder($course_id);
		$object->categoires = $this->get_course_categories_mb($course_id);
		$object->course_price = $this->get_course_price_mb($course_id);
		$object->thumbnails = $this->get_thumbnail_course_mb($course_id);
		$object->video = $this->get_video_course_mb($course_id);
		$object->course_topics = $this->get_course_content($course_id);
		$object->instructors = $this->merge_list_instructor_course($course_id);
		$object->additinal_data = $this->get_additional_data_course_mb($course_id);
		$object->tutor_settings = $this->get_tutor_settings_course_mb($course_id);
		$object->attachments = $this->get_course_attachments($course_id);
		$object->perquisites = $this->get_course_prequisites($course_id);
		$object->zoommeetings =Edumall_Zoom_Controller::instance()->get_zoom_meetings($course_id,$user_id);

		return $object;


	}



	public function get_course_prequisites($course_id) {
		$object = new \stdClass();
		$static_arr = [];
		$static_arr['select_a_course']  = __('Select a Course', 'tutor-pro');
		$static_arr['list_course_that_must']  = __('List courses that must be completed before enrolling for this course.', 'tutor-pro');
		$object->static_arr = $static_arr;


		$courses = tutor_utils()->get_courses(array($course_id));
		$savedPrerequisitesIDS = (array) maybe_unserialize(get_post_meta($course_id, '_tutor_course_prerequisites_ids', true));
		$prequisite_arr = [];
		foreach ($courses as $course){
			$object_prequiste =  new \stdClass();
			$selected = in_array($course->ID, $savedPrerequisitesIDS) ;
			$object_prequiste->selected = $selected;
			$object_prequiste->course_id = $course->ID;
			$object_prequiste->title = $course->post_title;
			$prequisite_arr[] = $object_prequiste;
		}
		$object->prequisites  = $prequisite_arr;
		return $object;
	}

	public function get_course_attachments($course_id) {
		$object = new \stdClass();
		$static_arr = [];
		$static_arr['uplodar_attachemnt']  = __('Upload Attachment', 'tutor-pro');
		$object->static_arr = $static_arr;


		$attachments = tutor_utils()->get_attachments($course_id);
		$attachemnt_arr = [];

		if (is_array($attachments) && count($attachments)) {
			foreach ($attachments as $attachment) {
				$object_attachment = new \stdClass();
				$object_attachment->url = $attachment->url;
				$object_attachment->name = $attachment->name;
				$object_attachment->id = $attachment->id;
				$attachemnt_arr[] = $object_attachment;

			}
		}
		$object->attachments =$attachemnt_arr;
		return $object;
	}

	public function get_tutor_settings_course_mb($course_id) {
		$object = new \stdClass();
		$disable_qa = $this->additional_meta[0];
		$is_public = $this->additional_meta[1];

		$array = [];

		$object_item = new \stdClass();
		$object_item->key = 'is_public_checked';
		$object_item->name = __('Make This Course Public','edumall');
		$object_item->value =get_post_meta($course_id, $is_public, true);
		$object_item->desc = __('No enrollment required','edumall');
		$array[] = $object_item;

		$object_item = new \stdClass();
		$object_item->key = 'disable_qa_checked';
		$object_item->name = __('Disable Q&A','edumall');
		$object_item->value =get_post_meta($course_id, $disable_qa, true);
		$object_item->desc = '';
		$array[] = $object_item;

		$object->settings =$array;
		return $object;

	}

	public function get_additional_data_course_mb($course_id) {
		$object = new \stdClass();


		$duration = maybe_unserialize(get_post_meta($course_id, '_course_duration', true));
		$durationHours = tutor_utils()->avalue_dot('hours', $duration);
		$durationMinutes = tutor_utils()->avalue_dot('minutes', $duration);
		$durationSeconds = tutor_utils()->avalue_dot('seconds', $duration);

		$benefits = get_post_meta($course_id, '_tutor_course_benefits', true);
		$requirements = get_post_meta($course_id, '_tutor_course_requirements', true);
		$target_audience = get_post_meta($course_id, '_tutor_course_target_audience', true);
		$material_includes = get_post_meta($course_id, '_tutor_course_material_includes', true);

		$static_arr = [];
		$static_arr['tota_coure_duration']  = __('Total Course Duration', 'tutor');
		$static_arr['hh']  = __('HH', 'tutor');
		$static_arr['mm']  = __('MM', 'tutor');
		$static_arr['ss']  = __('SS', 'tutor');
		$static_arr['benefit_of_course']  = __('Benefits of the course', 'tutor');
		$static_arr['list_the_knowledge']  = __('List the knowledge and skills that students will learn after completing this course. (One per line)
', 'tutor');
		$static_arr['requirement_instructors']  = __('Requirements/Instructions', 'tutor');
		$static_arr['additional_requirements_or_special']  = __('Additional requirements or special instructions for the students (One per line)', 'tutor');
		$static_arr['targeted_audience']  = __('Targeted Audience', 'tutor');
		$static_arr['specify_the_target']  = __('Specify the target audience that will benefit the most from the course. (One line per target audience.)', 'tutor');
		$static_arr['maerials_included']  = __('Materials Included', 'tutor');
		$static_arr['a_list_of_assets']  = __('A list of assets you will be providing for the students in this course (One per line)', 'tutor');
		$object->static_arr = $static_arr;
		$object->durationHour = $durationHours;
		$object->durationMinutes = $durationMinutes;
		$object->durationSeconds = $durationSeconds;
		$object->benefits = $benefits;
		$object->requirements = $requirements;
		$object->target_audience = $target_audience;
		$object->material_includes = $material_includes;
		return $object;
	}

	public function merge_list_instructor_course($course_id) {
		$object = new \stdClass();
		$intructors = tutor_utils()->get_instructors();
		$saved_instructors = tutor_utils()->get_instructors_by_course($course_id);

		$k=0;
		if($saved_instructors){
			$k=count($saved_instructors);
		}
		$i =0;
		if($intructors){
			foreach ($intructors as $v){
				$selected = false;
				if ($saved_instructors){
					if($i < $k) {
						foreach ( $saved_instructors as $t ) {
							if ( $v->ID == $t->ID ) {
								$i ++;
								$selected = true;
								break;
							}


						}
					}
				}
				$object_instructor =  new \stdClass();
				$object_instructor->id =$v->ID;
				$object_instructor->display_name =$v->display_name;
				$object_instructor->isSelected = $selected;
				$object_instructor->avatar = Edumall_Mobile_Utils::get_avatar_mb( $v->ID, '32x32' );

				$output [] = $object_instructor;
			}
		}


		$static_arr['author']  = __("Author", "tutor");
		$static_arr['add_more_instrutors']  =__('Add More Instructors', 'tutor');
		$static_arr['add_intructors']  =__("Add instructors", "tutor");
		$static_arr['search_instructors']  =__('Search instructors...');

		$object->static_arr = $static_arr;
		$object->instructors = $output;
		return $object;
	}

	public function get_instructors_course($course_id) {
		$object = new \stdClass();
		$saved_instructors = tutor_utils()->get_instructors_by_course($course_id);

		$output = [];
		if ($saved_instructors){
			foreach ($saved_instructors as $t){
				$object_instructor =  new \stdClass();
				$object_instructor->id =$t->ID;
				$object_instructor->display_name =$t->display_name;
				$object_instructor->avatar = Edumall_Mobile_Utils::get_avatar_mb( $t->ID, '32x32' );;
				$output [] = $object_instructor;
			}
		}

		$static_arr = [];
		$static_arr['author']  = __("Author", "tutor");
        $static_arr['add_more_instrutors']  =__('Add More Instructors', 'tutor');
        $static_arr['add_intructors']  =__("Add instructors", "tutor");
        $static_arr['search_instructors']  =__('Search instructors...');

        $object->static_arr = $static_arr;
		$object->instructors = $output;
		return $object;

	}
	public function get_video_course_mb($course_id){
		$object = new \stdClass();
		$static_arr = [];

		$video = maybe_unserialize(get_post_meta($course_id, '_video', true));

		$poster = tutor_utils()->avalue_dot('poster', $video);

		$builder_course_img_src = tutor()->url . 'assets/images/placeholder-course.jpg';
		$poster_url = $builder_course_img_src;
		if ( $poster){
			$poster_url = wp_get_attachment_image_url($poster);
		}
		$object->video= $video;
		$object->urlVideo = '';
		$object->thumbnail_url = '';


		if($video['source'] =='html5'){
			$object->urlVideo = wp_get_attachment_url( $video['source_video_id'] );
			$object->thumbnail_url = '';
		}
		else if($video['source'] =='youtube') {
			$object->urlVideo =  $video['source_youtube'];
			$object->thumbnail_url = $poster_url;
		}

		$object->thumbnail_url = $poster_url;
		$static_arr['coure_intro_video'] =  __('Course Intro Video', 'tutor');
		$static_arr['video_source'] = __('Video Source', 'tutor');
		$static_arr['select_video_source'] = __('Select Video Source', 'tutor');
		$static_arr['select_your_video_type'] = __('Select your preferred video type.', 'tutor');
		$static_arr['upload_your_video_type'] = __('Upload Your Video','tutor');
		$static_arr['file_format']=__('File Format: ');
		$static_arr['upload_video']=__('Upload Video', 'tutor');
		$static_arr['media_id']=__('Media ID', 'tutor');
		$static_arr['video_poster']=__("Video Poster", 'tutor');
		$static_arr['thumbsize']=__("Thumb Size: 700x430 pixels. File Support: jpg, jpeg, or png", 'tutor');
		$static_arr['upload']=__('Upload Image', 'tutor');
		$static_arr['external_video_url']=__('External Video URL', 'tutor');
		$static_arr['youtube_video_url']=__('YouTube Video URL', 'tutor');
		$static_arr['vimeo_video_url']=__('Vimeo Video URL', 'tutor');
		$static_arr['place_your_embedded']=__('Place your embedded code here', 'tutor');
		$static_arr['video_playback_time']=__('Video playback time', 'tutor');
		$object->static_arr =$static_arr;
		return $object;
	}

	public function get_thumbnail_course_mb($course_id){
	$object = new \stdClass();
	$static_arr = [];
	$static_arr['course_thumbnail'] = __( 'Course Thumbnail', 'edumall' );

	$builder_course_img_src = tutor()->url . 'assets/images/placeholder-course.jpg';
	$_thumbnail_url         = get_the_post_thumbnail_url( $course_id );
	$post_thumbnail_id      = get_post_thumbnail_id( $course_id );

	if ( ! $_thumbnail_url ) {
		$_thumbnail_url = $builder_course_img_src;
	}

	$object->thumbnail_url = esc_url( $_thumbnail_url );
	$object->post_thumbnail_id = esc_attr( $post_thumbnail_id );
	$static_arr['upload_image'] = __( 'Upload Image', 'edumall' );
	$object->static_arr =$static_arr;
	return $object;
}

	public function get_course_price_mb($course_id){
		$object = new \stdClass();

		$monetize_by = tutils()->get_option( 'monetize_by' );
		$static_arr = [];
		if ( $monetize_by === 'wc' || $monetize_by === 'edd' ) {
			$course_price               = tutor_utils()->get_raw_course_price( $course_id );
			$currency_symbol            = tutor_utils()->currency_symbol();
			$_tutor_course_price_type   = tutils()->price_type( $course_id );
			$static_arr['course_price'] = __( 'Course Price', 'edumall' );
			$object->isPaid             = $_tutor_course_price_type == 'paid' ? true : false;
			$object->current_symboy     = $currency_symbol;
			$object->course_price       = $course_price->regular_price;
			$static_arr['paid'] = __( 'Paid', 'edumall' );
			$static_arr['set_course_price'] = __( 'Set course price', 'edumall' );
			$static_arr['free'] = __( 'Free', 'edumall' );

		}
		$object->static_arr =$static_arr;
         return $object;
	}

	public function get_course_categories_mb($post_ID) {
		$object = new \stdClass();
		$categories = tutor_utils()->get_course_categories();
		$object->categories = $this->generate_categories_dropdown_option_mb($post_ID,$categories);

		return $object;
	}

	public function generate_categories_dropdown_option_mb($post_ID = 0, $categories, $args = array(), $depth = 0){
		$array_child = [];

		if (tutor_utils()->count($categories)) {

			foreach ( $categories as $category_id => $category ) {
				if ( ! $category->parent){
					$depth = 0;
				}
				$object_child = new \stdClass();
				$childrens = tutor_utils()->array_get( 'children', $category );
				$has_in_term = has_term( $category->term_id, 'course-category', $post_ID );

				$depth_seperator = '';
				if ($depth){
					for ($depth_i = 0; $depth_i < $depth; $depth_i++){
						$depth_seperator.='-';
					}
				}

				$object_child->id = $category->term_id;
				$object_child->name = $depth_seperator . $category->name;
				$object_child->selected =  false;
				if($has_in_term == true)
				{
					$object_child->selected =  true;
				}

				if ( tutor_utils()->count( $childrens ) ) {
					$depth++;
					$object_child->childs = $this->generate_categories_dropdown_option_mb($post_ID,$childrens, $args, $depth);
				}

				$array_child[] = $object_child;
			}
		}
		return $array_child;
	}


	public function get_course_settings_tabs($post){
        $object = new \stdClass();
		$args = $this->get_default_args_course_settings_tabs();
		$settings_meta = get_post_meta($post->ID, '_tutor_course_settings', true);
		$settings_meta = (array) maybe_unserialize($settings_meta);

        $static_arr = [];
		$static_arr['course_settings'] = __('Course Settings', 'tutor');
		$object->static_arr = $static_arr;
		$object->fields = [];

		if (tutils()->count($args) && $post->post_type === tutor()->course_post_type) {

			foreach ($args as $key => $tab) {

				$fields = tutils()->array_get('fields', $tab);
				if (tutils()->count($fields)) {

					$object->fields = $this->generate_field_cst( $settings_meta,$fields );
				}

			}

		}


		return $object;

	}

	public function generate_field_cst($settings_meta,$fields = array()){
	    $arr_fields = [];
		if (tutils()->count($fields)){
			foreach ($fields as $field_key => $field){
			    $arr_fields[] = $this->field_type_cst($settings_meta,$field);
			}
		}
		return $arr_fields;
	}


	public function field_type_cst($settings_meta,$field = array()){
	    $object_field = new \stdClass();
	    if($field['type'] == 'number')
        {
	        $object_field->type = $field['type'];

            $value = $this->get_cst($settings_meta,$field['field_key']);
            if ( ! $value && isset($field['default'])){
	            $value = $field['default'];
            }
	        $object_field->value = $value;
	        $object_field->desc = '';
	        if (isset($field['desc'])){
		        $object_field->desc =  $field['desc'];
	        }
        }
	    return $object_field;
	}

	public function get_cst($settings_meta,$key = null, $default = false){
		return tutils()->array_get($key, $settings_meta, $default);
	}

	public function get_course_level_settings($course_id){
		$object = new \stdClass();
		$static_arr = [];

		$static_arr['difficulty_level'] = __('Difficulty Level', 'tutor');
		$object->static_arr = $static_arr;

		$levels = tutor_utils()->course_levels();
		$course_level = get_post_meta($course_id, '_tutor_course_level', true);

		$array_level = [];
		foreach ($levels as $level_key => $level){
			$object_level = new \stdClass();
			$object_level->level = Edumall_Mobile_Utils::course_levels( $level_key );
			$array_level[] = $object_level;

		}
		$object->levels = $array_level;
		if(!$course_level)
		{
			$course_level = 'intermediate';
		}
		$object->course_level = $course_level;

		return $object;
	}


	public function get_default_args_course_settings_tabs(){
		$args = array(
			'general' => array(
				'label' => __('General', 'tutor'),
				'desc' => __('General Settings', 'tutor'),
				'icon_class'  => ' tutor-icon-settings-1',
				'callback'  => '',
				'fields'    => array(
					'maximum_students' => array(
						'type'      => 'number',
						'label'     => __('Maximum Students', 'tutor'),
						'label_title' => __('Enable', 'tutor'),
						'default' => '0',
						'field_key' =>'maximum_students',
						'desc'      => __('Number of students that can enrol in this course. Set 0 for no limits.', 'tutor'),
					),
				),
			),
		);

		return $args;
	}



	public function get_course_language_builder( $post_ID ) {
		$object = new \stdClass();

		$object->selected = -1;
		$terms = get_the_terms( $post_ID, self::TAXONOMY_LANGUAGE );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$object->selected = $terms[0]->term_id;
		}
		$args1 = [
			'taxonomy'         => self::TAXONOMY_LANGUAGE,
			'hide_empty'       => 0,
			'orderby'          => 'name',

		];

		$array_language = [];
		$categories_language = get_categories($args1);
		if($categories_language){
			foreach ($categories_language as $cat_lan){
				$object_language = new \stdClass();
				$object_language->id = $cat_lan->term_id;
				$object_language->name = $cat_lan->name;
				$array_language[] = $object_language;

			}
		}
		$object->language =$array_language;
		return $object;

	}



	//save course builder

	public function tutor_load_template_before(){
		$post_type = tutor()->course_post_type;
		$post_id = wp_insert_post( array( 'post_title' => __( 'Auto Draft', 'tutor' ), 'post_type' => $post_type, 'post_status' => 'auto-draft' ) );
		$object = new \stdClass();
		$object->id = $post_id;
		$object->type = 1;
		$object->msg ='';
		return $object;
	}

	/**
	 * Process course submission from frontend course builder
	 *
	 * @since v.1.3.4
	 */
	public function tutor_add_course_builder_mb($request,$user_id) {

		/**
		 * Update the post
		 */
		$object = new \stdClass();
		$content   = wp_kses_post( $request['content'] );
		$title     = sanitize_text_field( $request['title']);
		$tax_input =  (array)$request['tax_input'];
		$submit_action  = $request['submit_action'];

		$postData = array(
			'ID'           => $request['post_id'],
			'post_title'   => $title,
			'post_name'    => sanitize_title( $title ),
			'post_content' => $content,
		);

		//Publish or Pending...

		if ( $submit_action === 'save_course_as_draft' ) {
			$postData['post_status'] = 'draft';
		} elseif ( $submit_action === 'submit_for_review' ) {
			$postData['post_status'] = 'pending';

		} elseif ( $submit_action === 'publish_course' ) {
			$can_publish_course = (bool) tutor_utils()->get_option( 'instructor_can_publish_course' );
			if ( $can_publish_course ) {
				$postData['post_status'] = 'publish';
			} else {
				$postData['post_status'] = 'pending';
			}
		}

		wp_update_post( $postData );


		/**
		 * Setting Thumbnail
		 */
		if($request['isFeatureImageChange']) {
			if ( $request['imagecoursephoto'] != '' ) {
				$cover_photo_body = base64_decode( $request['imagecoursephoto'] );
				$_thumbnail_id    = Edumall_Mobile_Utils::update_post_photo_mb( $cover_photo_body, $request['imagecoursename'], $request['imagecoursetype'] );
				if ( $_thumbnail_id ) {
					update_post_meta( $request['post_id'], '_thumbnail_id', $_thumbnail_id );
				}
			} else {
				delete_post_meta( $request['post_id'], '_thumbnail_id' );

			}
		}

		/**
		 * Adding taxonomy
		 */

		if ( ! empty($tax_input )) {

			foreach ( $tax_input as $taxonomy => $tags ) {

				$taxonomy_obj = get_taxonomy( $taxonomy );
				if ( ! $taxonomy_obj ) {


					continue;
				}

				// array = hierarchical, string = non-hierarchical.
				if ( is_array( $tags ) ) {
					$tags = array_filter( $tags );
				}
				wp_set_post_terms($request['post_id'], $tags, $taxonomy );
			}
		}


		/**
		 * Adding support for do_action();
		 */

		$this->save_course_setttings_mb($request);
		$object->meta = $this->save_course_meta($request,$user_id);
		$this->attach_product_with_course_id($request);

		$object->post_id =$request['post_id'];
		$object->type = 1;
		$object->msg = '';
		$object->thumbnailUrl = get_the_post_thumbnail_url( $request['post_id'] );
		return $object;



	}



	/**
	 * @param $request
	 *
	 * Insert Topic and attached it with Course
	 */
	public function save_course_meta($request,$user_id){
		$object = new \stdClass();
		global $wpdb;

		/**
		 * Save course price type
		 */
		$price_type = $request['tutor_course_price_type'];

		if ($price_type){
			update_post_meta($request['post_id'], '_tutor_course_price_type', $price_type);
		}

		$course_duration = (array) $request['course_duration'];

		//Course Duration
		if ( ! empty($course_duration)){
			$video = $course_duration;

			update_post_meta($request['post_id'], '_course_duration', $video);
		}

		$course_level = $request['course_level'];
		if ( ! empty($course_level)){
			$course_level = sanitize_text_field($course_level);
			update_post_meta($request['post_id'], '_tutor_course_level', $course_level);
		}


		if (!empty($request['course_benefits'])) {
				$course_benefits = wp_kses_post($request['course_benefits']);
				update_post_meta($request['post_id'], '_tutor_course_benefits', $course_benefits);
		} else {
				delete_post_meta($request['post_id'], '_tutor_course_benefits');
		}

		if (!empty($request['course_requirements'])) {
				$requirements = wp_kses_post($request['course_requirements']);
				update_post_meta($request['post_id'], '_tutor_course_requirements', $requirements);
		} else {
			delete_post_meta($request['post_id'], '_tutor_course_requirements');
		}

		if (!empty($request['course_target_audience'])) {
			$target_audience = wp_kses_post($request['course_target_audience']);
			update_post_meta($request['post_id'], '_tutor_course_target_audience', $target_audience);
		} else {
			delete_post_meta($request['post_id'], '_tutor_course_target_audience');
		}

		if (!empty($request['course_material_includes'])) {
			$material_includes = wp_kses_post($request['course_material_includes']);
			update_post_meta($request['post_id'], '_tutor_course_material_includes', $material_includes);
		} else {
			delete_post_meta($request['post_id'], '_tutor_course_material_includes');
		}


		/**
		 * Sorting Topics and lesson
		 */
		/*
		if ( ! empty($_POST['tutor_topics_lessons_sorting'])){
			$new_order = sanitize_text_field(stripslashes($_POST['tutor_topics_lessons_sorting']));
			$order = json_decode($new_order, true);

			if (is_array($order) && count($order)){
				$i = 0;
				foreach ($order as $topic ){
					$i++;
					$wpdb->update(
						$wpdb->posts,
						array('menu_order' => $i),
						array('ID' => $topic['topic_id'])
					);


					// Removing All lesson with topic


					$wpdb->update(
						$wpdb->posts,
						array('post_parent' => 0),
						array('post_parent' => $topic['topic_id'])
					);


					//  Lesson Attaching with topic ID sorting lesson

					if (isset($topic['lesson_ids'])){
						$lesson_ids = $topic['lesson_ids'];
					}else{
						$lesson_ids = array();
					}
					if (count($lesson_ids)){
						foreach ($lesson_ids as $lesson_key => $lesson_id ){
							$wpdb->update(
								$wpdb->posts,
								array('post_parent' => $topic['topic_id'], 'menu_order' => $lesson_key),
								array('ID' => $lesson_id)
							);
						}
					}
				}
			}
		}*/
		$object->video = $this->save_video_mb( $request );


		$this->save_prerequisites($request);
		$this->save_course_attachment($request);
		if(empty($request['isSaveInstructors'])) {
			$object->instructors = $this->save_instructors( $request, $user_id );
		}

		//Adding author to instructor automatically


		$author_id = $user_id;
		$attached = (int) $wpdb->get_var($wpdb->prepare(
			"SELECT COUNT(umeta_id) FROM {$wpdb->usermeta} 
			WHERE user_id = %d AND meta_key = '_tutor_instructor_course_id' AND meta_value = %d ", $author_id, $request['post_id']));

		if ( ! $attached){
			add_user_meta($author_id, '_tutor_instructor_course_id', $request['post_id']);
		}



		// Disable question and answer for this course @since 1.7.0

		foreach($this->additional_meta as $key) {
			update_post_meta( $request['post_id'], $key, ( isset( $request[ $key ] ) ? 'yes' : 'no' ) );
		}



		$this->save_course_language_mb($request);
		return $object;
	}

	public function save_video_mb($request){


		if ( ! empty($request['source'])) { //Video

			$attach_id = 0;

			if(! empty($request['source_video_id']))
			{
				$attach_id = $request['source_video_id'];
			}
			else {
				if($request['source'] == 'html5') {
					$attach_id = Edumall_Mobile_Utils::save_video_mp4( true );
				}
			}
			$poster = '';

			if(!empty($request['isChangeImagePoster']))
			{
				$poster = $request['isChangeImagePoster'];
			}
			else {
					if ( $request['imageposter'] != '' ) {
						$cover_photo_body = base64_decode( $request['imageposter'] );
						$poster           = Edumall_Mobile_Utils::update_post_photo_mb( $cover_photo_body, $request['imagepostername'], $request['imagepostertype'] );

					}
			}

			$video = Edumall_Mobile_Utils::convertToVideoArray($request,$poster,$attach_id);
			$result = update_post_meta($request['post_id'], '_video', $video);

		}
		else {

			delete_post_meta($request['post_id'], '_video');
		}

		return  tutor_utils()->get_video_info( $request['post_id'] );
	}

	public function save_video_post_id($request,$post_id,$is_get_info_video,$isUpdatePoster = true){
		if ( ! empty($request['type_video']) || ($request['type_video']>0 &&  $request['type_video'] <=2)) { //Video

			$poster = '';
			if(!$isUpdatePoster) {
				if ( $request['imageposter'] != '' ) {
					$cover_photo_body = base64_decode( $request['imageposter'] );
					$poster           = Edumall_Mobile_Utils::update_post_photo_mb( $cover_photo_body, $request['imagepostername'], $request['imagepostertype'] );

				}
			}
			else {
				$poster  =  $request['poster'];
			}

			$attach_id = $request['video_id'];
			$video = Edumall_Mobile_Utils::convertToVideoArrayWithPostId($request,$poster,$attach_id);

			update_post_meta($post_id, '_video', $video);
		}
		else {
			delete_post_meta($post_id, '_video');
		}
		if($is_get_info_video)
		return  tutor_utils()->get_video_info( $post_id );
		return true;
	}

	public function save_prerequisites($request) {

		$prerequisites_course_ids =  explode( ',',$request['_tutor_course_prerequisites_ids']);

		if (is_array($prerequisites_course_ids) && count($prerequisites_course_ids)) {
			update_post_meta( $request['post_id'], '_tutor_course_prerequisites_ids', $prerequisites_course_ids );
		} else {
			delete_post_meta($request['post_id'], '_tutor_course_prerequisites_ids');
		}

	}

	/**
	 * @param $request
	 *
	 */
	public function save_course_setttings_mb($request){
		$_tutor_course_settings = (array)$request['tutor_course_settings'];
		if (tutils()->count($_tutor_course_settings)){
			update_post_meta($request['post_id'], '_tutor_course_settings', $_tutor_course_settings);
		}
	}

	public function save_course_language_mb($request) {
		$language = isset( $request['course-language'] ) && '-1' !== $request['course-language'] ? $request['course-language'] : false;

		if ( ! empty( $language ) ) {
			$integerIDs = array_map( 'intval', [ $language ] );
			$integerIDs = array_unique( $integerIDs );
			wp_set_post_terms($request['post_id'], $integerIDs, 'course-language' );
		}
	}

	public function save_course_attachment($request){
		//Attachments
		$attachments = array();

		if ( !empty($request['tutor_attachments']) ) {

			$attachments = explode(',', $request['tutor_attachments'] );
			$attachments = array_unique( $attachments );

		}
		update_post_meta($request['post_id'], '_tutor_attachments', $attachments);

	}

	public function save_instructors($request,$user_id){
		$object = new \stdClass();
		$output = array();
		$course_id = $request['post_id'];
		$instructor_ids = (array)tutor_utils()->avalue_dot('tutor_instructor_ids', $request);

		if(!tutils()->can_user_manage('course', $course_id,$user_id)) {
			$object->msg = array('message'=>__('Access Denied', 'tutor'));
			$object->type = 0;
			return $object;
		}

		if (is_array($instructor_ids) && count($instructor_ids)){
			foreach ($instructor_ids as $instructor_id){
				add_user_meta($instructor_id, '_tutor_instructor_course_id', $course_id);
			}
		}

		$saved_instructors = tutor_utils()->get_instructors_by_course($course_id);


		if ($saved_instructors){
			foreach ($saved_instructors as $t){
				$object_instructor =  new \stdClass();
				$object_instructor->id =$t->ID;
				$object_instructor->display_name =$t->display_name;
				$object_instructor->avatar = Edumall_Mobile_Utils::get_avatar_mb( $t->ID, '32x32' );;
				$output [] = $object_instructor;
			}
		}
		$object->msg = array('message'=>__('Successfully', 'tutor'));
		$object->type = 1;
		$object->instructors = $output;
		return $object;


	}


	/**
	 * @param $request
	 * @return string
	 */
	public function attach_product_with_course_id($request){
		$attached_product_id = tutor_utils()->get_course_product_id($request['post_id']);
		$course_price = sanitize_text_field($request['course_price']);

		if ( ! $course_price){
			return;
		}

		$monetize_by = tutor_utils()->get_option('monetize_by');
		$course = get_post($request['post_id']);

		if ($monetize_by === 'wc'){

			$is_update = false;
			if ($attached_product_id){
				$wc_product = get_post_meta($attached_product_id, '_product_version', true);
				if ($wc_product){
					$is_update = true;
				}
			}

			if ($is_update) {
				$productObj = wc_get_product($attached_product_id);
				$productObj->set_price($course_price); // set product price
				$productObj->set_regular_price($course_price); // set product regular price
				$product_id = $productObj->save();
				if($productObj->is_type('subscription')) {
					update_post_meta( $attached_product_id, '_subscription_price', $course_price );
				}
			} else {
				$productObj = new \WC_Product();
				$productObj->set_name($course->post_title);
				$productObj->set_status('publish');
				$productObj->set_price($course_price); // set product price
				$productObj->set_regular_price($course_price); // set product regular price

				$product_id = $productObj->save();
				if ($product_id) {
					update_post_meta( $request['post_id'], '_tutor_course_product_id', $product_id );
					//Mark product for woocommerce
					update_post_meta( $product_id, '_virtual', 'yes' );
					update_post_meta( $product_id, '_tutor_product', 'yes' );

					$coursePostThumbnail = get_post_meta( $request['post_id'], '_thumbnail_id', true );
					if ( $coursePostThumbnail ) {
						set_post_thumbnail( $product_id, $coursePostThumbnail );
					}
				}
			}

		}elseif ($monetize_by === 'edd'){

			$is_update = false;

			if ($attached_product_id){
				$edd_price = get_post_meta($attached_product_id, 'edd_price', true);
				if ($edd_price){
					$is_update = true;
				}
			}

			if ($is_update){
				//Update the product
				update_post_meta( $attached_product_id, 'edd_price', $course_price );
			}else{
				//Create new product

				$post_arr = array(
					'post_type'    => 'download',
					'post_title'   => $course->post_title,
					'post_status'  => 'publish',
					'post_author'  => get_current_user_id(),
				);
				$download_id = wp_insert_post( $post_arr );
				if ($download_id ) {
					//edd_price
					update_post_meta( $download_id, 'edd_price', $course_price );

					update_post_meta( $request['post_id'], '_tutor_course_product_id', $download_id );
					//Mark product for EDD
					update_post_meta( $download_id, '_tutor_product', 'yes' );

					$coursePostThumbnail = get_post_meta( $request['post_id'], '_thumbnail_id', true );
					if ( $coursePostThumbnail ) {
						set_post_thumbnail( $download_id, $coursePostThumbnail );
					}

				}

			}


		}

	}


	/**
	 * Tutor add course topic
	 */
	public function tutor_add_course_topic_mb($request,$user_id){

		$object = new \stdClass();
		if (empty($request['topic_title']) ) {
			$object->type = 0;
			$object->msg = 'Error';
			return $object;
		}
		$course_id = (int) $request['tutor_topic_course_ID'];
		$next_topic_order_id = tutor_utils()->get_next_topic_order_id($course_id);

		if(!tutils()->can_user_manage('course', $course_id,$user_id)) {

			$object->type = 0;
			$object->msg = __('Access Denied', 'tutor');


			return $object;
		}

		$topic_title   = sanitize_text_field( $request['topic_title'] );
		$topic_summary = wp_kses_post( $request['topic_summary'] );

		$post_arr = array(
			'post_type'    => 'topics',
			'post_title'   => $topic_title,
			'post_content' => $topic_summary,
			'post_status'  => 'publish',
			'post_author'  => $user_id,
			'post_parent'  => $course_id,
			'menu_order'  => $next_topic_order_id,
		);
		$current_topic_id = wp_insert_post( $post_arr );
		if($current_topic_id) {
			//$object->coure_content = $this->get_course_content( $course_id );
			$object->type          = 1;
			$object->msg           = '';
			$object->current_topic_id = $current_topic_id;


		}
		else {
			$object->type          = 0;
			$object->msg           = '';
		}
		return $object;


	}

	public function get_course_content($course_id){
		/*if (empty($current_topic_id)){
			$current_topic_id = (int) tutor_utils()->avalue_dot('current_topic_id', $_POST);
		}*/

		$object = new \stdClass();

		$query_lesson = tutor_utils()->get_lesson($course_id, -1);

		$attached_lesson_ids = array();

		// tutor_utils()->get_topics function doesn't work correctly for multi instructor case. Rather use get_posts.
		$topic_args = array(
			'post_type'  => 'topics',
			'post_parent'  => $course_id,
			'orderby' => 'menu_order',
			'order'   => 'ASC',
			'posts_per_page'    => -1,
		);
		$query_topics = (object) array('posts' => get_posts($topic_args));

		if ( ! count($query_topics->posts)){
			$object->type =0 ;
			$object->msg = __('Add a topic to build your course', 'tutor') ;
			return $object;
		}

		$array_topic = [];
		$static_text = [];
		$static_text['topic_name'] = __('Topic Name', 'tutor');
		$static_text['topic_name_desc'] = __('Topic title will be publicly show where required, you can call it as a section also in course', 'tutor');
		foreach ($query_topics->posts as $topic){
			$object_topic = new \stdClass();
			$object_topic->ID = $topic->ID;
			$object_topic->topic_name = stripslashes($topic->post_title);
			$object_topic->topic_content = $topic->post_content;

			$array_lession = [];
			 $lessons = tutor_utils()->get_course_contents_by_topic($topic->ID, -1);
			 foreach ($lessons->posts as $lesson){
			 	$object_lession = new \stdClass();
			 	$attached_lesson_ids[] = $lesson->ID;
			 	$object_lession->ID= $lesson->ID;
				$object_lession->lession_title= stripslashes($lesson->post_title);
				$object_lession->lession_type =$lesson->post_type;

				$array_lession [] =  $object_lession;


			 }
			$object_topic->lessions = $array_lession;

			$array_topic [] = $object_topic;
		}
		$object->topics = $array_topic;

		$static_text['un_assign'] =  __( 'Un-assigned lessons' );
		$array_unassigns = [];
		if (count($query_lesson->posts)) {
			if ( count( $query_lesson->posts ) > count( $attached_lesson_ids ) ) {

				 foreach ( $query_lesson->posts as $lesson ) {
							if ( ! in_array( $lesson->ID, $attached_lesson_ids ) ) {
								$object_unassign = new \stdClass();
								$object_unassign->ID= $lesson->ID;
								$object_unassign->lession_title= stripslashes($lesson->post_title);
								$object_unassign->lession_type =$lesson->post_type;
								$array_unassigns[] = $object_unassign;
							}
						}
			 }
		}
		$object->un_assign = $array_unassigns;

		$static_text['add_topic'] = __('Add Topic', 'tutor');
		$static_text['topic_name'] = __('Topic Name', 'tutor');
		$static_text['topic_title_are_displau'] = __('Topic titles are displayed publicly wherever required. Each topic may contain one or more lessons, quiz and assignments.', 'tutor');
		$static_text['topic_summary'] = __('Topic Summary', 'tutor');
		$static_text['the_idea_of_a'] = __('The idea of a summary is a short text to prepare students for the activities within the topic or week. The text is shown on the course page under the topic name.', 'tutor');
		$static_text['quiz'] = __('Quiz', 'tutor');
		$static_text['lesson'] = __('Lesson', 'tutor');
		$static_text['assignments'] = __('Assignments', 'tutor');

		$object->static_text = $static_text;
		$object->course_id = $course_id;
		$object->type =1 ;

		return $object;

	}

	public function tutor_delete_topic($request){
		global $wpdb;

		$topic_id =  $request['topic_id'];
		$wpdb->update(
			$wpdb->posts,
			array('post_parent' => 0),
			array('post_parent' => $topic_id)
		);

		$wpdb->delete(
			$wpdb->postmeta,
			array('post_id' => $topic_id)
		);

		$result = wp_delete_post($topic_id);
		$object = new \stdClass();
		if($result)
		{
			$object->type          = 1;
			$object->msg           = '';
		}
		else
		{
			$object->type          = 0;
			$object->msg           = '';
		}
		return $object;

	}

	/**
	 * Update the topic
	 */
	public function tutor_update_topic($request,$user_id){
		$object = new \stdClass();

		$topic_id = (int) $request['topic_id'];
		$topic_title = $request['topic_title'];
		$topic_summery = wp_kses_post($request['topic_summary']);

		if(!tutils()->can_user_manage('topic', $topic_id,$user_id)) {
			$object->type          = 0;
			$object->topic_id          = $topic_id;
			$object->msg           = __('Access Denied', 'tutor') ;
			return $object;

		}

		$topic_attr = array(
			'ID'           => $topic_id,
			'post_title'   => $topic_title,
			'post_content' => $topic_summery,
		);
		$result = wp_update_post( $topic_attr );
		$object = new \stdClass();
		if($result)
		{
			$object->type          = 1;
			$object->msg           = '';
		}
		else
		{
			$object->type          = 0;
			$object->msg           = '';
		}
		return $object;


	}


	/**
	 * @since v.1.0.0
	 * @updated v.1.5.1
	 */
	public function tutor_modal_create_or_update_lesson($request,$user_id){
		global $wpdb;

		$lesson_id = (int) $request['lesson_id'];
		$topic_id = (int) (int) $request['topic_id'];
		//save thumbnail
		if($lesson_id!=0 && $request['isFeatureImageNework']){


		}
		else {
			$_lesson_thumbnail_id = Edumall_Mobile_Utils::save_image_post( $request );
		}
		$object = new \stdClass();


		if(!tutils()->can_user_manage('topic', $topic_id,$user_id)) {
			$object->msg = array('message'=>__('Access Denied', 'tutor'));
			$object->type = 0;
			return $object;
		}


		$title = $request['lesson_title'];
		$lesson_content = wp_kses_post($request['lesson_content']);

		$lesson_data = array(
			'post_type'    => tutor()->lesson_post_type,
			'post_title'    => $title,
			'post_name'     => sanitize_title($title),
			'post_content'  => $lesson_content,
			'post_status'  => 'publish',
			'post_author'  => $user_id,
			'post_parent'  => $topic_id,
		);

		if($lesson_id==0) {

			$lesson_id = wp_insert_post( $lesson_data );

			if ($lesson_id ) {

				$course_id = $wpdb->get_var( $wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID=%d", $topic_id) );
				update_post_meta( $lesson_id, '_tutor_course_id_for_lesson', $course_id );
			}
			else {
				$object->msg = array('message'=>__('Couldn\'t create lesson.', 'tutor'));
				$object->type = 0;
				return $object;

			}
		}
		else {
			$lesson_data['ID']=$lesson_id;
			wp_update_post($lesson_data);


		}

		if($lesson_id!=0 && $request['isFeatureImageNework']){ }
		else {
			if ( $_lesson_thumbnail_id ) {
				update_post_meta( $lesson_id, '_thumbnail_id', $_lesson_thumbnail_id );
			} else {
				delete_post_meta( $lesson_id, '_thumbnail_id' );
			}
		}
		if($lesson_id!=0 && $request['isPosterImageNetwork']){
			$this->save_video_post_id( $request, $lesson_id, false ,false);
		}
		else {
			$this->save_video_post_id( $request, $lesson_id, false ,true);
		}
		$this->save_coure_preview_meta($lesson_id,$request);
		$this->saveAttachments($lesson_id,$request);
		$object->msg = array('message'=>__('Create lession successfully', 'tutor'));
		$object->type = 1;
		$object->lession_id = $lesson_id;
		return $object;


	}

	public function saveAttachments($post_id,$request) {
		$attachments = array();

		if ( ! empty($request['_tutor_attachments'])){
			$attachments = tutor_utils()->sanitize_array( explode (',',$request['_tutor_attachments']));
			$attachments = array_unique($attachments);
		}

		update_post_meta($post_id, '_tutor_attachments', $attachments);

	}

	//Attachments


	/**
	 * Delete Lesson from course builder
	 */
	public function tutor_delete_lesson_by_id($request,$user_id){

		$lesson_id = (int) $request['lesson_id'];
		$object = new \stdClass();

		if(!tutils()->can_user_manage('lesson', $lesson_id,$user_id)) {
			$object->msg = array('message'=>__('Access Denied', 'tutor'));
			$object->type = 0;
			return $object;
		}

		wp_delete_post($lesson_id, true);
		delete_post_meta($lesson_id, '_tutor_course_id_for_lesson');
		$object->msg = '';
		$object->type = 1;
		return $object;
	}


	public function tutor_load_edit_lesson_modal($request,$user_id){

		$lesson_id = (int) $request['lesson_id'];
		$topic_id = (int) $request['topic_id'];
		$object = new \stdClass();


		if(!tutils()->can_user_manage('topic', $topic_id,$user_id)) {
			$object->msg = array('message'=>__('Access Denied', 'tutor'));
			$object->type = 0;
			return $object;
		}

		/**
		 * If Lesson Not Exists, provide dummy
		 */
		$post_arr = array(
			'ID' 		   => 0,
			'post_content' => '',
			'post_type'    =>  tutor()->lesson_post_type,
			'post_title'   => __('Draft Lesson', 'tutor'),
			'post_status'  => 'publish',
			'post_author'  => $user_id,
			'post_parent'  => $topic_id,
		);

		$post = $lesson_id ? get_post($lesson_id) : (object)$post_arr;

		$object->type = 1;
		$result = new \stdClass();
		$result->lesson_title = $post->post_title;
		$result->lesson_content = wp_strip_all_tags($post->post_content);
		$result->url_coursephoto = \Edumall_Image::get_the_post_thumbnail_url(array( 'size' => '226x150','post_id'=> $post->ID));
		$video = maybe_unserialize(get_post_meta($post->ID, '_video', true));
		$result->video =$video;
		if($video)
		{
			$result->isgetvideo =true;
		}
		else {
			$result->isgetvideo = false;
		}
		$result->urlMp4Video = '';
		if($result->video['source'] == 'html5') {
			$result->urlMp4Video =  wp_get_attachment_url($result->video['source_video_id']);
		}
		$result->urlPoster = '';
		if($result->video['poster']) {
			$result->urlPoster =  wp_get_attachment_url($result->video['poster']);
		}
		$result->attachments = tutor_utils()->get_attachments($post->ID);
		$result->is_preview = get_post_meta($post->ID, '_is_preview', true);
		$object->lession = $result;
		return $object;

	}


	public function save_coure_preview_meta($post_ID,$request){
		$_is_preview = $request['_is_preview'];
		if ($_is_preview){
			update_post_meta($post_ID, '_is_preview', 1);
		}else{
			delete_post_meta($post_ID, '_is_preview');
		}
	}

	public function save_coure_attachments_meta($post_ID,$request){
		//Attachments
		$attachments = array();
		if ( ! empty($request['tutor_attachments'])){
			$attachments = tutor_utils()->sanitize_array($_POST['tutor_attachments']);
			$attachments = array_unique($attachments);
		}
		update_post_meta($post_ID, '_tutor_attachments', $attachments);
	}

	/**
	 * Update assignment
	 */
	public function tutor_modal_create_or_update_assignment($request,$user_id) {
		global $wpdb;
		$assignment_id     = (int)$request['assignment_id'];
		$topic_id          = (int)$request['topic_id'];
		$title             =  $request['assignment_title'];
		$lesson_content    = wp_kses_post($request['assignment_content']);



		$assignment_data   = array(
			'post_type'                   => 'tutor_assignments',
			'post_status'                   => 'publish',
			'post_author'                   => $user_id ,
			'post_parent'                   => $topic_id,
			'post_title'                   => $title,
			'post_name'                   => sanitize_title($title) ,
			'post_content'                   => $lesson_content
		);

		$object = new \stdClass();

		if ($assignment_id == 0) {

			$assignment_id     = wp_insert_post($assignment_data);

			if ($assignment_id) {
				$course_id         = $wpdb->get_var($wpdb->prepare("SELECT post_parent FROM {$wpdb->posts} WHERE ID=%d", $topic_id));
				update_post_meta($assignment_id, '_tutor_course_id_for_assignments', $course_id);
			} else {
				$object->type =0;
				$object->msg =__('Couldn\'t create assignment');

			}
		} else {
			$assignment_data['ID'] = $assignment_id;

			wp_update_post($assignment_data);

		}

		update_post_meta($assignment_id, 'assignment_option', $this->convertAssignemntOption($request));

		$this->saveAttachmentsForAssignment($assignment_id,$request);
		$object->type =1;
		$object->msg ='';
		$object->assignmentId = $assignment_id;
		return $object;

	}

	public function convertAssignemntOption($request){
		$array = [
			'time_duration'=>array(
				'value' => $request['value'],
				'time' => $request['time']
			),
			'total_mark' => $request['total_mark'],
			'pass_mark' => $request['pass_mark'],
			'upload_files_limit' => $request['upload_files_limit'],
			'upload_file_size_limit' => $request['upload_file_size_limit'],
		];

		return $array;

	}

	public function saveAttachmentsForAssignment($post_id,$request) {
		$attachment_arr =  explode (',',$request['tutor_assignment_attachments']);;
		if (tutor_utils()->count($attachment_arr)) {
			update_post_meta($post_id, '_tutor_assignment_attachments',$attachment_arr );
		} else {
			delete_post_meta($post_id, '_tutor_assignment_attachments');
		}

	}


	public function tutor_load_assignments_builder_modal($request,$user_id) {


		$assignment_id     = (int)$request['assignment_id'];
		$topic_id          = (int)$request['topic_id'];
		/**
		 * If Assignment Not Exists, provide dummy
		 */
		$post_arr = array(
			'ID'           => 0,
			'post_type'    => 'tutor_assignments',
			'post_title'   => __('Assignments', 'tutor-pro') ,
			'post_content' => '',
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id() ,
			'post_parent'  => $topic_id,
		);

		$post          = $assignment_id ? get_post($assignment_id) : (object)$post_arr;
		$object = new \stdClass();
		$object->type = 1;
		$result = new \stdClass();
		$result->title = wp_strip_all_tags($post->post_title);
		$result->content = wp_strip_all_tags($post->post_content);

		$assignment_arr = array();
		$assignment_attachments = get_post_meta($post->ID,'_tutor_assignment_attachments', true);
		if (tutor_utils()->count($assignment_attachments)){
			foreach ($assignment_attachments as $assignment_attachment){
				if ($assignment_attachment) {
					$object_assginments = new \stdClass();
					$object_assginments->id = $assignment_attachment;
					$attachment_name =  get_post_meta( $assignment_attachment, '_wp_attached_file', true );
					$attachment_name = substr($attachment_name, strrpos($attachment_name, '/')+1);
					$object_assginments->name = $attachment_name;
					$object_assginments->url = wp_get_attachment_url($assignment_attachment);
					$assignment_arr[] = $object_assginments;

				}
			}
		}
		$result->attachments = $assignment_arr;
		$result->options = tutor_utils()->get_assignment_option($assignment_id);
		$object->assignments =$result;
		return $object;

	}



	/**
	 * New Design Quiz
	 */
	public function tutor_create_quiz_and_load_modal($request,$user_id){


		$topic_id           = $request['topic_id'];
		$quiz_title         = $request['quiz_title'];
		$quiz_description   = $request['quiz_description'];
		$next_order_id      = tutor_utils()->get_next_course_content_order_id($topic_id);
		$object = new \stdClass();
		if(!tutils()->can_user_manage('topic', $topic_id,$user_id)) {
			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
			return $object;

		}

		$post_arr = array(
			'post_type'     => 'tutor_quiz',
			'post_title'    => $quiz_title,
			'post_content'  => $quiz_description,
			'post_status'   => 'publish',
			'post_author'   => get_current_user_id(),
			'post_parent'   => $topic_id,
			'menu_order'    => $next_order_id,
		);
		$quiz_id = wp_insert_post( $post_arr );
		$object->type =1;
		$object->msg ='';
		$object->quizz_id =$quiz_id;
		return $object;
	}

	public function get_quizz($request,$user_id) {
			$quizz = get_post($request['quizz_id']);
		$object = new \stdClass();
		if($quizz){
			$object->type =1;
			$object->msg ='';
			$object->title = wp_strip_all_tags($quizz->post_title);
			$object->desc = wp_strip_all_tags($quizz->post_content);;
			$object->id =$quizz->ID;
		}
		else {
			$object->type =0;
			$object->msg =__('Get quizz failed', 'tutor');

		}
		return $object;
	}


	/**
	 * Update Quiz from quiz builder modal
	 *
	 * @since v.1.0.0
	 */
	public function tutor_quiz_builder_quiz_update($request,$user_id){

		$quiz_id         	= $request['quiz_id'] ;
		$quiz_title         = $request['quiz_title'];
		$quiz_description   = $request['quiz_description'];
		$object = new \stdClass();
		if(!tutils()->can_user_manage('quiz', $quiz_id,$user_id)) {

			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
			return $object;
		}

		$post_arr = array(
			'ID'    => $quiz_id,
			'post_title'    => $quiz_title,
			'post_content'  => $quiz_description,

		);
		$quiz_id = wp_update_post( $post_arr );
		if($quiz_id){
			$object->type =1;
			$object->msg =__('Update Quizz Succefully', 'tutor');
		}
		else {
			$object->type =0;
			$object->msg =__('Update quizz failed', 'tutor');
		}
		return $object;

	}


	public function tutor_quiz_builder_get_question_form($request,$user_id){

		global $wpdb;
		$quiz_id = $request['quiz_id'];
		$question_id = $request['question_id'];

		$object = new \stdClass();
		if(!tutils()->can_user_manage('quiz', $quiz_id,$user_id)) {
			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
			return $object;
		}

		if ( ! $question_id){
			$next_question_id = tutor_utils()->quiz_next_question_id();
			$next_question_order = tutor_utils()->quiz_next_question_order_id($quiz_id);

			$new_question_data = array(
				'quiz_id'               => $quiz_id,
				'question_title'        => __('Question', 'tutor').' '.$next_question_id,
				'question_description'  => '',
				'question_type'         => 'true_false',
				'question_mark'         => 1,
				'question_settings'     => maybe_serialize(array()),
				'question_order'        => esc_sql( $next_question_order ) ,
			);

			$wpdb->insert($wpdb->prefix.'tutor_quiz_questions', $new_question_data);
			$question_id = $wpdb->insert_id;
		}

		$question = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tutor_quiz_questions where question_id = %d ", $question_id));

		$object->question =$question;
		$object->settings = maybe_unserialize($question->question_settings);
		$object->question_title = wp_strip_all_tags($question->question_title);
		$object->question_description = wp_strip_all_tags($question->question_description);
		$object->question_types = Edumall_Mobile_Utils::get_question_types();
		$object->has_tutor_pro = tutor()->has_pro;
		//$object->answer_required = checked('1', tutor_utils()->avalue_dot('answer_required', $object->settings));
		//$object->randomize_question = checked('1', tutor_utils()->avalue_dot('randomize_question', $object->settings));
		//$object->show_question_mark = checked('1', tutor_utils()->avalue_dot('show_question_mark', $object->settings));
		$object->hint_desc = '';

		switch ($question->question_type){
			case 'true_false':
				$object->hint_desc = __('Input options for the question and select the correct answer.', 'tutor');
				break;
			case 'ordering':
				$object->hint_desc = __('Make sure you’re saving the answers in the right order. Students will have to match this order.', 'tutor');
				break;
		}

		$answers = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers where belongs_question_id = %d AND belongs_question_type = %s order by answer_order asc ;", $question_id, $question->question_type));

		if (is_array($answers) && count($answers)) {
			foreach ( $answers as $answer ) {
				$answer->answer_title = wp_strip_all_tags( $answer->answer_title );
				if ( $answer->belongs_question_type === 'fill_in_the_blank' ) {
					$answer->answer_two_gap_match = ' (' . __( 'Answer', 'tutor' ) . ' : ' . wp_strip_all_tags( $answer->answer_two_gap_match );
				}
				if ( $answer->belongs_question_type === 'matching' ) {
					$answer->answer_two_gap_match = ' - ' . stripslashes( $answer->answer_two_gap_match );

				}

				if ( $answer->image_id ) {
					wp_get_attachment_image_url( $answer->image_id );
				}
			}

			$object->answers = $answers;

		}

		$static_arr                         = [];
		$static_arr['back']                 = __( 'Back', 'tutor' );
		$static_arr['write_your_question']  = __( 'Write your question here', 'tutor' );
		$static_arr['type_your_quiz']       = __( 'Type your quiz title here', 'tutor' );
		$static_arr['question_type']        = __( 'Question Type', 'tutor' );
		$static_arr['true_or_false']        = __( 'True or False', 'tutor' );
		$static_arr['point_for_answer']     = __( 'Point(s) for this answer', 'tutor' );
		$static_arr['set_the_mark']         = __( 'set the mark ex. 10', 'tutor' );
		$static_arr['display']              = __( 'Display Points', 'tutor' );
		$static_arr['description']          = __( 'Description', 'tutor' );
		$static_arr['optional']             = __( 'Optional', 'tutor' );
		$static_arr['pro_version_required'] = __( 'Pro version required', 'tutor' );

		$object->static = $static_arr;
		$object->type   = 1;
		$object->msg    = '';


		return $object;
	}

	public function	tutor_load_edit_quiz_modal($request,$user_id) {
		$quiz_id = $request['quiz_id'];

		$quiz = get_post($quiz_id);
		$object = new \stdClass();

		if (!$quiz) {
			$object->type =0;
			$object->msg =__('No quiz found', 'tutor');
			return $object;

		}

		$static_arr = [];

			$static_arr['quiz_inof'] = __('Quiz Info', 'tutor');
			$static_arr['questions'] = __('Questions', 'tutor');
			$static_arr['settings'] = __('Settings', 'tutor');
			$static_arr['advanded_options'] = __('Advanced Options', 'tutor');
			$static_arr['type_your_quiz_here'] = __('Type your quiz title here', 'tutor');
			$static_arr['save_next'] = __('Save &amp; Next', 'tutor');
			$static_arr['cancel'] = __('Cancel', 'tutor');
			$static_arr['add_question'] = __('Add Question', 'tutor');
			$static_arr['back'] = __('Back', 'tutor');
			$static_arr['next'] =__('Next', 'tutor');
			$static_arr['cancel'] = __('Cancel', 'tutor');
			$static_arr['time_limit'] = __('Time Limit', 'tutor');
			$static_arr['secondes'] =__('Seconds', 'tutor');
			$static_arr['minutes'] = __('Minutes', 'tutor');
			$static_arr['hours'] = __('Hours', 'tutor');
			$static_arr['days'] = __('Days', 'tutor');
			$static_arr['weeks'] = __('Weeks', 'tutor');
			$static_arr['hide_quiz_time'] = __('Hide quiz time - display', 'tutor');
			$static_arr['quiz_feedback_mode'] = __('Quiz Feedback Mode', 'tutor');
			$static_arr['default'] = __('Default', 'tutor');
			$static_arr['anser_show_after_quiz'] = __('Answers shown after quiz is finished', 'tutor');
			$static_arr['retry_mode'] = __('Retry Mode', 'tutor');
			$static_arr['unlimited_attemps_on_each_question'] = __('Unlimited attempts on each question.', 'tutor');
			$static_arr['live_demo'] = __('Live Demo', 'tutor');
			$static_arr['reveal_mode'] = __('Reveal Mode', 'tutor');
			$static_arr['show_resutl_after_the_attempt'] = __('Show result after the attempt.', 'tutor');
			$static_arr['live_demo'] = __('Live Demo', 'tutor');
			$static_arr['attemps_allowed'] = __('Attempts Allowed', 'tutor');
			$static_arr['optional'] = __('Optional', 'tutor');
			$static_arr['restriction_on_the_number'] = __('Restriction on the number of attempts a student is allowed to take for this quiz. 0 for no limit', 'tutor');
			$static_arr['passing_grade'] = __('Passing Grade (%)', 'tutor');
			$static_arr['set_the_passing_percentage'] = __('Set the passing percentage for this quiz', 'tutor');
			$static_arr['max_question_allowd'] = __('Max questions allowed to answer', 'tutor');
			$static_arr['this_amount_of_question'] = __('This amount of question will be available for students to answer, and question will comes randomly from all available questions belongs with a quiz, if this amount greater than available question, then all questions will be available for a student to answer.', 'tutor');
			$static_arr['back'] = __('Back', 'tutor');
			$static_arr['error'] = __('Error', 'tutor');
			$static_arr['action_failed'] = __('Action Failed', 'tutor');
			$static_arr['success'] = __('Success', 'tutor');
			$static_arr['saved'] = __('Saved', 'tutor');
			$static_arr['save'] = __('Save', 'tutor');
			$static_arr['quiz_auto_start']=__('Quiz Auto Start', 'tutor');
			$static_arr['if_you_enable_this_option'] = __('If you enable this option, the quiz will start automatically after the page is loaded.', 'tutor');
			$static_arr['question_layout'] = __('Question Layout', 'tutor');
			$static_arr['set_question_layout_view'] = __('Set question layout view', 'tutor');
			$static_arr['single_question'] = __('Single Question', 'tutor');
			$static_arr['question_pagination'] = __('Question Pagination', 'tutor');
			$static_arr['question_below_each_other']=__('Question below each other', 'tutor');
			$static_arr['question_order'] = __('Questions Order', 'tutor');
			$static_arr['random'] = __('Random', 'tutor');
			$static_arr['sorting'] = __('Sorting', 'tutor');
			$static_arr['ascending'] = __('Ascending', 'tutor');
			$static_arr['decending'] = __('Descending', 'tutor');
			$static_arr['hide_question_number'] = __('Hide question number', 'tutor');
			$static_arr['show_hide_question_number'] =__('Show/hide question number during attempt.', 'tutor');
			$static_arr['short_answer_characters_limit'] = __('Short answer characters limit', 'tutor');
			$static_arr['studen_will_place_the_short_answer'] = __('Student will place answer in short answer question type within this characters limit.', 'tutor');
			$static_arr['open_ended_essay_question'] = __('Open-Ended/Essay questions answer character limit', 'tutor');
			$static_arr['studen_will_place_the_open_essay'] = __('Students will place the answer in the Open-Ended/Essay question type within this character limit.', 'tutor');
			$static_arr['back'] =__('Back', 'tutor');
			$static_arr['error'] =__('Error', 'tutor');
			$static_arr['knowledge_base_link'] =  "https://docs.themeum.com/tutor-lms/". __("Knowledge Base", "tutor");
			$static_arr['documentation_link'] =  "https://docs.themeum.com/tutor-lms/". __("Documentation", "tutor");
			$object->static_arr = $static_arr;

			$quiz->post_title  = wp_strip_all_tags($quiz->post_title);
			$quiz->post_content = wp_strip_all_tags($quiz->post_content);
			$object->quiz = $quiz;
			$quesion_arr = tutor_utils()->get_questions_by_quiz($quiz_id);
			if($quesion_arr){
				$object->questions = $quesion_arr;
			}
			else {
				$object->questions = [];
			}

		    $object->time_limit_time_value=tutor_utils()->get_quiz_option($quiz_id, 'time_limit.time_value', 0);
            $object->time_limit_time_type = tutor_utils()->get_quiz_option($quiz_id, 'time_limit.time_type', 'minutes');
            $object->hide_quiz_time_display =tutor_utils()->get_quiz_option($quiz_id, 'hide_quiz_time_display');
            $object->feedback_mode = tutor_utils()->get_quiz_option($quiz_id, 'feedback_mode');
            $object->quiz_attempts_allowed = tutor_utils()->get_option('quiz_attempts_allowed');
            $object->attempts_allowed = (int) tutor_utils()->get_quiz_option($quiz_id, 'attempts_allowed',  $object->quiz_attempts_allowed);
			$object->passing_grade =tutor_utils()->get_quiz_option($quiz_id, 'passing_grade', 80);
			$object->max_questions_for_answer =tutor_utils()->get_quiz_option($quiz_id, 'max_questions_for_answer', 10);

			$object->quiz_auto_start =tutor_utils()->get_quiz_option($quiz_id, 'quiz_auto_start');
			$object->question_layout_view =tutor_utils()->get_quiz_option($quiz_id, 'question_layout_view');
			$object->questions_order =tutils()->get_quiz_option($quiz_id, 'questions_order');
			$object->hide_question_number_overview =tutor_utils()->get_quiz_option($quiz_id, 'hide_question_number_overview');
			$object->short_answer_characters_limit =tutor_utils()->get_quiz_option($quiz_id, 'short_answer_characters_limit', 200);
			$object->open_ended_answer_characters_limit =tutor_utils()->get_quiz_option($quiz_id, 'open_ended_answer_characters_limit', 500);

			$object->type =1;
			$object->msg =__('Success', 'tutor');
			return $object;


	}

	/**
	 * Get answers options form for quiz question
	 *
	 * @since v.1.0.0
	 */
	public function tutor_quiz_add_question_answers($request,$user_id){


		$question_id = $request['question_id'];
		$question = $request['tutor_quiz_question'];
		$question_type = $question['question_type'];

		$object = new \stdClass();

		if(!tutils()->can_user_manage('question', $question_id,$user_id)) {
			$object->type =0;
			$object->msg =__('No quiz found', 'tutor');
			return $object;
		}

		$static_arr = [];
		$static_arr->select_the_correct_option = __('Select the correct option', 'tutor');
		$static_arr->true = __('True', 'tutor');
		$static_arr->false = __('False', 'tutor');
		$static_arr->answer_title = __('Answer title', 'tutor');
		$static_arr->upload_image = __('Upload Image', 'tutor');
		$static_arr->display_format_for_options = __('Display format for options', 'tutor');
		$static_arr->only_text = __('Only text', 'tutor');
		$static_arr->only_image = __('Only Image', 'tutor');
		$static_arr->text_and_image = __('Text &amp; Image both', 'tutor');
		$static_arr->question_title = __('Question Title', 'tutor');
		$static_arr->please_make_sure_dash = __( 'Please make sure to use the {dash} variable in your question title to show the blanks in your question. You can use multiple {dash} variables in one question.', 'tutor' );
		$static_arr->no_option_is_necessary = __('No option is necessary for this answer type', 'tutor');
		$static_arr->correct_answer = __('Correct Answer(s)', 'tutor');
		$static_arr->answer_title = __('Answer title', 'tutor');
		$static_arr->sepereate_multiple_answers = __( 'Separate multiple answers by a vertical bar |. 1 answer per {dash} variable is defined in the question. Example: Apple | Banana | Orange', 'tutor' );
		$static_arr->matched_answer_title = __('Matched Answer title', 'tutor');
		$static_arr->image_match_text = __('Image matched text', 'tutor');
	    $static_arr->answer_input_value =  __('Answer input value', 'tutor');
	    $static_arr->the_answers_the_students_enter = __('The answers that students enter should match with this text. Write in small caps','tutor');
	    $static_arr->save_answer = __('Save Answer', 'tutor');
	    $object->static_arr =$static_arr;
		$object->type =1;

	    return $object;


	}


	public function tutor_save_quiz_answer_options($request, $user_id){

		global $wpdb;

		$questions = $request['tutor_quiz_question'];
		$answers = $request['quiz_answer'];
		$object = new \stdClass();

		foreach ($answers as $question_id => $answer){

			if(!tutils()->can_user_manage('question', $question_id,$user_id)) {

				continue;
			}


			$question = tutor_utils()->avalue_dot($question_id, $questions);


			$question_type = $question['question_type'];


			$request['question_id'] = $question_id;
			$request['question_type'] =$question_type;

			//Getting next sorting order
			$next_order_id = (int) $wpdb->get_var($wpdb->prepare(
				"SELECT MAX(answer_order) 
				FROM {$wpdb->prefix}tutor_quiz_question_answers 
				where belongs_question_id = %d 
				AND belongs_question_type = %s ", $question_id, esc_sql( $question_type )));

			$next_order_id = $next_order_id + 1;

			if ($question){
				if ($question_type === 'true_false'){
					$wpdb->delete($wpdb->prefix.'tutor_quiz_question_answers', array('belongs_question_id' => $question_id, 'belongs_question_type' => $question_type));
					$data_true_false = array(
						array(
							'belongs_question_id'   => esc_sql( $question_id ) ,
							'belongs_question_type' => $question_type,
							'answer_title'          => __('True', 'tutor'),
							'is_correct'            => $answer['true_false'] == 'true' ? 1 : 0,
							'answer_two_gap_match'  => 'true',
						),
						array(
							'belongs_question_id'   => esc_sql( $question_id ) ,
							'belongs_question_type' => $question_type,
							'answer_title'          => __('False', 'tutor'),
							'is_correct'            => $answer['true_false'] == 'false' ? 1 : 0,
							'answer_two_gap_match'  => 'false',
						),
					);

					foreach ($data_true_false as $true_false_data){
						$wpdb->insert($wpdb->prefix.'tutor_quiz_question_answers', $true_false_data);
					}

				}elseif($question_type === 'multiple_choice' || $question_type === 'single_choice' || $question_type === 'ordering' ||
				        $question_type === 'matching' || $question_type === 'image_matching' || $question_type === 'image_answering'  ){

					$answer_data = array(
						'belongs_question_id'   => sanitize_text_field( $question_id ),
						'belongs_question_type' => $question_type,
						'answer_title'          => sanitize_text_field( $answer['answer_title'] ),
						'image_id'              => isset($answer['image_id']) ? $answer['image_id'] : 0,
						'answer_view_format'    => isset($answer['answer_view_format']) ? $answer['answer_view_format'] : 0,
						'answer_order'          => $next_order_id,
					);
					if (isset($answer['matched_answer_title'])){
						$answer_data['answer_two_gap_match'] = sanitize_text_field( $answer['matched_answer_title'] );
					}

					$wpdb->insert($wpdb->prefix.'tutor_quiz_question_answers', $answer_data);

				}elseif($question_type === 'fill_in_the_blank'){
					$wpdb->delete($wpdb->prefix.'tutor_quiz_question_answers', array('belongs_question_id' => $question_id, 'belongs_question_type' => $question_type));
					$answer_data = array(
						'belongs_question_id'   => sanitize_text_field( $question_id ) ,
						'belongs_question_type' => $question_type,
						'answer_title'          => sanitize_text_field( $answer['answer_title'] ),
						'answer_two_gap_match'  => isset($answer['answer_two_gap_match']) ? sanitize_text_field( trim($answer['answer_two_gap_match']) ) : null,
					);
					$wpdb->insert($wpdb->prefix.'tutor_quiz_question_answers', $answer_data);
				}
			}
		}

		$object->type =1;
		$object->msg ='';
		$object->answer = $this->tutor_quiz_builder_get_answers_by_question($request,$user_id);

		return $object;


	}


	public function tutor_quiz_builder_get_answers_by_question($request,$user_id){

		global $wpdb;
		$question_id = sanitize_text_field($request['question_id']);
		$question_type = sanitize_text_field($request['question_type']);
		$object = new \stdClass();
		if(!tutils()->can_user_manage('question', $question_id,$user_id)) {

			$object->type =0;
			$object->user_id =$user_id;
			$object->msg =__('Access Denied', 'tutor');
			return $object;
		}

		$question = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tutor_quiz_questions WHERE question_id = %d ", $question_id));
		$answers = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers where belongs_question_id = %d AND belongs_question_type = %s order by answer_order asc ;", $question_id, esc_sql( $question_type ) ));
		$object->notice = '';

		switch ($question_type){
			case 'true_false':
				$object->notice =__('Answer options &amp; mark correct', 'tutor');
				break;
			case 'ordering':
				$object->notice = __('Make sure you’re saving the answers in the right order. Students will have to match this order exactly.', 'tutor');
				break;
		}

		if (is_array($answers) && count($answers)){
			foreach ($answers as $answer){
				$answer->answer_title = wp_strip_all_tags($answer->answer_title);
                            if ($answer->belongs_question_type === 'fill_in_the_blank'){
	                            $answer->message = __('Answer', 'tutor').' : ' .wp_strip_all_tags($answer->answer_two_gap_match);
                            }
                            if ($answer->belongs_question_type === 'matching'){
	                            $answer->message = ' - '.wp_strip_all_tags($answer->answer_two_gap_match);
                            }

						if ($answer->image_id){
							$answer->image_url = wp_get_attachment_image_url($answer->image_id);
						}
						if ($question_type === 'true_false' || $question_type === 'single_choice'){
						}elseif ($question_type === 'multiple_choice'){
						}

				$answer->isNetwork = false;
				if(isset($answer->image_id) && $answer->image_id!='0') {

					$answer->isNetwork = true;
				}
			}
		}
		$object->quesions = $question;
		if($answers) {
			$object->answers = $answers;
		}
		else {
			$object->answers = [];
		}
		$object->type =1;
		$object->msg ='';
		return $object;

	}

	public function tutor_mark_answer_as_correct($request,$user_id){


		global $wpdb;

		$answer_id = sanitize_text_field($request['answer_id']);
		$inputValue = sanitize_text_field($request['inputValue']);
		$object = new \stdClass();
		if(!tutils()->can_user_manage('quiz_answer', $answer_id,$user_id)) {
			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
			return $object;

		}

		$answer = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE answer_id = %d LIMIT 0,1 ;", $answer_id));
		if ($answer->belongs_question_type === 'single_choice') {
			$wpdb->update($wpdb->prefix.'tutor_quiz_question_answers', array('is_correct' => 0), array('belongs_question_id' => esc_sql( $answer->belongs_question_id ) ));
		}
		$wpdb->update($wpdb->prefix.'tutor_quiz_question_answers', array('is_correct' => esc_sql( $inputValue ) ), array('answer_id' => esc_sql( $answer_id ) ));
		$object->type =1;
		$object->msg ='';
		return $object;

	}

	/**
	 * Update quiz settings from modal
	 *
	 * @since : v.1.0.0
	 */
	public function tutor_quiz_modal_update_settings($request,$user_id){


		$quiz_id = sanitize_text_field($request['quiz_id']);
		$quiz_option = tutor_utils()->sanitize_array($request['quiz_option']);
		$object = new \stdClass();
		if(!tutils()->can_user_manage('quiz', $quiz_id,$user_id)) {
			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
			return $object;
		}

		update_post_meta($quiz_id, 'tutor_quiz_option', $quiz_option);
		$object->type =1;
		$object->msg ='';
		return $object;

	}

	public function tutor_quiz_builder_question_delete($request,$user_id){


		global $wpdb;

		$question_id =$request['question_id'];
		$object = new \stdClass();
		if(!tutils()->can_user_manage('question', $question_id,$user_id)) {
			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
			return $object;
		}

		if ($question_id){
			$wpdb->delete($wpdb->prefix.'tutor_quiz_questions', array('question_id' => esc_sql( $question_id ) ));
		}

		$object->type =1;
		$object->msg ='';
		return $object;
	}

	public function tutor_quiz_modal_update_question($request,$user_id){
		$object = new \stdClass();
		global $wpdb;

		$question_data = $request['tutor_quiz_question'];

		foreach ($question_data as $question_id => $question) {

			if(!tutils()->can_user_manage('question', $question_id,$user_id)) {
				continue;
			}

			$question_title         = sanitize_text_field($question['question_title']);
			$question_description   = wp_kses( $question['question_description'], $this->allowed_html ); // sanitize_text_field($question['question_description']);
			$question_type          = sanitize_text_field($question['question_type']);
			$question_mark          = sanitize_text_field($question['question_mark']);

			unset($question['question_title']);
			unset($question['question_description']);

			$data = array(
				'question_title'        => $question_title,
				'question_description'  => $question_description,
				'question_type'         => $question_type,
				'question_mark'         => $question_mark,
				'question_settings'     => maybe_serialize($question),
			);

			$result = $wpdb->update($wpdb->prefix.'tutor_quiz_questions', $data, array('question_id' => $question_id) );

			if($request['isValidate']){
				/**
				 * Validation
				 */
				if ($question_type === 'true_false' || $question_type === 'single_choice'){
					$question_options = tutils()->get_answers_by_quiz_question($question_id);
					if (tutils()->count($question_options)){
						$required_validate = true;
						foreach ($question_options as $question_option){
							if ($question_option->is_correct){
								$required_validate = false;
							}
						}
						if ($required_validate){
							$validation_msg = __('Please select the correct answer', 'tutor');

						}
					}else{
						$validation_msg = __('Please make sure you have added more than one option and saved them', 'tutor');

					}
				}
			}
		}
		if($result) {
			$object->type = 1;
			$object->msg  = '';
		}
		else {
			$object->type = 0;
			$object->msg  = '';
		}
		return $object;


	}


	/**
	 * Save sorting data for quiz answers
	 */
	public function tutor_quiz_answer_sorting($request,$user_id){

		global $wpdb;
		$object = new \stdClass();
		if ( ! empty($request['sorted_answer_ids']) && is_array($request['sorted_answer_ids']) && count($request['sorted_answer_ids']) ){
			$answer_ids = $request['sorted_answer_ids'];
			$i = 0;
			foreach ($answer_ids as $key => $answer_id){
				if(tutils()->can_user_manage('quiz_answer', $answer_id,$user_id)) {
					$i++;
					$wpdb->update($wpdb->prefix.'tutor_quiz_question_answers', array('answer_order' => $i), array('answer_id' => $answer_id));
				}
			}

			$object->type =1;
			$object->msg =__('Sorting Successfully', 'tutor');
		}
		else{
			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
		}



		return $object;



	}


	/**
	 * Tutor Update Answer
	 *
	 * @since v.1.0.0
	 */
	public function tutor_update_quiz_answer_options($request,$user_id){

		global $wpdb;
		$answer_id = (int) sanitize_text_field($request['tutor_quiz_answer_id']);

		$object = new \stdClass();
		if(!tutils()->can_user_manage('quiz_answer', $answer_id,$user_id)) {
			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
			return $object;
		}

		$questions = $request['tutor_quiz_question'];
		$answers = $request['quiz_answer'];

		foreach ($answers as $question_id => $answer){
			$question = tutor_utils()->avalue_dot($question_id, $questions);
			$question_type = $question['question_type'];
			$request['question_id'] = $question_id;
			$request['question_type'] =$question_type;
			if ($question){
				if($question_type === 'multiple_choice' || $question_type === 'single_choice' || $question_type === 'ordering' || $question_type === 'matching' || $question_type === 'image_matching' || $question_type === 'fill_in_the_blank' || $question_type === 'image_answering'  ){

					$answer_data = array(
						'belongs_question_id'   => $question_id,
						'belongs_question_type' => $question_type,
						'answer_title'          => sanitize_text_field( $answer['answer_title'] ) ,
						'image_id'              => isset($answer['image_id']) ? $answer['image_id'] : 0,
						'answer_view_format'    => isset($answer['answer_view_format']) ? sanitize_text_field( $answer['answer_view_format'] )  : '',
					);
					if (isset($answer['matched_answer_title'])){
						$answer_data['answer_two_gap_match'] = sanitize_text_field( $answer['matched_answer_title'] ) ;
					}

					if ($question_type === 'fill_in_the_blank'){
						$answer_data['answer_two_gap_match'] = isset($answer['answer_two_gap_match']) ? sanitize_text_field(trim($answer['answer_two_gap_match'])) : null;
					}

					$wpdb->update($wpdb->prefix.'tutor_quiz_question_answers', $answer_data, array('answer_id' => $answer_id));
				}
			}
		}

		$object->type =1;
		$object->msg =__('Success', 'tutor');
		$object->answer = $this->tutor_quiz_builder_get_answers_by_question($request,$user_id);

		return $object;
	}


	public function tutor_quiz_edit_question_answer($request,$user_id){


		$answer_id = (int) sanitize_text_field($request['answer_id']);
		$object = new \stdClass();
		if(!tutils()->can_user_manage('quiz_answer', $answer_id,$user_id)) {
			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
			return $object;
		}

		$old_answer = tutor_utils()->get_answer_by_id($answer_id);
		$object->answer=$old_answer;

		if (is_array($old_answer) && count($old_answer)){
			foreach ($old_answer as $answer){
				$answer->answer_title = wp_strip_all_tags($answer->answer_title);
				if ($answer->belongs_question_type === 'fill_in_the_blank'){
					$answer->message = __('Answer', 'tutor').' : ' .wp_strip_all_tags($answer->answer_two_gap_match);
				}
				if ($answer->belongs_question_type === 'matching'){
					$answer->message = ' - '.wp_strip_all_tags($answer->answer_two_gap_match);
				}

				if ($answer->image_id){
					$answer->image_url = wp_get_attachment_image_url($answer->image_id);
				}


				$answer->isNetwork = false;
				if(isset($answer->image_id) && $answer->image_id!='0') {

					$answer->isNetwork = true;
				}
			}
		}
		$object->type =1;
		$object->msg ='';

		return $object;

	}

	public function tutor_quiz_builder_delete_answer($request,$user_id){
		global $wpdb;
		$answer_id = sanitize_text_field($request['answer_id']);
		$object = new \stdClass();
		if(!tutils()->can_user_manage('quiz_answer', $answer_id,$user_id)) {
			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
			return $object;
		}

		$result = $wpdb->delete($wpdb->prefix.'tutor_quiz_question_answers', array('answer_id' => esc_sql( $answer_id ) ));
		if($result) {
			$object->type = 1;
			$object->msg  = '';
		}
		else {
			$object->type = 0;
			$object->msg  = '';
		}

		return $object;
	}


	public function detach_instructor_from_course($request,$user_id){


		global $wpdb;

		$instructor_id = (int) sanitize_text_field($request['instructor_id']);
		$course_id = (int) sanitize_text_field($request['course_id']);
		$object = new \stdClass();
		if(!tutils()->can_user_manage('course', $course_id,$user_id)) {
			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
			return $object;

		}
		if($instructor_id != $user_id) {
			$result = $wpdb->delete( $wpdb->usermeta, array( 'user_id'    => $instructor_id,
			                                                 'meta_key'   => '_tutor_instructor_course_id',
			                                                 'meta_value' => $course_id
			) );
			if ( $result ) {
				$object->type = 1;
				$object->msg  = '';
			} else {
				$object->type = 0;
				$object->msg  = '';
			}
		}
		else {
			$object->type = 0;
			$object->msg  = '';
		}

		return $object;
	}

	public function tutor_add_instructors_to_course($request,$user_id){


		$course_id = (int) sanitize_text_field($request['course_id']);


		$instructor_ids = explode(',',$request['tutor_instructor_ids']);

		$object = new \stdClass();
		if(!tutils()->can_user_manage('course', $course_id,$user_id)) {
			$object->type =0;
			$object->msg =__('Access Denied', 'tutor');
			return $object;
		}

		if (is_array($instructor_ids) && count($instructor_ids)){

			foreach ($instructor_ids as $instructor_id){
				add_user_meta($instructor_id, '_tutor_instructor_course_id', $course_id);
			}
		}

		$saved_instructors = tutor_utils()->get_instructors_by_course($course_id);


		if ($saved_instructors){
			foreach ($saved_instructors as $t){
				$t->avatar =  get_avatar_url( $t->ID, array( 'size' => 64 ) );


			}
		}

		$object->type =1;
		$object->save_instructor =$saved_instructors;
		return $object;


	}



}