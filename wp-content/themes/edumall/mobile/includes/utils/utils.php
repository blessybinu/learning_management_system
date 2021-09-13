<?php

namespace edumallmobile\utils;
use edumallmobile\framework\Edumall_Tutor_Shortcode;


class Edumall_Mobile_Utils {


	public static function get_respone( $data, $status ) {
		$response = new \WP_REST_Response( $data );

		$response->set_status( $status );

		return $response;
	}

	public static function is_time_in_range( $date1 ) {
		$date1 = new \DateTime( $date1 );
		$unix1 = strtotime( $date1->format( 'Y-m-d H:i:s' ) );
		$date2 = new \DateTime( current_time( 'Y-m-d H:i:s' ) );
		$unix2 = strtotime( $date2->format( 'Y-m-d H:i:s' ) );
		$range = $unix1 - $unix2;

		if ( $range > 0 ) {
			return true;
		}

		return false;
	}

	public static function edumall_mobile_get_user() {
		$result = get_users( array(
			'meta_key'   => 'mobile_token',
			'meta_value' => substr( $_SERVER['HTTP_AUTHORIZATION'], 7 ),
		) );

		return $result[0];
	}

	public static function is_user_login() : bool {
		$result = get_users( array(
			'meta_key'   => 'mobile_token',
			'meta_value' => substr( $_SERVER['HTTP_AUTHORIZATION'], 7 ),
		) );

		if ( count( $result ) == 1 ) {
			return true;
		} else {
			return false;
		}
	}

	public static function role_user() : int {
		$result = get_users( array(
			'meta_key'   => 'mobile_token',
			'meta_value' => substr( $_SERVER['HTTP_AUTHORIZATION'], 7 ),
		) );

		if ( count( $result ) == 1 ) {
			$register_time = get_user_meta( $result[0]->ID, '_is_tutor_instructor', true );

			if ( empty( $register_time ) ) {
				return 1;
			}

			$instructor_status = get_user_meta( $result[0]->ID, '_tutor_instructor_status', true );

			if ( 'approved' !== $instructor_status ) {
				return 1;
			}

			return 2;
		} else {
			return 0;
		}
	}

	public static function get_level_label( $post_id ) {
		$level = Edumall_Mobile_Utils::get_level( $post_id );

		if ( $level ) {
			return Edumall_Mobile_Utils::course_levels( $level );
		}

		return 0;
	}

	public static function get_level( $post_id ) {
		return get_post_meta( $post_id, '_tutor_course_level', true );

	}


	public static function course_levels( $level = null ) {
		$levels = apply_filters( 'tutor_course_level', array(
			'all_levels'   => 3,
			'beginner'     => 0,
			'intermediate' => 1,
			'expert'       => 2,
		) );

		if ( $level ) {
			if ( isset( $levels[ $level ] ) ) {
				return $levels[ $level ];
			} else {
				return 0;
			}
		}

		return 0;
	}

	public static function is_course_on_sale( $course_id ) {
		if ( tutor_utils()->is_course_purchasable( $course_id ) ) {
			if ( tutor_utils()->has_wc() ) {
				$product_id = tutor_utils()->get_course_product_id( $course_id );
				$product    = wc_get_product( $product_id );
				if($product) {
					if ( $product->is_on_sale() ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	public static function getPriceOfCourses( $course_id, $type = 0 ) {
		if ( tutor_utils()->is_course_purchasable( $course_id ) ) {
			if ( tutor_utils()->has_wc() ) {
				$product_id = tutor_utils()->get_course_product_id( $course_id );
				$product    = wc_get_product( $product_id );

				if ( $product ) {
					if ( $type == 0 ) {
						return $product->get_regular_price();
					} else {
						return $product->get_sale_price();
					}
				}
			}
		}

		return 0;
	}

	public static function get_course_categories() {
		$categories = get_terms( [
			'taxonomy'   => \Edumall_Tutor::instance()->get_tax_category(),
			'parent'     => 0,
			'hide_empty' => 0,
		] );

		$category_options = array();
		foreach ( $categories as $category ) {
			$object             = new \stdClass();
			$object->id         = $category->term_id;
			$object->name       = esc_html( $category->name );
			$category_options[] = $object;
		}


		return $category_options;
	}

	public static function get_video_source() {

		$video_info = tutor_utils()->get_video_info();
		switch ( $video_info->source ) {
			case 'youtube':
				$disable_default_player_youtube = tutor_utils()->get_option( 'disable_default_player_youtube' );
				$youtube_video_id               = tutor_utils()->get_youtube_video_id( tutor_utils()->avalue_dot( 'source_youtube', $video_info ) );
				if ( $disable_default_player_youtube ) {
					return 'https://www.youtube.com/embed/' . $youtube_video_id;
				}
				break;
			case 'embedded':
				return tutor_utils()->array_get( 'source_embedded', $video_info );
				break;
			case 'vimeo':
				$disable_default_player_vimeo = tutor_utils()->get_option( 'disable_default_player_vimeo' );
				$video_id                     = tutor_utils()->get_vimeo_video_id( tutor_utils()->avalue_dot( 'source_vimeo', $video_info ) );
				if ( $disable_default_player_vimeo ) {
					return 'https://player.vimeo.com/video/' . $video_id;
				}

				return 'https://player.vimeo.com/video/' . $video_id . '?loop=false&amp;byline=false&amp;portrait=false&amp;title=false&amp;speed=true&amp;transparent=0&amp;gesture=media';
				break;
			case 'html5':
				return tutor_utils()->get_video_stream_url();
				break;
			case 'external_url':
				return tutor_utils()->array_get( 'source_external_url', $video_info );
				break;

		}

		return '';
	}

	public static function get_avatar_mb( $user_id = null, $size = 'thumbnail' ) {
		if ( ! $user_id ) {
			return '';
		}

		$user = tutor_utils()->get_tutor_user( $user_id );
		if ( $user->tutor_profile_photo ) {
			return Edumall_Tutor_Shortcode::instance()->get_attachment_by_id_mb( [
				'id'        => $user->tutor_profile_photo,
				'size'      => $size,
				'img_attrs' => [
					'class' => 'tutor-image-avatar',
				],
			] );
		}

		$name = $user->display_name;
		$arr  = explode( ' ', trim( $name ) );

		if ( count( $arr ) > 1 ) {
			$first_char  = substr( $arr[0], 0, 1 );
			$second_char = substr( $arr[1], 0, 1 );
		} else {
			$first_char  = substr( $arr[0], 0, 1 );
			$second_char = substr( $arr[0], 1, 1 );
		}
		$initial_avatar = strtoupper( $first_char . $second_char );

		return $initial_avatar;
	}

	public static function mark_lesson_title_preview( $post_id ) {
		$is_preview = (bool) get_post_meta( $post_id, '_is_preview', true );
		if ( $is_preview ) {
			return esc_html__( 'Preview', 'edumall' );
		} else {
			return 'lock';
		}

		return $newTitle;
	}


	public static function get_course_completed_percent_mb( $course_id = 0, $user_id = 0 ) {
		$course_id 	      = tutils()->get_post_id($course_id);
		$user_id          = tutils()->get_user_id($user_id);
		$completed_lesson = tutils()->get_completed_lesson_count_by_course($course_id, $user_id);
		$course_contents  = tutils()->get_course_contents_by_id($course_id);


		$totalContents    = tutils()->count($course_contents);
		$totalContents    = $totalContents ? $totalContents : 0;
		$completedCount   = $completed_lesson;

		if ( tutils()->count( $course_contents ) ) {

			foreach ( $course_contents as $content ) {

				if ( $content->post_type === 'tutor_quiz' ) {

					$attempt = tutils()->get_quiz_attempt( $content->ID ,$user_id);

					if ( $attempt) {
						$completedCount++;
					}
				} elseif ( $content->post_type === 'tutor_assignments' ) {
					$isSubmitted = tutils()->is_assignment_submitted( $content->ID,$user_id );
					if ( $isSubmitted ) {

						$completedCount++;
					}
				}
			}

		}

		if ( $totalContents > 0 && $completedCount > 0 ) {
			return number_format( ( $completedCount * 100 ) / $totalContents );
		}

		return 0;
	}

	public static function update_post_photo_mb($filebody,$name,$imagetype){


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
		$file['type']     = Edumall_Mobile_Utils::getImageType($imagetype);
		$file['size']     = filesize( $fullFileName );

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



				return $media_id;
			}
		}

		return false;

	}


	public static function save_video_mp4($isOne) {

		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
			require_once( ABSPATH . "wp-admin" . '/includes/file.php' );
			require_once( ABSPATH . "wp-admin" . '/includes/media.php' );
		}
		if ( $_FILES ) {

			foreach ( $_FILES as $file => $array ) {
				if ( $_FILES[ $file ]['error'] !== UPLOAD_ERR_OK ) {
					return "upload error : " . $_FILES[ $file ]['error'];
				}

				$attach_id = media_handle_upload( $file,0 );
				if($isOne) return $attach_id;



			}
		}

	}

	public static function delete_attachment_id($id) {
		$result = wp_delete_attachment($id);
		return $result;
	}

	public static function convertToVideoArray($request,$poster,$source_video_id) {

		return array(
			'poster'=>$poster+'',
			'source'=>$request['source'],
			'source_embedded'=>$request['source_embedded'],
			'source_external_url'=>$request['source_external_url'],
			'source_vimeo'=>$request['source_vimeo'],
			'source_youtube'=>$request['source_youtube'],
			'source_video_id'=>$source_video_id,
		);



	}

	public static function convertToVideoArrayWithPostId($request,$poster,$source_video_id) {
		return
			array(
			'poster'=>$poster,
			'source'=>Edumall_Mobile_Utils::convertToSourceVideoFormat($request['type_video']),
			'source_embedded'=>'',
			'source_external_url'=>'',
			'source_vimeo'=>'',
			'source_youtube'=>$request['youtube_url'],
			'source_video_id'=>$source_video_id,
			'runtime'=>array(
				'hours' => $request['hh'],
				'minutes' =>$request['mm'],
				'seconds' => $request['ss']
			)
		);



	}

	public static function convertToSourceVideoFormat($type){
		switch($type){
			case 1;
			return 'html5';
				break;
			case 2;
			return 'youtube';
			  break;

		}
		return 'html5';
	}


	public static  function getImageType($imagetype) {
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


	public static function upload_video_mb($request){
		if ( $_FILES ) {
			$files = $_FILES["video"];
			foreach ($files['name'] as $key => $value) {
				if ($files['name'][$key]) {
					$file = array(
						'name' => $files['name'][$key],
						'type' => $files['type'][$key],
						'tmp_name' => $files['tmp_name'][$key],
						'error' => $files['error'][$key],
						'size' => $files['size'][$key]
					);
					$_FILES = array ("video" => $file);
					foreach ($_FILES as $file => $array) {
						$newupload = Edumall_Mobile_Utils::kv_handle_attachment($file,22);
					}
				}
			}
		}

	}


	public static function kv_handle_attachment($file_handler,$post_id,$set_thu=false) {
		// check to make sure its a successful upload
		if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();

		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');

		$attach_id = media_handle_upload( $file_handler, $post_id );

		// If you want to set a featured image frmo your uploads.
		if ($set_thu) set_post_thumbnail($post_id, $attach_id);
		return $attach_id;
	}

	public static function save_image_post($request){
		$_thumbnail_id = 0;
		if($request['imagecoursephoto']!='') {
			$cover_photo_body = base64_decode( $request['imagecoursephoto'] );
			$_thumbnail_id = Edumall_Mobile_Utils::update_post_photo_mb($cover_photo_body,$request['imagecoursename'],$request['imagecoursetype']);
			if ( $_thumbnail_id ) {
				update_post_meta( $request['post_id'], '_thumbnail_id', $_thumbnail_id );
			}
		}

		return $_thumbnail_id ;

	}

	/**
	 * @param null $type
	 *
	 * @return array|mixed
	 *
	 * Get all question types
	 *
	 * @since v.1.0.0
	 */
	public static function get_question_types( $type = null ) {
		$types = array(
			'true_false'        	=> array( 'name' => __('True/False', 'tutor'), 'is_pro' => false ),
			'single_choice'     	=> array( 'name' => __('Single Choice', 'tutor'),'is_pro' => false ),
			'multiple_choice'   	=> array( 'name' => __('Multiple Choice', 'tutor'), 'is_pro' => false ),
			'open_ended'        	=> array( 'name' => __('Open Ended/Essay', 'tutor'),  'is_pro' => false ),
			'fill_in_the_blank'  	=> array( 'name' => __('Fill In The Blanks', 'tutor'), 'is_pro' => false ),
			'short_answer'          => array( 'name' => __('Short Answer', 'tutor'), 'is_pro' => true ),
			'matching'              => array( 'name' => __('Matching', 'tutor'),  'is_pro' => true ),
			'image_matching'        => array( 'name' => __('Image Matching', 'tutor'),  'is_pro' => true ),
			'image_answering'       => array( 'name' => __('Image Answering', 'tutor'),  'is_pro' => true ),
			'ordering'          	=> array( 'name' => __('Ordering', 'tutor'),'is_pro' => true ),
		);

		if ( isset( $types[ $type ] ) ) {
			return $types[ $type ];
		}

		return $types;
	}

	/**
	 * @param int $price
	 *
	 * @return int|string
	 *
	 * Get the price format
	 *
	 * @since v.1.1.2
	 */
	public static function tutor_price( $price = 0 ) {
		if ( function_exists( 'wc_price') ) {
			return Edumall_Mobile_Utils::wc_price( $price );
		} elseif ( function_exists( 'edd_currency_filter' ) ) {
			return edd_currency_filter( edd_format_amount( $price ) );
		}else{
			return number_format_i18n( $price );
		}
	}



	/**
	 * Format the price with a currency symbol.
	 *
	 * @param  float $price Raw price.
	 * @param  array $args  Arguments to format a price {
	 *     Array of arguments.
	 *     Defaults to empty array.
	 *
	 *     @type bool   $ex_tax_label       Adds exclude tax label.
	 *                                      Defaults to false.
	 *     @type string $currency           Currency code.
	 *                                      Defaults to empty string (Use the result from get_woocommerce_currency()).
	 *     @type string $decimal_separator  Decimal separator.
	 *                                      Defaults the result of wc_get_price_decimal_separator().
	 *     @type string $thousand_separator Thousand separator.
	 *                                      Defaults the result of wc_get_price_thousand_separator().
	 *     @type string $decimals           Number of decimals.
	 *                                      Defaults the result of wc_get_price_decimals().
	 *     @type string $price_format       Price format depending on the currency position.
	 *                                      Defaults the result of get_woocommerce_price_format().
	 * }
	 * @return string
	 */
	public static function wc_price( $price, $args = array() ) {
		$args = apply_filters(
			'wc_price_args',
			wp_parse_args(
				$args,
				array(
					'ex_tax_label'       => false,
					'currency'           => '',
					'decimal_separator'  => wc_get_price_decimal_separator(),
					'thousand_separator' => wc_get_price_thousand_separator(),
					'decimals'           => wc_get_price_decimals(),
					'price_format'       => get_woocommerce_price_format(),
				)
			)
		);

		$original_price = $price;

		// Convert to float to avoid issues on PHP 8.
		$price = (float) $price;

		$unformatted_price = $price;
		$negative          = $price < 0;
		$formatted_price = ( $negative ? '-' : '' ) . sprintf($args['price_format'], get_woocommerce_currency_symbol( $args['currency'] ) ,  $price) ;
		if ( $args['ex_tax_label'] && wc_tax_enabled() ) {
			$formatted_price .= WC()->countries->ex_tax_or_vat();
		}
		return $formatted_price ;



	}

	/**
	 * @param int $course_id
	 *
	 * @return bool|false|string
	 *
	 * Get first lesson of a course
	 *
	 * @since v.1.0.0
	 */
	public static function get_course_first_lesson( $course_id = 0 ,$user_id=0) {
		global $wpdb;


		$lessons = $wpdb->get_results( $wpdb->prepare(
			"SELECT items.ID
			FROM 	{$wpdb->posts} topic
					INNER JOIN {$wpdb->posts} items
							ON topic.ID = items.post_parent
			WHERE 	topic.post_parent = %d
					AND items.post_status = %s
			ORDER BY topic.menu_order ASC,
					items.menu_order ASC;
			",
			$course_id,
			'publish'
		) );

		$first_lesson = false;

		if ( tutils()->count( $lessons ) ) {
			if ( ! empty( $lessons[0] ) ) {
				$first_lesson = $lessons[0];
			}

			foreach ( $lessons as $lesson ) {
				$is_complete = get_user_meta( $user_id, "_tutor_completed_lesson_id_{$lesson->ID}", true );
				if ( ! $is_complete ) {
					$first_lesson = $lesson;
					break;
				}
			}

			if ( ! empty($first_lesson->ID) ) {
				return $first_lesson->ID ;
			}
		}

		return false;
	}


	/**
	 * @param int $user_id
	 *
	 * @return array|null|object
	 *
	 * Get purchase history by customer id
	 */
	public function get_orders_by_user_id( $user_id = 0 ) {
		global $wpdb;


		$monetize_by = tutils()->get_option( 'monetize_by' );

		$post_type = "";
		$user_meta = "";

		if ( $monetize_by === 'wc' ) {
			$post_type = "shop_order";
			$user_meta = "_customer_user";
		} else if ( $monetize_by === 'edd' ) {
			$post_type = "edd_payment";
			$user_meta = "_edd_payment_user_id";
		}

		$orders = $wpdb->get_results( $wpdb->prepare(
			"SELECT {$wpdb->posts}.*
			FROM	{$wpdb->posts}
					INNER JOIN {$wpdb->postmeta} customer
							ON id = customer.post_id
						   AND customer.meta_key = '{$user_meta}'
					INNER JOIN {$wpdb->postmeta} tutor_order
							ON id = tutor_order.post_id
						   AND tutor_order.meta_key = '_is_tutor_order_for_course'
			WHERE	post_type = %s
					AND customer.meta_value = %d 
			ORDER BY {$wpdb->posts}.id DESC
			",
			$post_type,
			$user_id
		) );

		return $orders;
	}



}
