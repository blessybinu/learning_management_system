<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'KFF_Select_Field' ) ) {
	class KFF_Select_Field {
		static function template( $field, $post_metas ) {

			$field = wp_parse_args( $field, array(
				'options' => array(),
				'change'  => array(),
				'default' => ''
			) );

			$field['subtitle'] = isset( $field['subtitle'] ) ? '<p class="kungfu-form-sub-title">' . $field['subtitle'] . '</p>' : '';
			$field['desc']     = isset( $field['desc'] ) ? '<p class="kungfu-form-description">' . $field['desc'] . '</p>' : '';

			$value = isset( $post_metas[ $field['id'] ] ) ? esc_attr( $post_metas[ $field['id'] ] ) : $field['default'];

			$list = '';

			foreach ( $field['options'] as $val => $label ) {
				$list .= sprintf( '
          <option value="%s" %s>%s</option>',
					$val,
					selected( $value, $val, false ),
					$label
				);
			}
			$dataChange = '';
			if ( ! empty( $field['change'] ) ) {
				$dataChange = "data-change='" . json_encode( $field['change'], JSON_UNESCAPED_UNICODE ) . "'";
			}

			return sprintf( '<div class="kungfu-form-wrapper">
          <div class="kungfu-form-title">
            <label class="kungfu-form-label" for="%s">%s</label>%s
          </div>
          <div class="kungfu-form-control">
            <select name="%s" id="%s" class="kungfu-form-select" %s>%s</select>
            %s
          </div>
				</div>', $field['id'], $field['title'], $field['subtitle'], $field['id'], $field['id'], $dataChange, $list, $field['desc'] );
		}

		static function enqueue_scripts() {
			wp_enqueue_script( 'kungfu-select', KFF_JS_URL . 'select.js', array(
				'jquery-core'
			), false, true );
		}
	}
}