<?php
/**
 * Plugin Name: Edumall Addons
 * Plugin URI: https://edumall.thememove.com
 * Description: A collection of features, widgets and more for Edumall theme.
 * Author: ThemeMove
 * Author URI: https://thememove.com
 * Version: 1.1.0
 * Text Domain: edumall-addons
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

$current_theme = wp_get_theme();

if ( ! empty( $current_theme['Template'] ) ) {
	$current_theme = wp_get_theme( $current_theme['Template'] );
}

define( 'EDUMALL_ADDONS_DIR', plugin_dir_path( __FILE__ ) );
define( 'EDUMALL_ADDONS_URL', plugin_dir_url( __FILE__ ) );
define( 'EDUMALL_ADDONS_VERSION', '1.1.0' );
define( 'EDUMALL_ADDONS_THEME_NAME', $current_theme['Name'] );
define( 'EDUMALL_ADDONS_ASSETS_URI', EDUMALL_ADDONS_URL . '/assets' );

/**
 * Entry
 */
if ( 'edumall' === $current_theme->template ) {
	class Edumall_Addons {

		protected static $instance = null;

		public function __construct() {
		}

		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function initialize() {
			add_action( 'plugins_loaded', [ $this, 'load_text_domain' ] );

			$this->includes();
		}

		public function load_text_domain() {
			load_plugin_textdomain( 'edumall-addons', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Load files.
		 */
		public function includes() {
			require_once EDUMALL_ADDONS_DIR . '/includes/general-functions.php';
			require_once EDUMALL_ADDONS_DIR . '/includes/class-debug.php';
			require_once EDUMALL_ADDONS_DIR . '/i18n/countries.php';
			require_once EDUMALL_ADDONS_DIR . '/tutor/class-entry.php';
			require_once EDUMALL_ADDONS_DIR . '/wp-events-manager/class-entry.php';
			require_once EDUMALL_ADDONS_DIR . '/includes/class-cpt-faqs.php';
		}
	}

	Edumall_Addons::instance()->initialize();
}

function edumall_addons_activate() {
	$args = array(
		'hierarchical'      => false,
		'show_ui'           => false,
		'show_in_nav_menus' => false,
		'query_var'         => is_admin(),
		'rewrite'           => false,
		'public'            => false,
	);

	register_taxonomy( 'course-visibility', 'courses', $args );

	$taxonomies = array(
		'course-visibility' => array(
			'featured',
			'rated-1',
			'rated-2',
			'rated-3',
			'rated-4',
			'rated-5',
		),
	);

	foreach ( $taxonomies as $taxonomy => $terms ) {
		foreach ( $terms as $term ) {
			if ( ! get_term_by( 'name', $term, $taxonomy ) ) { // @codingStandardsIgnoreLine.
				wp_insert_term( $term, $taxonomy );
			}
		}
	}

	$custom_post_type_permission = array(
		'edit_faq',
		'edit_faqs',
		'edit_other_faqs',
		'publish_faqs',
		'read_faq',
		'read_private_faqs',
		'delete_faq',
	);

	$administrator = get_role( 'administrator' );
	if ( $administrator ) {
		foreach ( $custom_post_type_permission as $cap ) {
			$administrator->add_cap( $cap );
		}
	}
}

register_activation_hook( __FILE__, 'edumall_addons_activate' );
