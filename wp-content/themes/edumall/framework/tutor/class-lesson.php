<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Edumall_Lesson' ) ) {
	class Edumall_Lesson {

		protected $video       = null;
		protected $lesson_type = null;

		public function __construct() {
		}

		public function get_id() {
			return get_the_ID();
		}

		public function get_video() {
			if ( null === $this->video ) {
				$this->video = tutor_utils()->get_video();
			}

			return $this->video;
		}

		public function get_type() {
			if ( null === $this->lesson_type ) {
				$video      = $this->get_video();
				$post_id    = $this->get_id();
				$video_info = Edumall_Tutor::instance()->get_video_info( $video, $post_id );

				$play_time = false;
				if ( $video_info ) {
					$play_time = $video_info->playtime;
				}

				$this->lesson_type = $play_time ? 'video' : 'document';
			}

			return $this->lesson_type;
		}
	}
}

add_action( 'template_redirect', 'edumall_setup_lesson_object' );

function edumall_setup_lesson_object() {
	if ( ! is_singular( 'lesson' ) ) {
		return;
	}

	/**
	 * @var Edumall_Lesson $edumall_lesson
	 */
	global $edumall_lesson;

	$edumall_lesson = new Edumall_Lesson();
}
