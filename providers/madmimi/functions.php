<?php

function madmimi_get_lists( $api_key, $email ) {
	
	$lists_formatted = array( '' => 'Not set' );

	// Make call and add lists if any
	if ( !empty( $api_key ) && !empty ( $email ) ) {

		$args = array(
			'timeout'     => 15,
			'redirection' => 15,
			'headers'     => "Accept: application/json",
		);
		$url = "https://api.madmimi.com/audience_lists/lists.xml?username=$email&api_key=$api_key";

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $lists_formatted;
		}
		
		if ( !empty ( $response['body'] ) ) {
			$body = wp_remote_retrieve_body( $response );
			$xml  = simplexml_load_string( $body );
			//fuck xml
			$json = json_encode($xml);
			$array = json_decode($json, TRUE );
			
			if ( !empty ( $array['list'] )) {
				
				foreach ( $array['list'] as $list ) {
					$lists_formatted[ $list['@attributes'][ 'id' ] ] = $list['@attributes'][ 'name' ];
				}
			}
			
		}
		
	}

	return $lists_formatted;
}

function madmimi_ajax_get_lists() {

	// Validate the API key
	$api_key = K::get_var( 'madmimi_api_key', $_POST );
	$email = K::get_var( 'madmimi_email', $_POST );

	$lists_formatted = array( '' => 'Not set' );

	// Make call and add lists if any
	if ( !empty( $api_key ) && !empty ( $email ) ) {

		$args = array(
			'timeout'     => 15,
			'redirection' => 15,
			'headers'     => "Accept: application/json",
		);
		$url = "https://api.madmimi.com/audience_lists/lists.xml?username=$email&api_key=$api_key";

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $lists_formatted;
		}
		
		if ( !empty ( $response['body'] ) ) {
			$body = wp_remote_retrieve_body( $response );
			$xml  = simplexml_load_string( $body );
			//fuck xml
			$json = json_encode($xml);
			$array = json_decode($json, TRUE );
			
			if ( !empty ( $array['list'] )) {
				
				foreach ( $array['list'] as $list ) {
					$lists_formatted[ $list['@attributes'][ 'id' ] ] = $list['@attributes'][ 'name' ];
				}
			}
			
		}
		
	}

	// Output response and exit
	echo json_encode( $lists_formatted );
	exit;
}

function madmimi_add_user( $settings, $user_data, $list_id ) {
	$form_meta = get_post_meta ( $user_data['form_id'], 'fca_eoi', true );
	$api_key = empty( $form_meta['madmimi_api_key'] ) ? '' : $form_meta['madmimi_api_key'];
	$username = empty( $form_meta['madmimi_email'] ) ? '' : $form_meta['madmimi_email'];
	
	if ( empty ( $api_key ) ) {
		return 'Missing API key';
	}
	
	if ( empty ( $username ) ) {
		return 'Missing API username';
	}
	// Make call and add lists if any
	
	$email = urlencode( K::get_var( 'email', $user_data ) );
	$name = urlencode( K::get_var( 'name', $user_data, '' ) );
	
	$args = array(
		'timeout'     => 15,
		'redirection' => 15,
		'headers'     => "Accept: application/json",
	);
	$url = "https://api.madmimi.com/audience_lists/$list_id/add?email=$email&username=$username&api_key=$api_key";
	
	if ( !empty ( $name ) ) {
		//add name to query
		$url .= "&first_name=$name";
	}

	$response = wp_remote_post( $url, $args );

	if( !is_wp_error( $response ) ) {
		if ( $response['response']['code'] == 200 ) {
			return true;
		} else if( isSet( $response['response']['code']) ) {
			return $response['response']['code'];
		}
	} else {
		echo $response->get_error_code();
	}
	
	return false;
}

function madmimi_string( $def_str ) {

	$strings = array(
		'Form integration' => __( 'Mad Mimi Integration' ),
	);

	return K::get_var( $def_str, $strings, $def_str );
}

function madmimi_admin_notices( $errors ) {
	/* Provider errors can be added here */
	return $errors;
}


function madmimi_integration( $settings ) {

	// Detect free version
	$eoi_free = 'madmimi' === K::get_var( 'provider', $settings );

	global $post;
	$fca_eoi = get_post_meta( $post->ID, 'fca_eoi', true );

	$screen = get_current_screen();
	
	$current_user = wp_get_current_user();
	$user_email = empty ( $current_user->user_email ) ? '' : $current_user->user_email;
	
	// Remember old Mailcihmp settings if we are in a new form
	$last_form_meta = get_option( 'fca_eoi_last_form_meta', '' );
	$suggested_api = empty($last_form_meta['madmimi_api_key']) ? '' : $last_form_meta['madmimi_api_key'];
	$suggested_email = empty($last_form_meta['madmimi_email']) ? $user_email : $last_form_meta['madmimi_email'];
	$suggested_list = empty($last_form_meta['madmimi_list_id']) ? '' : $last_form_meta['madmimi_list_id'];
	
	$api_key = K::get_var( 'madmimi_api_key', $fca_eoi, $suggested_api );
	$email = K::get_var( 'madmimi_email', $fca_eoi, $suggested_email );
	$list = K::get_var( 'madmimi_list_id', $fca_eoi, $suggested_list );
	
	$lists_formatted = madmimi_get_lists( $api_key, $email );
	
	K::fieldset( madmimi_string( 'Form integration' ) ,
		array(
			array( 'input', 'fca_eoi[madmimi_email]',
				array( 
					'class' => 'regular-text',
					'value' => $email,
				),
				array( 'format' => '<p><label>Mad Mimi Account Email<br />:input</label><br /></p>' ),
			),
			
			array( 'input', 'fca_eoi[madmimi_api_key]',
				array( 
					'class' => 'regular-text',
					'value' => $api_key,
				),
				array( 'format' => '<p><label>Secret API key<br />:input</label><br /><em>Go to your <a tabindex="-1" href="https://madmimi.com/user/edit?account_info_tabs=account_info_personal" target="_blank">Mad Mimi Account</a> then click "API" on the right-hand side.</em></p>' ),
			),
			array( 'select', 'fca_eoi[madmimi_list_id]',
				array(
					'class' => 'select2',
					'style' => 'width: 27em;',
				),
				array(
					'format' => '<p id="madmimi_list_id_wrapper"><label>List to subscribe to<br />:select</label></p>',
					'options' => $lists_formatted,
					'selected' => $list,
				),
			),				
		),
		array(
			'id' => 'fca_eoi_fieldset_form_madmimi_integration',
		)
	);

}
