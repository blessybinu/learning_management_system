<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Tabs for CMB2 plugins
 *
 * @package   InsightFramework
 */
if ( ! class_exists( 'Insight_CMB2_Type_Tabs' ) ) {

	class Insight_CMB2_Type_Tabs {

		private $setting = array();
		private $object_id = 0;

		/**
		 * CMB2_Tabs constructor.
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'setup_admin_scripts' ) );
			add_action( 'cmb2_render_tabs', array( $this, 'render' ), 10, 5 );
			add_filter( 'cmb2_sanitize_tabs', array( $this, 'save' ), 10, 4 );
		}

		public function setup_admin_scripts() {
			// js
			wp_enqueue_script( 'jquery-ui-core' );// enqueue jQuery UI Core
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_script( 'insight-cmb2-tabs', plugins_url( 'js/cmb2-tabs.js', __FILE__ ) );
			wp_enqueue_script( 'insight-cmb2-cookie', plugins_url( 'js/js.cookie.min.js', __FILE__ ) );
		}

		/**
		 * Hook: Render field
		 *
		 * @param $field_object
		 * @param $escaped_value
		 * @param $object_id
		 * @param $object_type
		 * @param $field_type_object
		 */
		public function render( $field_object, $escaped_value, $object_id, $object_type, $field_type_object ) {
			$this->setting   = $field_object->args( 'tabs' );
			$this->object_id = $object_id;
			// set layout
			$layout = empty( $this->setting['layout'] ) ? 'ui-tabs-horizontal' : "ui-tabs-{$this->setting['layout']}";
			?>
			<div class="insight-cmb2-tabs <?php echo esc_attr( $layout ); ?>">
				<?php // render field
				echo $this->get_tabs();
				?>
			</div>
			<?php
		}

		/**
		 * Render tabs
		 *
		 * @return string
		 */
		public function get_tabs() {
			ob_start();
			?>

			<ul>
				<?php foreach ( $this->setting['tabs'] as $key => $tab ): ?>
					<li>
						<a href="#<?php echo esc_attr( $tab['id'] ); ?>"><?php echo esc_html( $tab['title'] ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php foreach ( $this->setting['tabs'] as $key => $tab ): ?>
				<div id="<?php echo esc_attr( $tab['id'] ); ?>">
					<?php
					// render fields from tab
					$this->render_fields( $this->setting['config'], $tab['fields'], $this->object_id );
					?>
				</div>
			<?php endforeach;

			return ob_get_clean();
		}

		/**
		 * Render fields from tab
		 *
		 * @param $args
		 * @param $fields
		 * @param $object_id
		 */
		public function render_fields( $args, $fields, $object_id ) {

			// set options to cmb2
			$setting_fields = array_merge( $args, array( 'fields' => $fields ) );
			$CMB2           = new \CMB2( $setting_fields, $object_id );
			foreach ( $fields as $key_field => $field ) {
				if ( $CMB2->is_options_page_mb() ) {
					$CMB2->object_type( $args['object_type'] );
				}
				// cmb2 render field
				$CMB2->render_field( $field );
			}
		}

		/**
		 * Hook: Save field values
		 *
		 * @param $override_value
		 * @param $value
		 * @param $post_id
		 * @param $data
		 */
		public static function save( $override_value, $value, $post_id, $data ) {

			foreach ( $data['tabs']['tabs'] as $tab ) {
				$setting_fields = array_merge( $data['tabs']['config'], array( 'fields' => $tab['fields'] ) );
				$CMB2           = new \CMB2( $setting_fields, $post_id );
				if ( $CMB2->is_options_page_mb() ) {
					$cmb2_options = cmb2_options( $post_id );
					$values       = $CMB2->get_sanitized_values( $_POST );
					foreach ( $values as $key => $value ) {
						$cmb2_options->update( $key, $value );
					}
				} else {
					$CMB2->save_fields();
				}
			}
		}
	}

	new Insight_CMB2_Type_Tabs();
}
