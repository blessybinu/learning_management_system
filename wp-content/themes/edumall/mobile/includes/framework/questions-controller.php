<?php
namespace edumallmobile\framework;
use edumallmobile\utils\Edumall_Mobile_Utils;

class Edumall_Questions_Controller {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function get_section_lession($request,$user_id){


		$object  = new \stdClass();


		$object->topics = $this->get_lession_topics($request,$user_id);

		//browser Q&A
		$object->browser_qa = $this->get_lession_browser_qa($request,$user_id);

		//content
		$object->content = $this->get_lession_content($request,$user_id);

		return $object;



	}



	public function get_lession_topics($request,$user_id){

		global $post;
		$post = get_post((int)$request['lession_id']) ;

		$currentPost = $post;

		$course_id = 0;
		if ( $post->post_type === 'tutor_quiz' ) {
			$course    = tutor_utils()->get_course_by_quiz( $post->ID );
			$course_id = $course->ID;
		} elseif ( $post->post_type === 'tutor_assignments' ) {
			$course_id = get_post_meta( $post->ID, '_tutor_course_id_for_assignments', true );
		} elseif ( $post->post_type === 'tutor_zoom_meeting' ) {
			$course_id = get_post_meta( $post->ID, '_tutor_zm_for_course', true );
		} else {
			$course_id = get_post_meta( $post->ID, '_tutor_course_id_for_lesson', true );
		}
		$disable_qa_for_this_course = get_post_meta( $course_id, '_tutor_disable_qa', true );
		$enable_q_and_a_on_course   = tutor_utils()->get_option( 'enable_q_and_a_on_course' ) && $disable_qa_for_this_course != 'yes';

		$topics = tutor_utils()->get_topics( $course_id );
		$topics_arr  = [];
		if ( $topics->have_posts() ) {
			while ( $topics->have_posts() ) {
				$object_topic = new \stdClass();
				$topics->the_post();
				$object_topic->topic_id          = get_the_ID();
				$object_topic->topic_summery     = get_the_content();
				$object_topic->has_topic_summery = false;
				if ( $object_topic->topic_summery ) {
					$object_topic->has_topic_summery = true;
				}
				$object_topic->topic_title = get_the_title();

				$lessons = tutor_utils()->get_course_contents_by_topic( get_the_ID(), - 1 );
				$lession_arr = [];
				if ( $lessons->have_posts() ) {

					while ( $lessons->have_posts() ) {
						$lessons->the_post();


						$object_lessions = new \stdClass();
						$object_lessions->post_type =$post->post_type;
						if ( $post->post_type === 'tutor_quiz' ) {
							$quiz = $post;

							if ( $currentPost->ID === get_the_ID() ) {
								$object_lessions->active = true;
							} else {
								$object_lessions->active = false;
							}
							$object_lessions->quiz_id    = $quiz->ID;
							$object_lessions->quiz_title = $quiz->post_title;
							$object_lessions->time_limit = '0';
							$time_limit                  = tutor_utils()->get_quiz_option( $quiz->ID, 'time_limit.time_value' );
							if ( $time_limit ) {
								$time_type                   = tutor_utils()->get_quiz_option( $quiz->ID, 'time_limit.time_type' );
								$object_lessions->time_limit = $time_limit;
								$object_lessions->time_type  = $time_type;

							}
						} elseif ( $post->post_type === 'tutor_assignments' ) {
							/**
							 * Assignments
							 *
							 * @since this block v.1.3.3
							 */
							if ( $currentPost->ID === get_the_ID() ) {
								$object_lessions->active = true;
							} else {
								$object_lessions->active = false;
							}
							$object_lessions->quiz_id     = $post->ID;
							$object_lessions->quiz_title  = $post->post_title;
							$object_lessions->submit_icon = $this->show_assignment_submitted_icon( $post );
						} elseif ( $post->post_type === 'tutor_zoom_meeting' ) {
							if ( $currentPost->ID === get_the_ID() ) {
								$object_lessions->active = true;
							} else {
								$object_lessions->active = false;
							}
							$object_lessions->quiz_id    = $post->ID;
							$object_lessions->quiz_title = $post->post_title;

						} else {
							$video                      = tutor_utils()->get_video( $post->ID );
							$video                      = \Edumall_Tutor::instance()->get_video_info( $video, get_the_ID() );
							$play_time                  = false;
							$object_lessions->has_video = false;
							if ( $video ) {
								$object_lessions->has_video = true;
								$object_lessions->playtime  = $video->playtime;
							}
							$object_lessions->is_completed_lesson = tutor_utils()->is_completed_lesson( $post->ID, $user_id );
							if ( $currentPost->ID === get_the_ID() ) {
								$object_lessions->active = true;
							} else {
								$object_lessions->active = false;
							}
							$object_lessions->quiz_id     = $post->ID;
							$object_lessions->quiz_title  = $post->post_title;
						}
						$lession_arr[] = $object_lessions;
					}
					$lessons->reset_postdata();
				}



				$object_topic->lessions = $lession_arr ;
				$topics_arr[]=$object_topic;
			}
			$topics->reset_postdata();
			wp_reset_postdata();

		}
		return $topics_arr;
	}

	public function get_lession_browser_qa($request,$user_id){
		$object_qa = new \stdClass();
		$object_qa->browser_qa_allow = true;
		$enable_q_and_a_on_course = tutor_utils()->get_option( 'enable_q_and_a_on_course' );
		if ( ! $enable_q_and_a_on_course ) {
			$object_qa->browser_qa_allow = false;
			$object_qa->browser_qa_allow_msg =  __( 'This feature has been disabled by the administrator', 'edumall' );
		}

		global $post;
		$post = get_post((int)$request['lession_id']) ;
		$currentPost = $post;

		$course_id = tutils()->get_course_id_by_content( $post );
		$current_user    = tutor_utils()->get_user_id($user_id);
		$all_instructors = tutor_utils()->get_instructors_by_course( $course_id );

		if($all_instructors) {
			$all_instructors = wp_list_pluck( (array) $all_instructors, 'ID' );
		}
		else{
			$all_instructors =[];
		}
		$object_qa->course_id = $course_id;
		$object_qa->all_instructors =$all_instructors;
		$object_qa->current_user =$current_user;

		if ( in_array( $current_user, $all_instructors ) ) {
			$questions = tutor_utils()->get_top_question( $course_id, $current_user, 0, 20, true );
			$object_qa->instruct = true;
		} else {

			$questions = tutor_utils()->get_top_question( $course_id ,$user_id);
			$object_qa->instruct = false;

		}


		$arr_question = [];
		if ( is_array( $questions ) && count( $questions ) ) {
			$object_qa->has_question = true;

			foreach ( $questions as $question ) {
				$object_qe  = new \stdClass();
				$object_qe->id = $question->comment_ID;
				$object_qe->profile_url =tutor_utils()->profile_url( $question->user_id );

				$object_qe->avatar = Edumall_Mobile_Utils::get_avatar_mb( $question->user_id, 52 );
				$object_qe->name = $question->display_name;
				$object_qe->time =  sprintf( __( '%s ago', 'edumall' ), human_time_diff( strtotime	( $question->comment_date ) ) ) ;
				$object_qe->question_title = $question->question_title;
				$object_qe->comment_content = wp_strip_all_tags(wpautop( stripslashes( $question->comment_content ) ));

				$answers = tutor_utils()->get_qa_answer_by_question( $question->comment_ID );


				$answer_arr = [];
				if ( is_array( $answers ) && count( $answers ) ) {

					foreach ( $answers as $answer ) {
						$object_a = new \stdClass();
						$object_a->profile_url = tutor_utils()->profile_url( $answer->user_id );
						if ( $question->user_id == $answer->user_id ) {
							$object_a->is_white = true;
						} else {
							$object_a->is_white = false;
						}
						$object_a->avatar = Edumall_Mobile_Utils::get_avatar_mb( $answer->user_id, 52 );
						$object_a->name = $answer->display_name;
						$object_a->time = sprintf( __( '%s ago', 'edumall' ), human_time_diff( strtotime
						( $answer->comment_date ) ) );
						$object_a->comment_content =wp_strip_all_tags(wpautop( stripslashes( $answer->comment_content ) ));
						$object_a->id = $answer->comment_ID;
						$object_a->parent = $answer->comment_parent;
						$answer_arr[] = $object_a;

					}
				}
				$object_qe->answers =$answer_arr;
				$object_qe->id = $question->comment_ID;
				$arr_question [] =$object_qe;

			}
		}
		else {
			$object_qa->has_question = false;

			$object_qa->no_question_title = __( 'No questions yet', 'edumall' );
			$object_qa->no_question_msg = __( 'Be the first to ask your question! You’ll be able to add details in the next step.', 'edumall' );

		}
		$object_qa->questions = $arr_question;

		$object_qa->add_question_anq =  __( 'Ask a new question', 'edumall' );
		$object_qa->add_question_qt =   __( 'Question Title', 'edumall' );
		$object_qa->add_question_sm =  	__( 'Submit my question', 'edumall' );

		return $object_qa;
	}

	public function get_lession_content($request,$user_id){


		$post = get_post((int)$request['lession_id']) ;
		if ( $post->post_type === 'tutor_quiz' ) {
			return $this->get_type_quizz($request,$user_id);
		}elseif ( $post->post_type === 'tutor_assignments' ){
			return $this->get_type_assignments($request,$user_id);
		}
		elseif ( $post->post_type === 'tutor_zoom_meeting' ) {
			return $this->get_type_zoom_meeting($request,$user_id);
		}
		else {
			return $this->get_type_lession($request,$user_id);
		}



	}

	public function get_type_assignments($request,$user_id){
		$object_c = new \stdClass();
		$object_c->type = 'assignments';
		$array_static = [];
		global $wpdb;
		global $post;
		$post = get_post((int)$request['lession_id']) ;
		$is_submitted  = false;
		$is_submitting = tutor_utils()->is_assignment_submitting( get_the_ID(),$user_id );
		$object_c->is_submitting =  $is_submitting;

		//get the comment
		$post_id            = get_the_ID();

		$assignment_comment = tutor_utils()->get_single_comment_user_post_id( $post_id, $user_id );
		$object_c->has_submitted= false;
		if ( $assignment_comment != false ) {
			$submitted = $assignment_comment->comment_approved;
			$submitted == 'submitted' ? $is_submitted = true : '';
			$object_c->has_submitted= true;
			$object_c->submitted= $submitted;
		}

		$course_id = get_post_meta( get_the_ID(), '_tutor_course_id_for_assignments', true );
		$object_c->course_id =$course_id;
		$object_c->course_name =get_the_title($course_id);
		$object_c->lession_title = get_the_title();
		$array_static['go_to_course_home'] = __( 'Go to course home', 'edumall' );

		$time_duration = tutor_utils()->get_assignment_option( get_the_ID(), 'time_duration' );

		$total_mark = tutor_utils()->get_assignment_option( get_the_ID(), 'total_mark' );
		$pass_mark  = tutor_utils()->get_assignment_option( get_the_ID(), 'pass_mark' );

		$object_c->time_duration =  $time_duration;
		$object_c->total_mark = $total_mark;
		$object_c->pass_mark  =$pass_mark;

		$assignment_created_time = strtotime( $post->post_date_gmt );
		$time_duration_in_sec    = 0;
		if ( isset( $time_duration['value'] ) and isset( $time_duration['time'] ) ) {
			switch ( $time_duration['time'] ) {
				case 'hours':
					$time_duration_in_sec = 3600;
					break;
				case 'days':
					$time_duration_in_sec = 86400;
					break;
				case 'weeks':
					$time_duration_in_sec = 7 * 86400;
					break;
				default:
					$time_duration_in_sec = 0;
					break;
			}
		}
		$time_duration_in_sec = $time_duration_in_sec * $time_duration['value'];
		$remaining_time       = $assignment_created_time + $time_duration_in_sec;
		$now                  = time();
		$object_c->time_duration_in_sec = $time_duration_in_sec;
		$object_c->remainint_time = $remaining_time;
		$array_static['time_duration'] = __( 'Time Duration :', 'edumall' );
		$object_c->time_duration =  $time_duration['value'] ? $time_duration['value'] . ' ' . $time_duration['time'] : __( 'No limit', 'edumall' );
		$array_static['dead_line'] = __( 'Deadline : ', 'edumall' );
		$array_static['expired'] = __( 'Expired', 'edumall' );
		$array_static['total_points'] = __( 'Total Points : ', 'edumall' );
		$array_static['minimum_pass_points'] = __( 'Minimum Pass Points : ', 'edumall' );
		$array_static['you_have_missed'] = __( 'You have missed the submission deadline. Please contact the instructor for more information.', 'edumall' );


		$object_c->has_missed_deatline = false;
		if ( $time_duration['value'] != 0 ) {
			if ( $now > $remaining_time and $is_submitted == false ) {
				$object_c->has_missed_deatline = true;
			}
		}

		$array_static['description'] = __( 'Description', 'edumall' );
		$content_post = get_post($request['lession_id']);


		$object_c->lession_content =wp_strip_all_tags($content_post->post_content);
		$assignment_attachments = maybe_unserialize( get_post_meta( get_the_ID(), '_tutor_assignment_attachments', true ) );


		$attachments_arr = [];



		if ( tutor_utils()->count( $assignment_attachments ) ) {
			$array_static['attachments'] = __( 'Attachments', 'edumall' );
				 foreach ( $assignment_attachments as $attachment_id ) {
					 if ( $attachment_id ) {
					 	$object_att =  new \stdClass();
						$attachment_name = get_post_meta( $attachment_id, '_wp_attached_file', true );
						$attachment_name = substr( $attachment_name, strrpos( $attachment_name, '/' ) + 1 );
						$object_att->name =  $attachment_name;
						$object_att->url =  wp_get_attachment_url( $attachment_id );
						$attachments_arr[] =$object_att;
					 }
				}
		}
		$object_c->attachments = $attachments_arr;
		$object_c->assigment_id = get_the_ID();
		$object_c->is_start_submitted = false;

		$submitted_assignment = tutor_utils()->is_assignment_submitted( get_the_ID(),$user_id );
		if ( !$submitted_assignment and ( $remaining_time > $now or $time_duration['value'] == 0 ) ) {
			$object_c->is_start_submitted = true;
			$array_static['assignment_answer_form'] = __( 'Assignment answer form', 'edumall' );
			$allowd_upload_files = (int) tutor_utils()->get_assignment_option( get_the_ID(), 'upload_files_limit' );
			$array_static['assignment_answer_form'] =  __( 'Write your answer briefly', 'edumall' );
			$object_c->allowd_upload_files = 0;
			 if ( $allowd_upload_files ) {
						$array_static['attach_assignment_files'] =  __( 'Attach assignment files', 'edumall' );
						$object_c->allowd_upload_files = $allowd_upload_files;
			 }
			$array_static['submit_assignment'] =  __( 'Submit Assignment', 'edumall' );
			 } else {
			$object_c->has_submitted_assignment = false;


			if ( $submitted_assignment ) {
				$object_c->has_submitted_assignment = true;
				$is_reviewed_by_instructor = get_comment_meta( $submitted_assignment->comment_ID, 'evaluate_time', true );
				$object_c->is_reivew_by_instructor = false;
				$object_c->comment_id_review = $submitted_assignment->comment_ID;

				if ( $is_reviewed_by_instructor ) {
					$object_c->is_reivew_by_instructor = true;
					$assignment_id = $submitted_assignment->comment_post_ID;
					$submit_id     = $submitted_assignment->comment_ID;
					$object_c->sa_assignment_id = $assignment_id;
					$object_c->sa_submit_id = $submit_id;

					$max_mark   = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'total_mark' );
					$pass_mark  = tutor_utils()->get_assignment_option( $submitted_assignment->comment_post_ID, 'pass_mark' );
					$given_mark = get_comment_meta( $submitted_assignment->comment_ID, 'assignment_mark', true );

					$object_c->sa_max_mark = $max_mark;
					$object_c->sa_pass_mark = $pass_mark;
					$object_c->sa_given_mark = $given_mark;
					$array_static['you_recieve_points_out'] = __( 'You received %s points out of %s', 'edumall' );
					$array_static['you_grade_is'] = __( 'Your Grade is ', 'edumall' );

				 }

				$array_static['you_answers'] = __( 'Your Answers', 'edumall' );
				$object_c->sa_your_answer_content = stripslashes( $submitted_assignment->comment_content ) ;

				$attachment_answer_arr = [];
				$attached_files = get_comment_meta( $submitted_assignment->comment_ID, 'uploaded_attachments', true );

					if ( $attached_files ) {
						$attached_files = json_decode( $attached_files, true );

						if ( tutor_utils()->count( $attached_files ) ) {
							$array_static['you_uploaded_files'] = __( 'Your uploaded file(s)', 'edumall' );
							$upload_dir     = wp_get_upload_dir();
							$upload_baseurl = trailingslashit( tutor_utils()->array_get( 'baseurl', $upload_dir ) );
							foreach ( $attached_files as $attached_file ) {

							$object_att =  new \stdClass();
							$object_att->url = $upload_baseurl . tutor_utils()->array_get( 'uploaded_path', $attached_file );
							$object_att->name = tutor_utils()->array_get( 'name', $attached_file );
							$attachment_answer_arr[] = $object_att;
							}
						}
					}
				$object_c->attachment_answer_arr =$attachment_answer_arr;


					if ( $is_reviewed_by_instructor ) {
							$array_static['instructor_note'] = __( 'Instructor Note', 'edumall' );
							$object_c->intructor_note =  get_comment_meta( $submitted_assignment->comment_ID, 'instructor_note', true ) ;
						}

				} else {
							$object_c->is_disable = false;
							if ( $now > $remaining_time ) {
								$object_c->is_disable = true;

							}
							$array_static['submit_assignment'] =	__( 'Submit assignment', 'edumall' );

				}
		}

		$content_id = tutils()->get_post_id((int)$request['lession_id']);
		$contents = tutils()->get_course_prev_next_contents_by_id($content_id);
		$object_c->previous_id = $contents->previous_id;
		$object_c->next_id = $contents->next_id;


		$object_c->static_arr =$array_static;
		return $object_c;
	}

	public function get_type_quizz($request,$user_id){
		$object_c = new \stdClass();
		$object_c->type = 'quizz';
		$array_static = [];
		global $post;
		$post = get_post((int)$request['lession_id']) ;
		$course    = tutor_utils()->get_course_by_quiz( get_the_ID() );
		$course_id = $course->ID;
		$array_static['go_to_course_home'] = __( 'Go to course home', 'edumall' );
		$object_c->lession_title = get_the_title();
		$object_c->course_id =$course_id;
		$object_c->course_name =get_the_title($course_id);

		if ( $course ) {
			$object_c->is_no_course_belong = false;
			$object_c->top =  $this->tutor_single_quiz_top($request,$user_id);
			$object_c->content = $this->tutor_single_quiz_content($request,$user_id);
			$object_c->body = $this->tutor_single_quiz_body($request,$user_id);
		} else {
			$object_c->is_no_course_belong = true;
			$object_c->no_course_belong = $this->tutor_single_quiz_no_course_belongs();
		}

		$content_id = tutils()->get_post_id((int)$request['lession_id']);
		$contents = tutils()->get_course_prev_next_contents_by_id($content_id);
		$object_c->previous_id = $contents->previous_id;
		$object_c->next_id = $contents->next_id;

		$object_c->static_arr =$array_static;

		return $object_c;
	}

	public function get_type_zoom_meeting($request,$user_id){
		$object_c = new \stdClass();
		$array_static = [];
		$object_c->type = 'zoom';
		global $post;
		$post = get_post((int)$request['lession_id']) ;
		$currentPost = $post;
		$object_c->lession_title = get_the_title();
		$course_id = get_post_meta($post->ID, '_tutor_zm_for_course', true);
		$zoom_meeting = tutor_zoom_meeting_data( $post->ID );
		$meeting_data = $zoom_meeting->data;
		$browser_url  = "https://us04web.zoom.us/wc/join/{$meeting_data['id']}?wpk={$meeting_data['encrypted_password']}";
		$browser_text = __( 'Join in Browser', 'edumall' );

		if ( $user_id== $post->post_author ) {
			$browser_url  = $meeting_data['start_url'];
			$browser_text = __( 'Start Meeting', 'edumall' );
			$object_c->browser_url = $browser_url;
			$object_c->browser_text = $browser_text;
		}
		$array_static['go_to_course_home'] = __( 'Go to course home', 'edumall' );

		$video = tutor_utils()->get_video_info( get_the_ID() );
		$object_c->video = $video ;
		$object_c->has_play_time = false ;
		$play_time = false;
		if ( $video ) {
			$play_time = $video->playtime;
			$object_c->has_play_time = true ;
			$object_c->play_time = $play_time;
		}
		$object_c->title = get_the_title();

		if ( $zoom_meeting->is_expired ) {
					$object_c->zoom_expired = TUTOR_ZOOM()->url . 'assets/images/zoom-icon-expired.png';
					$array_static['the_video_conference'] = __( 'The video conference has expired', 'edumall' );

					$array_static['please_contact_your_instructor'] = __( 'Please contact your instructor for further information', 'edumall' );
					$object_c->content =$post->post_content;
					$array_static['meeting_date'] = __( 'Meeting Date', 'edumall' );
					$object_c->zoom_start_date = $zoom_meeting->start_date;

					$array_static['host_email'] = __( 'Host Email', 'edumall' );
					$object_c->host_email = $meeting_data['host_email'];
		}
		else{
		    $object_c->countdown_date = $zoom_meeting->countdown_date;
		    $object_c->timezone = $zoom_meeting->timezone;
		    $object_c->join_url = esc_url( $meeting_data['join_url'] );
		    $array_static['join_in_zoom_app'] = __( 'Join in Zoom App', 'edumall' );
		    $object_c->content =$post->post_content;
		    $array_static['meeting_date'] = __( 'Meeting Date', 'edumall' );
		    $object_c->zoom_start_date = $zoom_meeting->start_date;
		    $array_static['meeting_id'] = __( 'Meeting ID', 'edumall' );
		    $object_c->meeting_id = $meeting_data['id'];
		    $array_static['password'] = __( 'Password', 'edumall' );
		    $object_c->password = $meeting_data['password'];
		    $array_static['meeting_id'] = __( 'Host Email', 'edumall' );
			$object_c->host_email = $meeting_data['host_email'];

		}



		$object_c->static_arr =$array_static;
		$object_c->course_id =$course_id;
		$object_c->course_name =get_the_title($course_id);

		return $object_c;
	}


	public function get_type_lession($request,$user_id){
		$object_c = new \stdClass();
		$object_c->type = 'lession';
		global $post;
		$post = get_post((int)$request['lession_id']) ;
		$currentPost = $post;

		$array_static = [];
		$jsonData                                 = array();
		$jsonData['post_id']                      = $post->ID;
		$jsonData['best_watch_time']              = 0;
		$jsonData['autoload_next_course_content'] = (bool) get_tutor_option( 'autoload_next_course_content' );

		$best_watch_time = tutor_utils()->get_lesson_reading_info( $post->ID, 0, 'video_best_watched_time' );
		if ( $best_watch_time > 0 ) {
			$jsonData['best_watch_time'] = $best_watch_time;
		}
		$course_id = get_post_meta( $post->ID, '_tutor_course_id_for_lesson', true );
		$array_static['go_to_course_home'] = __( 'Go to course home', 'edumall' );

		$video = tutor_utils()->get_video_info(  $post->ID );


		$object_c->has_play_time =  false;
		if ( $video ) {

			$play_time = $video->playtime;
			$object_c->has_play_time =  true;
			$object_c->play_time =  $play_time;

			if($video->source =='html5'){
				$object_c->urlVideo = wp_get_attachment_url( $video['source_video_id'] );

			}
		}
		$object_c->lession_title =$post->post_title;
		$iscompletelession = tutor_utils()->is_completed_lesson((int)$request['lession_id'],$user_id);
		if($iscompletelession){
			$object_c->is_completed_lesson = $iscompletelession;
		}
		else{
			$object_c->is_completed_lesson = 'false';
		}

		$array_static['complete_lession'] =  __( 'Complete Lesson', 'tutor' );

		$object_c->video_info = tutor_utils()->get_video_info((int)$request['lession_id']);
		$object_c->content = wp_strip_all_tags($post->post_content);

		$attachments_arr = [];
		$attachments = tutor_utils()->get_attachments((int)$request['lession_id']);
		if (is_array($attachments) && count($attachments)){
			foreach ($attachments as $attachment){
				$object_att =  new \stdClass();
				$object_att->url =  $attachment->url;
				$object_att->name =  $attachment->name;
				$object_att->icon =  $attachment->icon;
				$object_att->size =  $attachment->size;

				$attachments_arr[] =$object_att;

			}
		}
		$array_static['attachments'] =  __( 'Attachments', 'tutor' );
		$object_c->attachements =$attachments;

		$content_id = tutils()->get_post_id((int)$request['lession_id']);
		$contents = tutils()->get_course_prev_next_contents_by_id($content_id);
		$object_c->previous_id = $contents->previous_id;
		$object_c->next_id = $contents->next_id;


		$object_c->static_arr =$array_static;
		$object_c->course_name =get_the_title($course_id);
		$object_c->course_id =$course_id;
		$object_c->data = $jsonData;
		return $object_c;
	}

	public function show_assignment_submitted_icon($post) {
		if ($post->post_type === 'tutor_assignments') {
			$is_submitted = tutils()->is_assignment_submitted($post->ID);

			if ($is_submitted && $is_submitted->comment_approved === 'submitted') {
				return true;
			} else {
				return false;
			}
		}
	}

	public function tutor_single_quiz_no_course_belongs(){

			$object = new \stdClass();
			$object->no_course_found = __('No course found for this quiz', 'tutor');
			$object->it_seems_there_is_no =__('It seems there is no course belongs with this quiz, you can not attempt on this quiz without a course belongs, please notify to your instructor to fix this issue.', 'tutor');
			return $object;
	}

	public function tutor_single_quiz_top($request,$user_id){
		$object = new \stdClass();
		global $post;
		$post = get_post((int)$request['lession_id']) ;
		$currentPost = $post;
		$static_arr =[];

		$course            = tutor_utils()->get_course_by_quiz( get_the_ID() );
		$previous_attempts = tutor_utils()->quiz_attempts($request['lession_id'],$user_id);
		$attempted_count   = is_array( $previous_attempts ) ? count( $previous_attempts ) : 0;
		$object->preivous_attempt = $previous_attempts ;
		$object->attempted_count = $attempted_count;

		$attempts_allowed = tutor_utils()->get_quiz_option( get_the_ID(), 'attempts_allowed', 0 );
		$passing_grade    = tutor_utils()->get_quiz_option( get_the_ID(), 'passing_grade', 0 );

		$attempt_remaining = $attempts_allowed - $attempted_count;
		$object->attempts_allowed = $attempts_allowed;
		$object->passing_grade = $passing_grade;
		$object->attempt_remaining = $attempt_remaining;
		$object->course_id = $course->ID;
		$object->course_title =get_the_title( $course->ID );

		$total_questions = tutor_utils()->total_questions_for_student_by_quiz( get_the_ID() );
		$time_limit      = tutor_utils()->get_quiz_option( get_the_ID(), 'time_limit.time_value' );
		$object->total_questions = $total_questions;
		$object->time_limit = $time_limit;
		$static_arr['questions'] =__( 'Questions', 'edumall' );

		 if ( $time_limit ) {
		 	    $time_type = tutor_utils()->get_quiz_option( get_the_ID(), 'time_limit.time_type' );
				$static_arr['time'] =__( 'Time', 'edumall' );
			    $object->time_type = $time_type;

		 }
		$static_arr['attempts_allowed'] = __('Attempts Allowed', 'tutor');
		$static_arr['no_limit'] = __('No limit', 'tutor');
		$static_arr['attempted'] = __('Attempted', 'tutor');
		$static_arr['attempts_remaining'] = __('Attempts Remaining', 'tutor');
		$static_arr['passing_grade'] = __('Passing Grade', 'tutor');

		$object->static = $static_arr;
		return $object;
	}

	public function tutor_single_quiz_content($request,$user_id){
		$object = new \stdClass();
		global $post;
		$post = get_post((int)$request['lession_id']) ;

		$object->content = get_the_content();

		return $object;
	}

	public function tutor_single_quiz_body($request,$user_id){
		$object = new \stdClass();
		$static_arr = [];
		global $post;
		$post = get_post((int)$request['lession_id']) ;
		$currentPost = $post;

		$quiz_id = get_the_ID();
		$is_started_quiz = $this->is_started_quiz($quiz_id,$user_id);

		$previous_attempts = tutor_utils()->quiz_attempts($quiz_id,$user_id);
		$attempted_count = is_array($previous_attempts) ? count($previous_attempts) : 0;
		$questions_order = tutor_utils()->get_quiz_option($quiz_id, 'questions_order', 'rand');
		$attempts_allowed = tutor_utils()->get_quiz_option($quiz_id, 'attempts_allowed', 0);
		$passing_grade = tutor_utils()->get_quiz_option($quiz_id, 'passing_grade', 0);
		$feedback_mode = tutor_utils()->get_quiz_option($quiz_id, 'feedback_mode', 0);
		$attempt_remaining = $attempts_allowed - $attempted_count;

		$object->is_started_quiz = false;

		$object->attempted_count = $attempted_count;
		$object->questions_order = $questions_order;
		$object->attempts_allowed = $attempts_allowed;
		$object->passing_grade = $passing_grade;
		$object->feedback_mode = $feedback_mode;
		$object->attempt_remaining = $attempt_remaining;

		if ($is_started_quiz) {
			$object->attempt_id =  $is_started_quiz->attempt_id;
			$object->is_started_quiz = true;
			$quiz_attempt_info                  = tutor_utils()->quiz_attempt_info( $is_started_quiz->attempt_info );
			$quiz_attempt_info['date_time_now'] = date( "Y-m-d H:i:s", tutor_time() );

			$time_limit_seconds   = tutor_utils()->avalue_dot( 'time_limit.time_limit_seconds', $quiz_attempt_info );
			$question_layout_view = tutor_utils()->get_quiz_option( $quiz_id, 'question_layout_view' );
			! $question_layout_view ? $question_layout_view = 'single_question' : 0;

			$hide_quiz_time_display        = (bool) tutor_utils()->avalue_dot( 'hide_quiz_time_display', $quiz_attempt_info );
			$hide_question_number_overview = (bool) tutor_utils()->avalue_dot( 'hide_question_number_overview', $quiz_attempt_info );

			$remaining_time_secs = ( strtotime( $is_started_quiz->attempt_started_at ) + $time_limit_seconds ) - strtotime( $quiz_attempt_info['date_time_now'] );

			$remaining_time_context = tutor_utils()->seconds_to_time_context( $remaining_time_secs );
			$questions              = $this->get_random_questions_by_quiz($quiz_id,$user_id);
			$object->quiz_attempt_info =$quiz_attempt_info;
			$object->time_limit_seconds =$time_limit_seconds;
			$object->question_layout_view =$question_layout_view;
			$object->hide_quiz_time_display =$hide_quiz_time_display;
			$object->hide_question_number_overview =$hide_question_number_overview;
			$object->remaining_time_secs = $remaining_time_secs;
			$object->remaining_time_context = $remaining_time_context;

			$static_arr['time_remaining'] = __('Time remaining : ','tutor');
			$static_arr['marks'] = __('Marks : ', 'tutor');
			$static_arr['characters_remaining'] = __('characters remaining', 'tutor' );
			$static_arr['answer_next_question'] = __( 'Answer & Next Question', 'tutor' );
			$static_arr['submit_quiz'] = __( 'Submit Quiz', 'tutor' );
			$static_arr['finish'] = __( 'Finish', 'tutor' );



			$object->has_questions = false;
			if (is_array($questions) && count($questions)) {
				$object->has_questions = true;
				$question_i = 0;
						foreach ($questions as $question) {
							$question_i++;
							$question_settings = maybe_unserialize($question->question_settings);
							$question->question_settings_serialize = $question_settings;
							$question->question_title = wp_strip_all_tags($question->question_title);

							$question_type = $question->question_type;

								$rand_choice = false;
								if($question_type == 'single_choice' || $question_type == 'multiple_choice'){
									$choice = maybe_unserialize($question->question_settings);
									if(isset($choice['randomize_question'])){
										$rand_choice = $choice['randomize_question'] == 1 ? true : false;
									}
								}

								$answers = tutor_utils()->get_answers_by_quiz_question($question->question_id, $rand_choice);
								$show_question_mark = (bool) tutor_utils()->avalue_dot('show_question_mark', $question_settings);
								$answer_required = (bool) tutils()->array_get('answer_required', $question_settings);

								$question_description = wp_strip_all_tags( stripslashes($question->question_description) );
								$question->question_description = $question_description;
								$answer_arr = [];
								if ( is_array($answers) && count($answers) ) {
									if( $question_type === 'true_false' || $question_type === 'single_choice' || $question_type === 'multiple_choice' || $question_type === 'fill_in_the_blank' ||  $question_type === 'ordering' ) {
										foreach ( $answers as $answer ) {
											$object_as              = new \stdClass();
											$object_as->attempt_id  = $is_started_quiz->attempt_id;
											$object_as->question_id = $question->question_id;

											$answer_title                  = wp_strip_all_tags( stripslashes( $answer->answer_title ) );
											$object_as->answer_title       = $answer_title;
											$object_as->answer_view_format = $answer->answer_view_format;
											$object_as->answer_id  = $answer->answer_id;
											if ( $question_type === 'true_false' || $question_type === 'single_choice' || $question_type === 'multiple_choice' ) {

												if ( $answer->answer_view_format === 'image' || $answer->answer_view_format === 'text_image' ) {
													$object_as->image_url = wp_get_attachment_image_url( $answer->image_id, 'full' );

												}
												$object_as->answer_id  = $answer->answer_id;
												$object_as->is_correct = $answer->is_correct;


											} elseif ( $question_type === 'fill_in_the_blank' ) {
												$count_dash_fields            = substr_count( $answer_title, '{dash}' );
												$object_as->count_dash_fields = $count_dash_fields;


											} elseif ( $question_type === 'ordering' ) {
												if ( $answer->answer_view_format === 'image' || $answer->answer_view_format === 'text_image' ) {
													$object_as->image_url = wp_get_attachment_image_url( $answer->image_id, 'full' );
												}

											}
											$answer_arr[] = $object_as;


										}
										$question->answers = $answer_arr;
									}
										/**
										 * Question type matchind and image matching
										 */
										if ($question_type === 'matching' || $question_type === 'image_matching'){
										    $rand_answers = tutor_utils()->get_answers_by_quiz_question($question->question_id, true);
										    $rand_answers_arr = [];

													foreach ($rand_answers as $rand_answer){
														$object_ra              = new \stdClass();
															if ($question_type === 'matching'){
																$object_ra->answer_title =  wp_strip_all_tags(stripslashes($rand_answer->answer_two_gap_match));
															}else{
																$object_ra->answer_title =  wp_strip_all_tags(stripslashes($rand_answer->answer_title));

															}
														$object_ra->answer_id = $rand_answer->answer_id;

														$rand_answers_arr [] = $object_ra;

													}
											$question->rand_answers = $rand_answers_arr;

													foreach ($answers as $answer){
														$object_sa              = new \stdClass();
														$object_sa->attempt_id  = $is_started_quiz->attempt_id;
														$object_sa->question_id = $question->question_id;


														$object_sa->answer_id  = $answer->answer_id;

																if ($question_type === 'matching') {
																	$object_sa->answer_view_format = $answer->answer_view_format;
																	if ($answer->answer_view_format !== 'image'){

																		$object_sa->answer_title = wp_strip_all_tags(stripslashes($answer->answer_title));
																	}
																	if ($answer->answer_view_format === 'image' || $answer->answer_view_format === 'text_image'){
																		if(intval($answer->image_id)) {

																			$object_sa->image_url = wp_get_attachment_image_url( $answer->image_id, 'full' );
																		}
																		else {
																			$object_sa->image_url = '';
																		}


																	}
																}else{
																	if(intval($answer->image_id)) {

																		$object_sa->image_url = wp_get_attachment_image_url( $answer->image_id, 'full' );
																	}
																	else {
																		$object_sa->image_url = '';
																	}
																}
														$answer_arr[] =$object_sa ;


													}
											$question->answers = $answer_arr;
										}
									}

									/**
									 * For Open Ended Question Type
									 */
									if ($question_type === 'open_ended' || $question_type === 'short_answer'){


										if ($question_type === 'short_answer') {
											$object_sa              = new \stdClass();
											$get_option_meta = tutor_utils()->get_quiz_option($quiz_id);
											$object_sa->has_short_answer_character_limit = false;
											if(isset($get_option_meta['short_answer_characters_limit'])){
												if($get_option_meta['short_answer_characters_limit'] != "" ){
													$object_sa->has_short_answer_character_limit = true;

													$characters_limit = tutor_utils()->avalue_dot('short_answer_characters_limit', $quiz_attempt_info);
													$object_sa->characters_limit = $characters_limit;

												}
											}

											$answer_arr[] =$object_sa ;
											$question->answers = $answer_arr;
										}

										if ($question_type === 'open_ended') {
											$object_sa              = new \stdClass();
											$get_option_meta = tutor_utils()->get_quiz_option($quiz_id);
											$object_sa->has_open_ended_character_limit = false;
											if(isset($get_option_meta['open_ended_answer_characters_limit'])){
												if($get_option_meta['open_ended_answer_characters_limit'] != "" ){
													$object_sa->has_open_ended_character_limit = true;
													$characters_limit = $get_option_meta['open_ended_answer_characters_limit'];
													$object_sa->characters_limit = $characters_limit;

												}
											}
											$answer_arr[] =$object_sa ;
											$question->answers = $answer_arr;

										}
									}
									if ($question_type === 'image_answering'){
									    foreach ($answers as $answer){
												$object_as              = new \stdClass();
										        $object_as->image_id = $answer->image_id;
										    $object_as->attempt_id  = $is_started_quiz->attempt_id;
										    $object_as->question_id = $question->question_id;


										    $object_as->answer_id  = $answer->answer_id;
												if (intval($answer->image_id)){
													$object_as->image_url =  wp_get_attachment_image_url($answer->image_id, 'full');
													$object_as->answer_id =  $answer->answer_id;
													}
										    $answer_arr[] =$object_as ;

											}
										$question->answers = $answer_arr;

									}



						}



			}


			$object->questions = $questions;
		}
		else{
			if ($attempt_remaining > 0 || $attempts_allowed == 0) {

				$array_static['start_quiz'] = __( 'Start Quiz', 'tutor' );
			}


			$object->has_previous_attempt_results = false;
			if ($previous_attempts){
				$object->has_previous_attempt_results = true;
				$passing_grade = tutor_utils()->get_quiz_option($quiz_id, 'passing_grade', 0);
				$object->passing_grade =$passing_grade;
				$static_arr['course_info'] =  __('Course Info', 'tutor');
				$static_arr['correct_answer'] = __('Correct Answer', 'tutor');
				$static_arr['incorrect_answer'] = __('Incorrect Answer', 'tutor');
				$static_arr['earned_marks'] = __('Earned Marks', 'tutor');
				$static_arr['result'] = __('Result', 'tutor');
				$static_arr['question'] = __('Question: ', 'tutor');
				$static_arr['total_marks'] = __('Total Marks: ', 'tutor');
				$static_arr['under_review'] = __('Under Review', 'tutor');
				$static_arr['pass'] = __('Pass', 'tutor');
				$static_arr['fail'] = __('Fail', 'tutor');
				$static_arr['details'] = __('Details', 'tutor');
				$static_arr['view_attempt'] = __('View Attempt', 'tutor-pro');
                $arr_previous = [];

				foreach ( $previous_attempts as $attempt){
				    $object_p = new \stdClass();
					$attempt_action = tutor_utils()->get_tutor_dashboard_page_permalink('my-quiz-attempts/attempts-details/?attempt_id='.$attempt->attempt_id);
					$earned_percentage = $attempt->earned_marks > 0 ? ( number_format(($attempt->earned_marks * 100) / $attempt->total_marks)) : 0;
					$passing_grade = (int) tutor_utils()->get_quiz_option($attempt->quiz_id, 'passing_grade', 0);
					$answers = tutor_utils()->get_quiz_answers_by_attempt_id($attempt->attempt_id);
					$object_p->attempt_id  = $attempt->attempt_id ;
					$object_p->attempt_action = $attempt_action;
					$object_p->earned_percentage = $earned_percentage;
					$object_p->passing_grade = $passing_grade;
					$object_p->attempt_permalink = get_the_permalink($attempt->course_id);
					$object_p->attempt_title = get_the_title($attempt->course_id);
					$object_p->date =  date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($attempt->attempt_ended_at));
					$object_p->total_answers =  count($answers);
					$object_p->total_marks=   $attempt->total_marks;
					$object_p->total_questions =  $attempt->total_questions;

					if ( $passing_grade > 0 ) {
						$pass_marks = ( $attempt->total_marks * $passing_grade ) / 100;
					} else {
						$pass_marks = 0;
					}
					if ( $pass_marks > 0 ) {
						$object_p->pass_marks = number_format_i18n( $pass_marks, 2 );
					} else {
						$object_p->pass_marks =  0;
					}
					if ( $passing_grade > 0 ) {
						$object_p->pass_marks =  "({$passing_grade}%)";
					} else {
						$object_p->pass_marks =  "(0%)";
					}
					    $correct = 0;
					    $incorrect = 0;
							if(is_array($answers) && count($answers) > 0) {

								foreach ($answers as $answer){
									if ( (bool) isset( $answer->is_correct ) ? $answer->is_correct : '' ) {
										$correct++;
									} else {
										if ($answer->question_type === 'open_ended' || $answer->question_type === 'short_answer'){
										} else {
											$incorrect++;
										}
									}
								}
							}
					$object_p->correct = $correct;
					$object_p->incorrect = $incorrect;
					$object_p->earnmarks = $attempt->earned_marks . "({$earned_percentage}%)";
					$object_p->attempt_status = $attempt->attempt_status;
					 if ($attempt->attempt_status === 'review_required'){
						 $object_p->under_review = true;
					 }else{
						 $object_p->under_review = false;

					}

					 if($attempt->earned_marks >= $passing_grade){
						 $object_p->result = 'Pass';
					 }
					 else {
						 $object_p->result = 'Fail';
					 }


					$arr_previous[] = $object_p;
				}

				$object->previous_attempt_results = $arr_previous;


			}
        }

		$content_id = tutils()->get_post_id((int)$request['lession_id']);
		$contents = tutils()->get_course_prev_next_contents_by_id($content_id);
		$object->previous_id = $contents->previous_id;
		$object->next_id = $contents->next_id;


		$object->static = $static_arr;
		return $object;
	}

	public function is_started_quiz( $quiz_id = 0,$user_id = 0 ) {
		global $wpdb;

		$quiz_id = $this->get_post_id( $quiz_id );


		$is_started = $wpdb->get_row( $wpdb->prepare(
			"SELECT *
			FROM 	{$wpdb->prefix}tutor_quiz_attempts
			WHERE 	user_id =  %d
					AND quiz_id = %d
					AND attempt_status = %s;
			",
			$user_id,
			$quiz_id,
			'attempt_started'
		) );

		return $is_started;
	}

	public function get_post_id( $post_id = 0) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
			if ( ! $post_id ) {
				return false;
			}
		}

		return $post_id;
	}

	/**
	 * @param int $quiz_id
	 *
	 * @return array|null|object
	 *
	 * Get random questions by quiz
	 */
	public function get_random_questions_by_quiz( $quiz_id = 0, $user_id = 0 ) {
		global $wpdb;

		$quiz_id = $this->get_post_id( $quiz_id );
		$attempt = $this->is_started_quiz( $quiz_id ,$user_id);

		$total_questions = (int) $attempt->total_questions;

		if ( ! $attempt ) {
			return false;
		}

		$questions_order = tutor_utils()->get_quiz_option( get_the_ID(), 'questions_order', 'rand' );

		$order_by = "";
		if ( $questions_order === 'rand' ) {
			$order_by = "ORDER BY RAND()";
		} elseif ( $questions_order === 'asc' ) {
			$order_by = "ORDER BY question_id ASC";
		} elseif ( $questions_order === 'desc' ) {
			$order_by = "ORDER BY question_id DESC";
		} elseif ( $questions_order === 'sorting' ) {
			$order_by = "ORDER BY question_order ASC";
		}

		$limit = '';
		if ( $total_questions ) {
			$limit = "LIMIT {$total_questions} ";
		}

		$questions = $wpdb->get_results( $wpdb->prepare(
			"SELECT *
			FROM 	{$wpdb->prefix}tutor_quiz_questions
			WHERE 	quiz_id = %d
			{$order_by}
			{$limit}
			",
			$quiz_id
		) );

		return $questions;
	}


	/**
	 *
	 * Mark lesson completed
	 *
	 * @since v.1.0.0
	 */
	public function mark_lesson_complete($request,$user_id){


		$lesson_id = (int) sanitize_text_field($request['lesson_id']);
		/**
		 * Marking lesson at user meta, meta format, _tutor_completed_lesson_id_{id} and value = tutor_time();
		 */
		tutor_utils()->mark_lesson_complete($lesson_id,$user_id);




	}

	public function tutor_start_assignment($request,$user) {
		$object = new \stdClass();
		global $wpdb;

		$assignment_id     = (int)sanitize_text_field($request['assignment_id']);

		$date              = date("Y-m-d H:i:s");


		$course_id = get_post_meta($assignment_id, '_tutor_course_id_for_assignments', true);


		$data = apply_filters('tutor_assignment_start_submitting_data', array(
			'comment_post_ID'  => $assignment_id,
			'comment_author'   => $user->user_login,
			'comment_date'     => $date, //Submit Finished
			'comment_date_gmt' => $date, //Submit Started
			'comment_approved' => 'submitting', //submitting, submitted
			'comment_agent'    => 'TutorLMSPlugin',
			'comment_type'     => 'tutor_assignment',
			'comment_parent'   => $course_id,
			'user_id'          => $user->ID,
		));

		$wpdb->insert($wpdb->comments, $data);
		$comment_id = (int)$wpdb->insert_id;
		$object->comment_id  = $comment_id ;

		$object->data = $this->tutor_assignment_submit($request,$user->ID);
		return $object;


	}


	public function tutor_assignment_submit($request,$user_id) {

		$object = new \stdClass();
		global $wpdb;
		$assignment_id        = (int)sanitize_text_field($request['assignment_id']);
		$assignment_answer    = wp_kses_post($request['assignment_answer']);
		$allowd_upload_files  = (int)tutor_utils()->get_assignment_option($assignment_id, 'upload_files_limit');
		$assignment_submit_id = tutor_utils()->is_assignment_submitting($assignment_id,$user_id);
		$object->assignment_id = $assignment_submit_id ;
		$date = date("Y-m-d H:i:s");
		$data = apply_filters('tutor_assignment_submit_updating_data', array(
			'comment_content'  => $assignment_answer,
			'comment_date'     => $date, //Submit Finished
			'comment_approved' => 'submitted', //submitting, submitted

		));
		$object->data = $data;

		if ($allowd_upload_files) {

			if(isset($request['attachments_upload']))
			{
				update_comment_meta($assignment_submit_id, 'uploaded_attachments', json_encode($request['attachments_upload']));
			}

		}

		$wpdb->update($wpdb->comments, $data, array(
			'comment_ID' => $assignment_submit_id
		));

		return $object;


	}


	public function handle_assignment_attachment_uploads($assignment_id = 0) {
		$object = new \stdClass();
		if (!$assignment_id) {
			$object->type =0 ;
			$object->msg ='No assignment' ;
			return $object;
		}

		if (!function_exists('wp_handle_upload')) {
			require_once (ABSPATH . 'wp-admin/includes/file.php');
		}

		$attached_files = array();


		if (!empty($_FILES["attached_assignment_files"])) {
			$files          = $_FILES["attached_assignment_files"];
			$max_size_mb    = (int)tutor_utils()->get_assignment_option($assignment_id, 'upload_file_size_limit', 2);


			$file_size      = $files['size'];
			$size_in_mb     = round($file_size / (1024 * 1024));

				if ($size_in_mb > $max_size_mb) {
					$object->type =0 ;
					$object->msg =sprintf(__('Maximum attachment upload size allowed is %d MB', 'tutor-pro') , $max_size_mb);

					return $object;

				}



				$file             = array(
						'name'                  => $files['name'],
						'type'                  => $files['type'],
						'tmp_name'                  => $files['tmp_name'],
						'error'                  => $files['error'],
						'size'                  => $files['size']
					);

					$upload_overrides = array(
						'test_form'                  => false
					);
					$movefile         = wp_handle_upload($file, $upload_overrides);

					if ($movefile && !isset($movefile['error'])) {
						$file_path        = $movefile['file'];
						unset($movefile['file']);
						$upload_dir    = wp_get_upload_dir();

						$file_sub_path = str_replace(trailingslashit($upload_dir['basedir']) , '', $file_path);
						$file_name     = str_replace(trailingslashit($upload_dir['path']) , '', $file_path);

						$movefile['uploaded_path']               = $file_sub_path;
						$movefile['name']               = $file_name;

						$attached_files[]               = $movefile;
					} else {
						/**
						 * Error generated by _wp_handle_upload()
						 * @see _wp_handle_upload() in wp-admin/includes/file.php
						 */
						$object->type =0 ;
						$object->msg =sprintf(__('Maximum attachment upload size allowed is %d MB', 'tutor-pro') , $max_size_mb);
						return $object;

					}


		}
		$object->type = 1;
		$object->attached_files =$attached_files;

		return $object;
	}

	/**
	 *
	 * Start Quiz from here...
	 *
	 * @since v.1.0.0
	 */

	public function start_the_quiz($request,$user_id){

		$object =  new \stdClass();
		global $wpdb;


		$user = get_userdata($user_id);

		$quiz_id = (int) sanitize_text_field($request['quiz_id']);

		$quiz = get_post($quiz_id);
		$course = tutor_utils()->get_course_by_quiz($quiz_id);
		if ( empty($course->ID)){
			$object->type = 0;
			$object->msg = 'There is something went wrong with course, please check if quiz attached with a course';
			return $object;
		}


		$date = date("Y-m-d H:i:s", tutor_time());

		$tutor_quiz_option = (array) maybe_unserialize(get_post_meta($quiz_id, 'tutor_quiz_option', true));
		$attempts_allowed = tutor_utils()->get_quiz_option($quiz_id, 'attempts_allowed', 0);

		$time_limit = tutor_utils()->get_quiz_option($quiz_id, 'time_limit.time_value');
		$time_limit_seconds = 0;
		$time_type = 'seconds';
		if ($time_limit){
			$time_type = tutor_utils()->get_quiz_option($quiz_id, 'time_limit.time_type');

			switch ($time_type){
				case 'seconds':
					$time_limit_seconds = $time_limit;
					break;
				case 'minutes':
					$time_limit_seconds = $time_limit * 60;
					break;
				case 'hours':
					$time_limit_seconds = $time_limit * 60 * 60;
					break;
				case 'days':
					$time_limit_seconds = $time_limit * 60 * 60 * 24;
					break;
				case 'weeks':
					$time_limit_seconds = $time_limit * 60 * 60 * 24 * 7;
					break;
			}
		}

		$max_question_allowed = tutor_utils()->max_questions_for_take_quiz($quiz_id);
		$tutor_quiz_option['time_limit']['time_limit_seconds'] = $time_limit_seconds;

		$attempt_data = array(
			'course_id'                 => $course->ID,
			'quiz_id'                   => $quiz_id,
			'user_id'                   => $user_id,
			'total_questions'           => $max_question_allowed,
			'total_answered_questions'  => 0,
			'attempt_info'              => maybe_serialize($tutor_quiz_option),
			'attempt_status'            => 'attempt_started',
			'attempt_ip'                => tutor_utils()->get_ip(),
			'attempt_started_at'        => $date,
		);

		$wpdb->insert($wpdb->prefix.'tutor_quiz_attempts', $attempt_data);
		$attempt_id = (int) $wpdb->insert_id;

		$object->attempt_id = $attempt_id;
		$object->type = 1;
		$object->attempt_data = $attempt_data;
		return $object;

	}


	public function answering_quiz($request,$user_id){
		$object = new \stdClass();
		global $wpdb;

		$attempt_id = (int) sanitize_text_field($request['attempt_id']);
		$attempt = tutor_utils()->get_attempt($attempt_id);
		if(isset($request['quiz_question_ids']) && $request['quiz_question_ids']!=''){
			$question = explode(',',$request['quiz_question_ids']);



			$total_question_marks = $wpdb->get_var("SELECT SUM(question_mark) FROM {$wpdb->prefix}tutor_quiz_questions WHERE question_id IN({$request['quiz_question_ids']}) ;");
				$wpdb->update($wpdb->prefix.'tutor_quiz_attempts', array('total_marks' =>$total_question_marks ), array('attempt_id' => $attempt_id ));

			if ( ! $attempt || $user_id != $attempt->user_id){
				$object->type = 0 ;
				$object->msg = ('Operation not allowed, attempt not found or permission denied');
				return $object;
			}


			$total_marks = 0;
			$review_required = false;
			$quiz_answers = 0;

			foreach ($question as $question_id ){
				$question      = tutor_utils()->get_quiz_question_by_id( $question_id );
				$question_type = $question->question_type;

				$is_answer_was_correct = false;
				$given_answer          = '';

				if ( $question_type === 'true_false' || $question_type === 'single_choice' ) {

					if(!is_numeric((int)$request[$question_id]) || ! isset($request[$question_id])) {
						$object->type = 0 ;
						$object->msg = ('Answer not valid');
						return $object;

					}

					$given_answer          = (int)$request[$question_id];

					$is_answer_was_correct = (bool) $wpdb->get_var( $wpdb->prepare( "SELECT is_correct FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE answer_id = %d ", (int)$request[$question_id] ) );
					$quiz_answers += 1;


				}
				elseif ( $question_type === 'multiple_choice' ) {

					$given_answer =  explode(',',$request[$question_id]);



					$given_answer = array_filter( $given_answer, function($id) {
						return is_numeric((int)$id) && (int)$id>0;
					} );
					$quiz_answers += count($given_answer);

					$get_original_answers = (array) $wpdb->get_col($wpdb->prepare(
						"SELECT 
									answer_id 
								FROM 
									{$wpdb->prefix}tutor_quiz_question_answers 
								WHERE 
									belongs_question_id = %d 
									AND belongs_question_type = %s 
									AND is_correct = 1 ;
								",
						$question->question_id,
						$question_type
					) );



					if (count(array_diff($get_original_answers, $given_answer)) === 0 && count($get_original_answers) === count($given_answer)) {
						$is_answer_was_correct = true;
					}
					$given_answer = maybe_serialize( $given_answer );




				}
				elseif ( $question_type === 'fill_in_the_blank' ) {
					$answers =  explode(',',$request[$question_id]);

					$given_answer = (array) array_map( 'sanitize_text_field', $answers );
					$quiz_answers += count($given_answer);



					$given_answer = maybe_serialize( $given_answer );

					$get_original_answer = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tutor_quiz_question_answers WHERE belongs_question_id = %d AND belongs_question_type = %s ;", $question->question_id, $question_type ) );
					$gap_answer          = (array) explode( '|', $get_original_answer->answer_two_gap_match );


					$gap_answer = array_map( 'sanitize_text_field', $gap_answer );
					if ( strtolower($given_answer) == strtolower(maybe_serialize( $gap_answer )) ) {
						$is_answer_was_correct = true;
					}

				}
				elseif ( $question_type === 'open_ended' || $question_type === 'short_answer' ) {
					$review_required = true;
					$given_answer = wp_kses_post( $request[$question_id] );
					$quiz_answers += 1;

				}
				elseif ( $question_type === 'ordering' || $question_type === 'matching' || $question_type === 'image_matching' ) {
					$answers =  explode(',',$request[$question_id]);

					$given_answer = (array) array_map( 'sanitize_text_field', $answers );
					$quiz_answers += count($given_answer);

					$given_answer = maybe_serialize( $given_answer );

					$get_original_answers = (array) $wpdb->get_col($wpdb->prepare(
						"SELECT answer_id 
								FROM {$wpdb->prefix}tutor_quiz_question_answers 
								WHERE belongs_question_id = %d AND belongs_question_type = %s ORDER BY answer_order ASC ;", $question->question_id, $question_type));

					$get_original_answers = array_map( 'sanitize_text_field', $get_original_answers );


					if ( $given_answer == maybe_serialize( $get_original_answers ) ) {
						$is_answer_was_correct = true;
					}


				}
				elseif ( $question_type === 'image_answering' ) {
					$questions_ia = explode(',',$request[$question_id]['questions']);
					$answers_ia = explode(',',$request[$question_id]['answer']);
					$image_inputs = array();
					$i =0;
					foreach ($questions_ia as $item_id){
						$image_inputs[$item_id] =$answers_ia [$i];
						$i++;
					}

					$image_inputs          = (array) array_map( 'sanitize_text_field', $image_inputs );
					$quiz_answers += count($image_inputs);
					$given_answer          = maybe_serialize( $image_inputs );
					$is_answer_was_correct = false;

					$db_answer = $wpdb->get_col($wpdb->prepare(
						"SELECT answer_title 
								FROM {$wpdb->prefix}tutor_quiz_question_answers 
								WHERE belongs_question_id = %d AND belongs_question_type = 'image_answering' ORDER BY answer_order asc ;", $question_id));

					if ( is_array( $db_answer ) && count( $db_answer ) ) {
						$is_answer_was_correct = ( strtolower( maybe_serialize( array_values( $image_inputs ) ) ) == strtolower( maybe_serialize( $db_answer ) ) );
					}

				}

				$question_mark = $is_answer_was_correct ? $question->question_mark : 0;
				$total_marks   += $question_mark;

				$answers_data = array(
					'user_id'         => $user_id,
					'quiz_id'         => $attempt->quiz_id,
					'question_id'     => $question_id,
					'quiz_attempt_id' => $attempt_id,
					'given_answer'    => $given_answer,
					'question_mark'   => $question->question_mark,
					'achieved_mark'   => $question_mark,
					'minus_mark'      => 0,
					'is_correct'      => $is_answer_was_correct ? 1 : 0,
				);


				if($question_type==="open_ended" || $question_type ==="short_answer")
				{
					$answers_data['is_correct'] = NULL;
				}


				$wpdb->insert( $wpdb->prefix . 'tutor_quiz_attempt_answers', $answers_data );



			}

			$attempt_info = array(
				'total_answered_questions'  => $quiz_answers,
				'earned_marks'              => $total_marks,
				'attempt_status'            => 'attempt_ended',
				'attempt_ended_at'          => date("Y-m-d H:i:s", tutor_time()),
			);

			if ($review_required){
				$attempt_info['attempt_status'] = 'review_required';
			}



			$wpdb->update($wpdb->prefix.'tutor_quiz_attempts', $attempt_info, array('attempt_id' => $attempt_id));


			$object->type =1;
			$object->attempt_id = $attempt_id;
			return $object;

		}
		else {
			$object->type =0;
			$object->msg = 'invalid';
			return $object;

		}




	}

	public function tutor_add_answer_bqa($request,$user_id){
		$object = new \stdClass();
		global $wpdb;

		$answer = wp_kses_post($request['answer']);
		if ( ! $answer){
			$object->type =0;
			$object->msg = __('Please write answer', 'tutor');
			return $object;

		}

		$question_id = (int) sanitize_text_field($request['question_id']);
		$question = tutor_utils()->get_qa_question($question_id);

		$user = get_userdata($user_id);
		$date = date("Y-m-d H:i:s", tutor_time());

		if(!$this->has_enrolled_content_access('qa_question', $question_id,$user_id)) {
			$object->type =0;
			$object->msg = __('Access Denied', 'tutor');
			return $object;

		}


		$data = apply_filters('tutor_add_answer_data', array(
			'comment_post_ID'   => $question->comment_post_ID,
			'comment_author'    => $user->user_login,
			'comment_date'      => $date,
			'comment_date_gmt'  => get_gmt_from_date($date),
			'comment_content'   => $answer,
			'comment_approved'  => 'approved',
			'comment_agent'     => 'TutorLMSPlugin',
			'comment_type'      => 'tutor_q_and_a',
			'comment_parent'    => $question_id,
			'user_id'           => $user_id,
		));

		$wpdb->insert($wpdb->comments, $data);
		$comment_id = (int) $wpdb->insert_id;

		$object->type =1;
		$object->msg =__('Answer has been added successfully', 'tutor');
		$object->comment_id =$comment_id ;
		return $object;

	}

	public function tutor_ask_question_bqa($request,$user_id){
		$object = new \stdClass();


		global $wpdb;

		$course_id = (int) sanitize_text_field($request['tutor_course_id']);
		$question_title = sanitize_text_field($request['question_title']);
		$question = wp_kses_post($request['question']);

		if(!$this->has_enrolled_content_access('course', $course_id,$user_id)) {

			$object->type =0;
			$object->msg = __('Access Denied', 'tutor');
			return $object;

		}

		if (empty($question) || empty($question_title)){
			$object->course_id =$course_id;
			$object->user_id =$user_id;
			$object->question_title =$question_title;
			$object->question =$question;
			$object->type =0;
			$object->msg = __('Empty question title or body', 'tutor');
			return $object;

		}


		$user = get_userdata($user_id);
		$date = date("Y-m-d H:i:s", tutor_time());


		$data = apply_filters('tutor_add_question_data', array(
			'comment_post_ID'   => $course_id,
			'comment_author'    => $user->user_login,
			'comment_date'      => $date,
			'comment_date_gmt'  => get_gmt_from_date($date),
			'comment_content'   => $question,
			'comment_approved'  => 'waiting_for_answer',
			'comment_agent'     => 'TutorLMSPlugin',
			'comment_type'      => 'tutor_q_and_a',
			'user_id'           => $user_id,
		));

		$wpdb->insert($wpdb->comments, $data);
		$comment_id = (int) $wpdb->insert_id;

		if ($comment_id){
			$result = $wpdb->insert( $wpdb->commentmeta, array(
				'comment_id' => $comment_id,
				'meta_key' => 'tutor_question_title',
				'meta_value' => $question_title
			) );
		}
		$object->type =1;
		$object->msg =__('Ask Question has been added successfully', 'tutor');
		$object->comment_id =$comment_id ;
		return $object;

	}


	/**
	 * @return bool
	 *
	 * @since v1.7.9
	 *
	 * Check if user has access for content like lesson, quiz, assignment etc.
	 */
	public function has_enrolled_content_access( $content, $object_id=0, $user_id=0 ) {



		$course_id = tutils()->get_course_id_by( $content, $object_id );
		$course_content_access = (bool) get_tutor_option( 'course_content_access_for_ia' );

		do_action( 'tutor_before_enrolment_check', $course_id, $user_id );


		$edumall_detail_controler = Edumall_Tutor_Detail_Controller::instance();

		if ( $edumall_detail_controler->is_enrolled( $course_id, $user_id ) ) {

			return true;
		}
		if ( $course_content_access && ( $this->current_user_can( 'administrator' ) || $this->current_user_can( tutor()->instructor_role ) ) ) {

			return true;
		}
		//Check Lesson edit access to support page builders (eg: Oxygen)
		if ( $this->current_user_can(tutor()->instructor_role) && tutils()->has_lesson_edit_access() ) {

			return true;
		}

		return false;
	}

	public function current_user_can( $user_id,$capability, ...$args ) {
		$current_user = get_user_by($user_id);

		if ( empty( $current_user ) ) {
			return false;
		}

		return $current_user->has_cap( $capability, ...$args );
	}



}