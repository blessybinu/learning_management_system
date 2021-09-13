<?php

class Insight_CMB2_Type_Slider {

	public function __construct() {
		add_action( 'cmb2_render_slider', array( $this, 'cmb2_render_slider' ), 10, 5 );
		add_action( 'admin_enqueue_scripts', array( $this, 'setup_admin_scripts' ) );
	}

	public function setup_admin_scripts() {
		wp_enqueue_script( 'cmb2-slider-js', plugins_url( 'js/cmb2-slider.js', __FILE__ ) );
	}

	/**
	 * Render HTML
	 *
	 * @param $field
	 * @param $field_escaped_value
	 * @param $field_object_id
	 * @param $field_object_type
	 * @param $field_type_object
	 */
	public function cmb2_render_slider( $field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object ) {

		$slider = '<div class="insight-cmb2-slider"></div>';
		$slider .= $field_type_object->input( array(
			'type'       => 'hidden',
			'class'      => 'cmb2-slider-value',
			'readonly'   => 'readonly',
			'data-start' => abs( $field_escaped_value ),
			'data-min'   => $field->min(),
			'data-step'  => $field->step(),
			'data-max'   => $field->max(),
			'desc'       => '',
		) );

		$slider .= '<span class="cmb2-slider-value-display">' . $field->value_label() . ' <span class="cmb2-slider-value-text"></span></span>';
		$slider .= $field_type_object->_desc( true );
		echo $slider;
	}
}

new Insight_CMB2_Type_Slider();