<?php
defined( 'ABSPATH' ) || exit;

/**
 * Plugin installation and activation for WordPress themes
 */
if ( ! class_exists( 'Edumall_Register_Plugins' ) ) {
	class Edumall_Register_Plugins {

		protected static $instance = null;

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		function initialize() {
			add_filter( 'insight_core_tgm_plugins', array( $this, 'register_required_plugins' ) );
		}

		public function register_required_plugins( $plugins ) {
			/*
			 * Array of plugin arrays. Required keys are name and slug.
			 * If the source is NOT from the .org repo, then source is also required.
			 */
			$new_plugins = array(
				array(
					'name'     => esc_html__( 'Insight Core', 'edumall' ),
					'slug'     => 'insight-core',
					'source'   => $this->get_plugin_source_url( 'insight-core-2.2.4-fpmyA9C40J.zip' ),
					'version'  => '2.2.4',
					'required' => true,
				),
				array(
					'name'     => esc_html__( 'Edumall Addons', 'edumall' ),
					'slug'     => 'edumall-addons',
					'source'   => $this->get_plugin_source_url( 'edumall-addons-1.1.0-PmTV82ScLm.zip' ),
					'version'  => '1.1.0',
					'required' => true,
				),
				array(
					'name'     => esc_html__( 'Elementor', 'edumall' ),
					'slug'     => 'elementor',
					'required' => true,
				),
				array(
					'name'     => esc_html__( 'Elementor Pro', 'edumall' ),
					'slug'     => 'elementor-pro',
					'source'   => $this->get_plugin_source_url( 'elementor-pro-3.3.8-L75XJT8uou.zip' ),
					'version'  => '3.3.8',
					'required' => true,
				),
				array(
					'name'    => esc_html__( 'Revolution Slider', 'edumall' ),
					'slug'    => 'revslider',
					'source'  => $this->get_plugin_source_url( 'revslider-6.5.7-1J3ORlGWze.zip' ),
					'version' => '6.5.7',
				),
				array(
					'name' => esc_html__( 'WP Events Manager', 'edumall' ),
					'slug' => 'wp-events-manager',
				),
				array(
					'name' => esc_html__( 'Video Conferencing with Zoom', 'edumall' ),
					'slug' => 'video-conferencing-with-zoom-api',
				),
				array(
					'name' => esc_html__( 'BuddyPress', 'edumall' ),
					'slug' => 'buddypress',
				),
				array(
					'name' => esc_html__( 'MediaPress', 'edumall' ),
					'slug' => 'mediapress',
				),
				array(
					'name' => esc_html__( 'WordPress Social Login', 'edumall' ),
					'slug' => 'miniorange-login-openid',
				),
				array(
					'name' => esc_html__( 'Contact Form 7', 'edumall' ),
					'slug' => 'contact-form-7',
				),
				array(
					'name' => esc_html__( 'MailChimp for WordPress', 'edumall' ),
					'slug' => 'mailchimp-for-wp',
				),
				array(
					'name' => esc_html__( 'WooCommerce', 'edumall' ),
					'slug' => 'woocommerce',
				),
				array(
					'name' => esc_html__( 'WPC Smart Compare for WooCommerce', 'edumall' ),
					'slug' => 'woo-smart-compare',
				),
				array(
					'name' => esc_html__( 'WPC Smart Wishlist for WooCommerce', 'edumall' ),
					'slug' => 'woo-smart-wishlist',
				),
				array(
					'name'    => esc_html__( 'Insight Swatches', 'edumall' ),
					'slug'    => 'insight-swatches',
					'source'  => $this->get_plugin_source_url( 'insight-swatches-1.2.2-m1gCj4cpuy.zip' ),
					'version' => '1.2.2',
				),
				array(
					'name' => esc_html__( 'WP-PostViews', 'edumall' ),
					'slug' => 'wp-postviews',
				),
				array(
					'name'    => esc_html__( 'Tutor LMS Pro', 'edumall' ),
					'slug'    => 'tutor-pro',
					'source'  => $this->get_plugin_source_url( 'tutor-pro-1.9.7-4rOq5pqzOY.zip' ),
					'version' => '1.9.7',
				),
				/**
				 * Tutor LMS has Setup page after plugin activated.
				 * This made TGA stop activating other plugins after it.
				 * Move it to last activate plugin will resolve this problem.
				 */
				array(
					'name' => esc_html__( 'Tutor LMS', 'edumall' ),
					'slug' => 'tutor',
				),
			);

			$plugins = array_merge( $plugins, $new_plugins );

			return $plugins;
		}

		public function get_plugin_source_url( $file_name ) {
			return 'https://api.thememove.com/download/' . $file_name;
		}
	}

	Edumall_Register_Plugins::instance()->initialize();
}
