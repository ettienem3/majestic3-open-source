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

/**
 * Load some classes
 */
require_once 'classes/cls_execute_request.php';
require_once 'classes/cls_forms.php';
require_once 'classes/cls_form_element.php';
require_once 'classes/cls_form_element_predefined.php';

/**
 * Gather params from module configuration
 */
$formid 				= $params->get('formid');
$form_css_enabled		= $params->get("form_css_enabled");
$cache_form 			= $params->get('cache_form');
$cache_form_ttl 		= $params->get('cache_form_ttl');
$cache_path 			= "./tmp/form_cache_majestic_" . $formid . ".json"; //keep cached files out of public folders!
$arr_credentials 		= array(
								'api_base_url' 		=> $params->get('apiurl') . '/api',
								'api_key' 			=> $params->get('apikey'),
								'api_username' 		=> $params->get('apiusername'),
								'api_password' 		=> $params->get('apipassword'),
							);

//load request object and set variables
$objExecuteRequest = new cls_execute_request();

//set variables loaded from config
$objExecuteRequest->setKeyFromArray($arr_credentials);

//set api url
$objExecuteRequest->setKey('api_url', $arr_credentials['api_base_url']);

require JModuleHelper::getLayoutPath('mod_majestic_forms', $params->get('layout', 'default'));