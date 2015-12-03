<?php
/*
 Copyright (c) 2015 Majestic 3 http://majestic3.com

 Permission is hereby granted, free of charge, to any person obtaining
 a copy of this software and associated documentation files (the
 'Software'), to deal in the Software without restriction, including
 without limitation the rights to use, copy, modify, merge, publish,
 distribute, sublicense, and/or sell copies of the Software, and to
 permit persons to whom the Software is furnished to do so, subject to
 the following conditions:

 The above copyright notice and this permission notice shall be included
 in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

// No direct access
defined('_JEXEC') or die;
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

	if ($form_css_enabled == 1)
	{
		//add some basic styling
		$form_html .= '
					<style>
						#' . $objForm->form_css_id . ' {
							padding: 5px;
							display: inline-block;
							margin-bottom: 25px;
							width: 500px;
						}

						#' . $objForm->form_css_id . ' div {
							margin: 5px 0px 5px 0px;
						}

						#' . $objForm->form_css_id . ' label.form-label  {
							display: inline-block;
							width: 150px;
						}
					</style>';
	}//end if

	//present the form
	echo $form_html;
} catch (Exception $e) {
	echo '<fieldset><legend>Error:</legend>';
	echo 	'<pre>' . print_r($e, TRUE) . '</pre>';
	echo '</fieldset>';
}//end catch

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