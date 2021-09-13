jQuery( document ).ready( function() {
	var data = {
		action: 'edumall_course_term_set_views',
		term_id: edumallAddons.term_id,
		edumall_nonce: edumallAddons.nonce
	};

	jQuery.post( edumallAddons.ajax_url, data, function( response ) {

	} );
} );
