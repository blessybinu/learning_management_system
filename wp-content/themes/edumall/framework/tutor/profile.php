<?php
defined( 'ABSPATH' ) || exit;

/**
 * Templates & hooks for Profile page.
 */
if ( ! class_exists( 'Edumall_Tutor_Profile' ) ) {
	class Edumall_Tutor_Profile {

		protected static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function initialize() {
			add_filter( 'body_class', [ $this, 'body_class' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'frontend_scripts' ] );
			add_filter( 'edumall_top_bar_type', [ $this, 'hide_top_bar' ] );
			add_filter( 'edumall_header_type', [ $this, 'hide_header' ] );
			add_filter( 'edumall_title_bar_type', [ $this, 'hide_title_bar' ] );
			add_filter( 'edumall_footer', [ $this, 'hide_footer' ] );
			add_filter( 'edumall_sidebar_1', [ $this, 'hide_sidebar' ] );

			add_action( 'template_redirect', [ $this, 'handle_invalid_users' ], 100 );

			add_filter( 'tutor_user_social_icons', [ $this, 'change_social_icons' ] );
		}

		public function body_class( $classes ) {
			if ( Edumall_Tutor::instance()->is_profile() ) {
				$classes[] = 'dashboard-page';

				if ( ! Edumall::is_handheld() ) {
					$classes[] = 'dashboard-nav-fixed';
				}
			}

			return $classes;
		}

		public function frontend_scripts() {
			if ( Edumall_Tutor::instance()->is_profile() ) {
				wp_enqueue_script( 'edumall-grid-layout' );
			}
		}

		public function hide_top_bar( $type ) {
			if ( Edumall_Tutor::instance()->is_profile() ) {
				return 'none';
			}

			return $type;
		}

		public function hide_header( $type ) {
			if ( Edumall_Tutor::instance()->is_profile() ) {
				return 'none';
			}

			return $type;
		}

		public function hide_title_bar( $type ) {
			if ( Edumall_Tutor::instance()->is_profile() ) {
				return 'none';
			}

			return $type;
		}

		public function hide_footer( $type ) {
			if ( Edumall_Tutor::instance()->is_profile() ) {
				return 'none';
			}

			return $type;
		}

		public function hide_sidebar( $type ) {
			if ( Edumall_Tutor::instance()->is_profile() ) {
				return 'none';
			}

			return $type;
		}

		/**
		 * Show 404 page if given profile not exist.
		 *
		 * @return string
		 */
		public function handle_invalid_users() {
			/**
			 * @var WP_Query $wp_query
			 */
			global $wp_query;

			if ( ! empty( $wp_query->query['tutor_student_username'] ) ) {
				global $wpdb;

				$user_name = sanitize_text_field( $wp_query->query['tutor_student_username'] );

				$user = $wpdb->get_row( "select display_name from {$wpdb->users} WHERE user_login = '{$user_name}' limit 1; " );

				if ( empty( $user->display_name ) ) {
					$wp_query->set_404();
					status_header( 404 );
					get_template_part( 404 );
					exit();
				}
			}
		}

		/**
		 * Update user profile social icons.
		 *
		 * @param $social_icons
		 *
		 * @return mixed
		 */
		public function change_social_icons( $social_icons ) {
			/**
			 * Replace with theme social.
			 */
			$social_icons = Edumall_Helper::get_user_social_networks_support();

			return $social_icons;
		}
	}
}
