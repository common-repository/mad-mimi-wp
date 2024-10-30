<?php

/**
 * Details for the provider Mad Mimi
 */

function provider_madmimi() {

	$provider_id = 'madmimi';

    $eoi_settings = get_option('easy_opt_in_settings');

	return  array(
		'info' => array(
			'id' => 'madmimi',
			'name' => 'Mad Mimi',
		),
		'settings' => array(
			'api_key' => array(
				'title' => 'Mad Mimi API Key',
				'html' => K::input( '{{setting_name}}'
					, array(
						'value' => K::get_var( $provider_id . '_api_key', $eoi_settings ),
						'class' => 'regular-text',
					)
					, array(
						'return' => true,
						'format' => ':input<br /><a tabindex="-1" href="https://madmimi.com/user/edit?account_info_tabs=account_info_personal" target="_blank">Where can I find my API Key?</a>',
					)
				),
			),
		),
	);
}
