<?php
return array(
		"profile_config" => array(
				"profile_url" 						=> "https://*************.com",
				"api_request_location" 				=> "https://*************.com",
		),

		"login_page_settings" => array(
				"logo" => "https://cdn-aws.majestic3.com/images/m3frontend/m3logo_main.svg",
				"tag_line" => "integrate <strong>anywhere</strong>",
				"login_button_text" => "Sign in",
				"forgot_password_link_enabled" => FALSE,
				"forgot_password_link" => "<p class='forgot'><a href='#'>Forgot your password?</a></p>",
		),

		//required to assist api key less login and other operations
		'master_user_account' => array(
			'user' => '********',
			'password' => '********',
			'apikey' => '***********',
		),

		/**
		 * Comment this array to run with NO db installed
		 */
		'frontend_db_config' => array(
				'enabled' => TRUE,
				'database' => 'm3_frontend_data',
				'username' => 'root',
				'password' => 'password',
				'hostname' => '127.0.0.1',
		),

		/**
		 * Comment this array to run with NO db installed and local storage disabled
		 */
		'logged_in_user_settings' => array(
				'storage_enabled' => TRUE,
				'storage' => '\\FrontUsers\\Storage\\UserMySqlStorage',
		),

		/**
		 * Uncomment this block to enable local storage but not using the db details above
		 */
// 		'logged_in_user_settings' => array(
// 				'storage_enabled' => TRUE,
// 				'storage' => '\\FrontUsers\\Storage\\UserFileSystemStorage',
// 		),
);
