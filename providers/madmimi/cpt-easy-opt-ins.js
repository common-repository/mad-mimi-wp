jQuery( document ).ready( function( $ ) {

	var $api_key = $( '[name="fca_eoi[madmimi_api_key]"]' );
	var $account = $( '[name="fca_eoi[madmimi_email]"]' );
	var $lists = $( '[name="fca_eoi[madmimi_list_id]"]' );
	var $lists_wrapper = $( '#madmimi_list_id_wrapper' );

	madmimi_toggle_fields();

	fca_eoi_provider_status_setup( 'madmimi', $api_key );

	$api_key.bind( 'input', function() {
		if ( ! fca_eoi_provider_is_value_changed( $( this ) ) ) {
			return;
		}

		fca_eoi_provider_status_set( 'madmimi', fca_eoi_provider_status_codes.loading );

		var data = {
			'action': 'fca_eoi_madmimi_get_lists', /* API action name, do not change */
			'madmimi_api_key' : $api_key.val().trim(),
			'madmimi_email' : $account.val(),
		};

		$.post( ajaxurl, data, function( response ) {

			var lists = JSON.parse( response );

			fca_eoi_provider_status_set( 'madmimi', Object.keys(lists).length > 1
				? fca_eoi_provider_status_codes.ok
				: fca_eoi_provider_status_codes.error );

			var $lists = $( '<select class="select2" style="width: 27em;" name="fca_eoi[madmimi_list_id]" >' );

			for ( list_id in lists ) {
				$lists.append( '<option value="' + list_id + '">' + lists[ list_id ] + '</option>' );
			}

			// Set first list as selected
			$( 'option:eq(1)', $lists ).prop( 'selected', true );

			// Replace dropdown with new list of lists, apply Select2 then show
			$( '[name="fca_eoi[madmimi_list_id]"]' ).select2( 'destroy' );
			$( '[name="fca_eoi[madmimi_list_id]"]' ).replaceWith( $lists );
			$( '[name="fca_eoi[madmimi_list_id]"]' ).select2();
			madmimi_toggle_fields();
		} );
	})

	/**
	 * Show/hide some fields if there are/aren't list options
	 *
	 * Don't forget that there is always the option "Not Set", 
	 * so take it into consideration when cheking the number of options
	 */
	function madmimi_toggle_fields() {

		var options = $( 'option', '[name="fca_eoi[madmimi_list_id]"]' );

		if( options.length > 1 ) {
			$()
				.add( $lists_wrapper )
				.show( 'fast' )
			;
		} else {
			$()
				.add( $lists_wrapper )
				.hide( )
			;
		}
	}
});