(
	function( $ ) {
		'use strict';

		$( document ).ready( function() {
			/**
			 * Edition version of edit review form on dashboard.
			 */
			$( document ).on( 'submit', '#custom_tutor_update_review_form', function( e ) {
				e.preventDefault();

				var $that = $( this );
				var review_id = $that.closest( '.tutor-edit-review-modal-wrap ' ).attr( 'data-review-id' );

				var nonce_key = _tutorobject.nonce_key;

				var rating = $that.find( 'input[name="tutor_rating_gen_input"]' ).val();
				var review = $that.find( 'textarea[name="review"]' ).val();
				review = review.trim();

				var json_data = {
					review_id: review_id,
					rating: rating,
					review: review,
					action: 'edumall_update_review_modal' // Custom ajax action
				};
				json_data[ nonce_key ] = _tutorobject[ nonce_key ];

				$.ajax( {
					url: _tutorobject.ajaxurl,
					type: 'POST',
					data: json_data,
					beforeSend: function() {
						$that.find( 'button[type="submit"]' ).addClass( 'tutor-updating-message' );
					},
					success: function( data ) {
						if ( data.success ) {
							// Close the modal.
							$( '.tutor-edit-review-modal-wrap' ).removeClass( 'show' );
							location.reload( true );
						}
					},
					complete: function() {
						$that.find( 'button[type="submit"]' ).removeClass( 'tutor-updating-message' );
					}
				} );
			} );

			var withdrawMethodInput = $( '.withdraw-method-select-input' );

			withdrawMethodInput.on( 'change', function( e ) {
				$( '.withdraw-account-save-btn-wrap' ).show();
			} );

			withdrawMethodInput.each( function() {
				var $that = $( this );
				if ( $that.is( ':checked' ) ) {
					$( '.withdraw-account-save-btn-wrap' ).show();
				}
			} );
		} );
	}( jQuery )
);
