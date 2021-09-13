<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'KFF_Text_Field' ) ) {
	class KFF_Text_Field {
		static function template( $field, $post_metas ) {

			$field = wp_parse_args( $field, array(
				'title'   => '',
				'default' => ''
			) );

			$field['subtitle'] = isset( $field['subtitle'] ) ? '<p class="kungfu-form-sub-title">' . $field['subtitle'] . '</p>' : '';
			$field['desc']     = isset( $field['desc'] ) ? '<p class="kungfu-form-description">' . $field['desc'] . '</p>' : '';

			$value = isset( $post_metas[ $field['id'] ] ) ? esc_attr( $post_metas[ $field['id'] ] ) : $field['default'];

			return sprintf( '<div class="kungfu-form-wrapper">
          <div class="kungfu-form-title">
            <label class="kungfu-form-label" for="%s">%s</label>%s
          </div>
          <div class="kungfu-form-control">
            <input type="text" name="%s" class="kungfu-form-text kungfu-form-control" id="%s" value="%s" />
            %s
          </div>
        </div>', $field['id'], $field['title'], $field['subtitle'], $field['id'], $field['id'], $value, $field['desc'] );
		}
	}
}