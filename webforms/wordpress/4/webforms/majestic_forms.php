<?php
/*
 Copyright (c) 2015 Majestic 3 http://majestic3.com

 Permission is hereby granted, free of charge, to any person obtaining
 a copy of this software and associated documentation files (the
 "Software"), to deal in the Software without restriction, including
 without limitation the rights to use, copy, modify, merge, publish,
 distribute, sublicense, and/or sell copies of the Software, and to
 permit persons to whom the Software is furnished to do so, subject to
 the following conditions:

 The above copyright notice and this permission notice shall be included
 in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/*
Plugin Name: Majestic 3 Web Forms
Plugin URI: http://majestic3.com
Version: 1
Author: J Du Plessis
Description: Display and complete Majestic 3 Web Forms from your Wordpress website
*/
ini_set('display_errors', 1);
//load some classes
require_once "majestic_forms/classes/cls_execute_request.php";
require_once "majestic_forms/classes/cls_forms.php";
require_once "majestic_forms/classes/cls_form_element.php";
require_once "majestic_forms/classes/cls_form_element_predefined.php";

//define config fields
$arr_config_fields = array(
		'm3_api_base_url',
		'm3_api_key',
		'm3_api_username',
		'm3_api_password',
		'm3_form_id',
		'm3_cache_form',
		'm3_cache_form_ttl'
);

/**
 * Some house cleaning functions
 */
register_deactivation_hook( __FILE__, 'majestic_forms_remove' );
register_activation_hook( __FILE__, 'majestic_forms_install' );

/**
 * Create admin section
 */
add_action('admin_menu', 'majestic_forms_plugin_setup_menu');

/**
 * Enable hook to call majestic forms from templates
 * Add do_action('majestic_forms'); to your template or templates files to generate the form
 */
add_action('majestic_forms', 'majestic_form');

/**
 * Adds a form to all pages
 * Uncomment to load on all pages
 * See wordpress docs for available hooks to replace wp_footer to generate for
 */
//add_action( 'wp_footer', 'majestic_form' );

/**
 * Shortcode creates the capability to add forms to content
 * Add [majestic_form_shortcode] in a post for example to have the form generated
 */
add_shortcode('majestic_form_shortcode', 'majestic_form');

/**
 * Register form widget
 * Use the Appearance > Widget section in your website's administration area to allocate the widget to different areas
 */
add_action('widgets_init', function() { register_widget( 'majestic_forms_widget' );});

//monitor config form submission
if (isset($_POST['majestic3_forms_config']))
{
	majestic_forms_plugin_setup_process($_POST);
}//end if

/**
 * Create module option fields in the options table on install
 */
function majestic_forms_install()
{
	add_option('m3_api_base_url', 'https://', '', 'yes');
	add_option('m3_api_key', '', '', 'yes');
	add_option('m3_api_username', '', '', 'yes');
	add_option('m3_api_password', '', '', 'yes');
	add_option('m3_form_id', '0', '', 'yes');
	add_option('m3_cache_form', '1', '', 'yes');
	add_option('m3_cache_form_ttl', '86400', '', 'yes');
}//end function

/**
 * Remove module option fields from the options table on uninstall
 */
function majestic_forms_remove()
{
	global $arr_config_fields;
	foreach ($arr_config_fields as $field)
	{
		delete_option($field);
	}//end foreach
}//end function

/**
 * Deals with presenting and submitting forms
 */
function majestic_form() 
{
	$api_url 			= get_option('m3_api_base_url');
	$api_key 			= get_option('m3_api_key');
	$api_user 			= get_option('m3_api_username');
	$api_pword 			= get_option('m3_api_password');
	$formid 			= get_option('m3_form_id');
	$cache_form 		= get_option('m3_cache_form');
	$cache_form_ttl 	= get_option('m3_cache_form_ttl');
	
	//set cache path
	if (!is_dir(getcwd() . '/wp-content/mjforms_tmp'))
	{
		if (is_writable(getcwd() . '/wp-content/mjforms_tmp/t.txt'))
		{
			mkdir(getcwd() . '/wp-content/mjforms_tmp', 0700);
		}//end if
	}//end if
	$cache_path = getcwd() . '/wp-content/mjforms_tmp/formcache' . $formid . '.json';
	
	$arr_credentials = array(
								'api_base_url' 		=> $api_url . '/api',
								'api_key' 			=> $api_key,
								'api_username' 		=> $api_user, 
								'api_password' 		=> $api_pword,
						);
	
	//load request object and set variables
	$objExecuteRequest = new cls_execute_request();
	
	//set variables load from credentials config file
	$objExecuteRequest->setKeyFromArray($arr_credentials);
	
	//set api url
	$objExecuteRequest->setKey('api_url', $arr_credentials['api_base_url']);
	
	try {
		//set initial api request values
		$objExecuteRequest->setKey('api_url', $arr_credentials['api_base_url']); //object is defined in mod_majestic_forms.php
	
		//load forms model
		$objForm = new cls_forms($objExecuteRequest);
		
		//generate the form
		$objForm = loadMajesticForm(
										$objForm, 
										$formid, 
										array(), 
										$arr_config = array(
															'cache_form' => $cache_form, 
															'cache_form_ttl' => $cache_form_ttl, 
															'cache_path' => $cache_path)
									);
		
		//set data to be populated into form where applicable, in this case only the post data is being used
		if ($_POST)
		{
			$arr_form_data = $_POST;
			//submit form data to the api
			$objForm->submitForm($arr_form_data);
		} else {		
			//this array could be used to prepoulate form elements
			$arr_form_data = array();
		}//end if
		
		//generate form html
		$form_html = $objForm->generateOutput($arr_form_data);
		
		//present the form
		echo $form_html;
	} catch (Exception $e) {
		echo '<fieldset><legend>Error:</legend>';
		echo 	'<pre>' . print_r($e, TRUE) . '</pre>';
		echo '</fieldset>';
	}//end catch
}//end function

/**
 * Connect to the api and request form information.
 * If caching is enabled, local storage will be used to load form data.
 * Caching expires based on the timeout configured in the module's options
 * @param cls_forms $objForm
 * @param int $formid
 * @param array $arr_form_data - Optional
 * @param array $arr_config - Optional
 * @return cls_forms
 */
function loadMajesticForm($objForm, $formid, $arr_form_data = array(), $arr_config = array())
{
	//set cache enabled flag
	if (isset($arr_config['cache_form']) && $arr_config['cache_form'] == 1 && isset($arr_config['cache_form_ttl']) && $arr_config['cache_form_ttl'] > 0)
	{
		$cache_enabled = true;
	} else {
		$cache_enabled = false;
	}//end if

	/**
	 * Load data from cache where enabled
	 */
	if ($cache_enabled === true)
	{
		if (file_exists($arr_config["cache_path"]))
		{
			$json = file_get_contents($arr_config["cache_path"]);
			$obj = json_decode($json);

			//check if cache has expired
			if (time() > ($obj->cache_expires - 10))
			{
				$objFormData = false;
			} else {
				$objFormData = unserialize($obj->data);
			}//end if
		} else {
			$objFormData = false;
		}//end if
	} else {
		//mark form as false for load/reload to take place
		$objFormData = false;
	}//end if

	if ($objFormData === false)
	{
		//connect to the api and load form data required
		$objForm->generateForm($formid);

		if ($cache_enabled === true)
		{
			/**
			 * Cache the object
			 */
			$arr = array(
					'cache_expires' => (time() + (int) $arr_config['cache_form_ttl']),
					'data' => serialize($objForm), //we can use serialize since classes do not use callbacks or anonymous functions
			);
			file_put_contents($arr_config["cache_path"], json_encode((object) $arr));
		}//end cache
	} elseif ($objFormData instanceof cls_forms) {
		$objForm = $objFormData;
	}//end function

	return $objForm;
}//end function

/**
 * Create admin section
 */
function majestic_forms_plugin_setup_menu()
{
	add_menu_page( 'Majestic Forms Plugin Page', 'Majestic Forms Plugin', 'manage_options', 'majestic-forms-plugin', 'majestic_forms_plugin_setup' );
}//end function

/**
 * Create configuration form in the admin section
 */
function majestic_forms_plugin_setup()
{
   ?>
	<div class="wrap">
		<h2>Majestic Web Forms</h2>
			<form method="post">
				<input type="hidden" name="majestic3_forms_config" value="1" />
         <?php
		 	settings_fields( 'options-group' );
		 	do_settings_sections( 'options-group' ); 
		 ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">API Base URL</th>
							<td><input type="text" required="required" title="Set the API Location URL to hit with requests" name="m3_api_base_url" value="<?php echo esc_attr( get_option('m3_api_base_url') ); ?>" /></td>
					</tr>
        
					<tr valign="top">
						<th scope="row">Username</th>
						<td><input type="text" required="required" title="Set Username required for API requests" name="m3_api_username" value="<?php echo esc_attr( get_option('m3_api_username') ); ?>" /></td>
					</tr>
        
					<tr valign="top">
						<th scope="row">Password</th>
						<td><input type="password" required="required" title="Set Password required for API requests" name="m3_api_password" value="<?php echo esc_attr( get_option('m3_api_password') ); ?>" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row">API Key</th>
						<td><input type="text" required="required" title="Set API Key required for API requests" name="m3_api_key" value="<?php echo esc_attr( get_option('m3_api_key') ); ?>" /></td>
					</tr>					
        
					<tr valign="top">
						<th scope="row">Web Form ID</th>
						<td><input type="text" required="required" title="Set Web Form ID to be used" name="m3_form_id" value="<?php echo esc_attr( get_option('m3_form_id') ); ?>" /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row">Enable Caching</th>
						<td><input type="checkbox" title="Enable or Disable local form caching. It speeds up presenting the form to the visitor" name="m3_cache_form" value="1" <?php if (get_option('m3_cache_form') == 1) { echo "checked=\"checked\"";} ?> /></td>
					</tr>
					
					<tr valign="top">
						<th scope="row">Form Cache Timeout (Seconds)</th>
						<td><input type="text" title="Set how long a form should be cached for in seconds. This is ignored if caching is not enabled" name="m3_cache_form_ttl" value="<?php echo esc_attr( get_option('m3_cache_form_ttl') ); ?>" /></td>
					</tr>
				</table>
		 <?php submit_button(); ?>
			</form>
		</div> 
   <?php
}//end function

/**
 * Process configuration form
 * @param array $arr_data
 */
function majestic_forms_plugin_setup_process($arr_data)
{
	//make sure the user is admin
	if (!is_admin())
	{
		return;
	}//end if
	
	//bring config fields array into scope
	global $arr_config_fields;
	
	foreach ($arr_config_fields as $field)
	{
		update_option($field, $_POST[$field]);
	}//end foreach
}//end function

/**
 * Class handling form widget
 */
class majestic_forms_widget extends WP_Widget 
{

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() 
	{
		parent::__construct(
					'majestic', // Base ID
					__( 'Majestic Web Forms', 'text_domain' ), // Name
					array( 'description' => __( 'Load a Majestic Form as Widget', 'text_domain' ), ) // Args
				);
	}//end function

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance )
	{
		majestic_form();
    }//end function

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) 
	{
		// outputs the options form on admin
	}//end function

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) 
	{
		// processes widget options to be saved
	}//end function
}//end class
