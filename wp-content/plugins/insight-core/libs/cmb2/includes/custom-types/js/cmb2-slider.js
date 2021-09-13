(
	function( $ ) {
		'use strict';
		$( document ).ready( function() {
			$( '.cmb-type-slider' ).each( function() {
				var $this       = $( this );
				var $value      = $this.find( '.cmb2-slider-value' );
				var $slider     = $this.find( '.insight-cmb2-slider' );
				var $text       = $this.find( '.cmb2-slider-value-text' );
				var slider_data = $value.data();

				$slider.slider( {
					range: 'min',
					value: slider_data.start,
					min: slider_data.min,
					step: slider_data.step,
					max: slider_data.max,
					slide: function( event, ui ) {
						$value.val( ui.value );
						$text.text( ui.value );
					}
				} );

				// Initiate the display
				$value.val( $slider.slider( 'value' ) );
				$text.text( $slider.slider( 'value' ) );

			} );
		} );
	}
)( jQuery );

jQuery.noConflict();