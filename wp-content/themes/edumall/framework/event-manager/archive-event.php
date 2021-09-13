<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Edumall_Archive_Event' ) ) {
	class Edumall_Archive_Event extends Edumall_Event {

		protected static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function initialize() {
			add_filter( 'edumall_title_bar_heading_text', [ $this, 'archive_title_bar_heading' ] );

			add_filter( 'tp_event_pagination_args', [ $this, 'change_pagination_args' ] );

			add_filter( 'body_class', [ $this, 'body_class' ] );
		}

		public function archive_title_bar_heading( $text ) {
			if ( $this->is_archive() ) {
				$text = Edumall::setting( 'event_archive_title_bar_title' );
			}

			return $text;
		}

		public function change_pagination_args( $args ) {
			$args['prev_text'] = Edumall_Templates::get_pagination_prev_text();
			$args['next_text'] = Edumall_Templates::get_pagination_next_text();

			return $args;
		}

		public function body_class( $classes ) {
			if ( $this->is_archive() ) {
				$layout = Edumall::setting( 'event_archive_style' );

				$classes[] = 'archive-event-style-' . $layout;

				$filtering_bar_on = Edumall::setting( 'event_archive_filtering' );

				if ( '1' === $filtering_bar_on ) {
					$classes[] = 'page-has-filtering-bar';
				}
			}

			return $classes;
		}
	}
}
