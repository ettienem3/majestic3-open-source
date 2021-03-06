<?php
namespace MajesticExternalForms\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontUserLogin\Models\FrontUserSession;
use MajesticExternalForms\Forms\MajesticExternalFormBase;

class MajesticExternalFormsModel extends AbstractCoreAdapter
{
	/**
	 * Container for the External Forms Cache Model
	 * @var \MajesticExternalForms\Models\MajesticExternalFormsCacheModel
	 */
	private $model_cache;

	/**
	 * Load data from the api to construct a form with
	 * @triggers loadForm.cache.get, loadForm.cache.set, loadForm.cache.clear
	 * @param mixed $form_id
	 * @param mixed $reg_id - Optional. Specify where a form must be loaded with populated values
	 * @return multitype:unknown \Zend\Form\Form
	 */
	public function loadForm($form_id, $reg_id = NULL, $arr_additional_params = false)
	{
		ini_set('display_errors', 0);
		if (isset($_GET['debug_display_errors']) && $_GET['debug_display_errors'] == 1)
		{
			ini_set('display_errors', 1);
		}//end if

		if (isset($_GET["cache_clear"]) && $_GET["cache_clear"] == 1)
		{
			try {
				$this->getFormsCacheModel()->clearFormCache($form_id);
				unset($arr_additional_params["cache_clear"]);
				unset($_GET["cache_clear"]);
			} catch (\Exception $e) {
				trigger_error($e->getMessage(), E_USER_WARNING);
			}//end catch
		}//end if

		//try to load data from cache
		try {
			$result = $this->getEventManager()->trigger(__FUNCTION__ . ".cache.get", $this, array("form_id" => $form_id));
			if ($result->stopped() === TRUE)
			{
				$arr_data = $result->last();
			}//end if
		} catch (\Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}//end catch

		if (!isset($arr_data))
		{
			//request latest data from api
			//create the request object
			$objApiRequest = $this->getApiRequestModel();

			//check if user is logged in
			$objUserLoginDetails = $this->setUserLogin($form_id);
			$objApiRequest->setAPIKey($objUserLoginDetails->api_key);

			//setup the object and specify the action
			$objApiRequest->setApiAction("forms/external/$form_id");

			$arr_params = array("raw_data" => 1);
			if (is_array($arr_additional_params))
			{
 				$arr_params = array_merge($arr_params, $arr_additional_params);
			}//end if

			//execute request to load raw data
			$objFormRawData = $objApiRequest->performGETRequest($arr_params)->getBody()->data;
			$arr_data["objFormRawData"] = $objFormRawData;

			//execute request to get constructed from
			unset($arr_params["raw_data"]);
			$objFormData = $objApiRequest->performGETRequest($arr_params)->getBody()->data;

			$arr_data["objFormData"] = $objFormData;

			//load look and feel where applicable
			if ($objFormRawData->template_id != "0" && $objFormRawData->template_id != "")
			{
				$objLookAndFeel = self::loadFormLookAndFeel($objFormRawData->template_id, $form_id);
				if (is_object($objLookAndFeel) && $objLookAndFeel->active == 1)
				{
					$arr_data["objLookAndFeel"] = $objLookAndFeel;
				}//end if
			}//end if

			//save data to cache for reuse
			try {
				//$result = $this->getEventManager()->trigger(__FUNCTION__ . ".cache.clear", $this, array("form_id" => $form_id));
				$result = $this->getEventManager()->trigger(__FUNCTION__ . ".cache.set", $this, array("form_id" => $form_id, "arr_data" => $arr_data));
			} catch (\Exception $e) {
				trigger_error($e->getMessage(), E_USER_WARNING);
			}//end catch
		} else {
			//trigger call in background to record form comm history id activity where set
			if (is_array($arr_additional_params) && isset($arr_additional_params['cid']) && $arr_additional_params['cid'] != '')
			{
				//set absolute path to console
				$console_path = realpath('./MajesticExternal/MajesticExternalForms/console') . '/console.php';
				$arr_request_data = array('form_id' => $form_id, 'reg_id' => $reg_id, 'comm_history_id' => $arr_additional_params['cid'], 'host' => $_SERVER['HTTP_HOST']);
				$json = json_encode($arr_request_data, JSON_FORCE_OBJECT);
				$str = 'php ' . $console_path . ' Formprocessaction execute TrackFormCommHistory --argument1=\'' . $json . '\' --host=\'' . $_SERVER['HTTP_HOST'] . '\'  > /dev/null 2>&1 &';
				exec($str);
			}//end if
		}//end if

		$arr_return = array();

		//build the form object
		$objForm = self::constructForm($arr_data["objFormData"], $arr_data["objFormRawData"]);

		//set form default values...
		if (is_null($reg_id) || $reg_id == "")
		{
			foreach($arr_data["objFormRawData"]->arr_fields as $objField)
			{
				if (!isset($objField->id) || $objField->default_content == "")
				{
					continue;
				}//end if

				if ($objField->fields_custom_id != "")
				{
					//custom field
					$field_type = $objField->field_custom_input_type;
					$field_name = $objField->fields_custom_field;
				} else {
					//standard field
					$field_type = $objField->field_std_input_type;
					$field_name = $objField->fields_std_field;
				}//end if

				switch (strtolower($field_type))
				{
					case "checkbox":
						if ($objForm->has($field_name))
						{
							$objForm->get($field_name)->setValue($objField->default_content);
						}//end if
						break;

					case "text":
					case "textarea":
						if ($objField->default_content != 0)
						{
							$objForm->get($field_name)->setValue($objField->default_content);
						}//end if
						break;

					default:
						if ($objForm->has($field_name))
						{
							$objForm->get($field_name)->setValue($objField->default_content);
						}//end if
						break;
				}//end switch
			}//end foreach
		}//end if

		$arr_return["objForm"] = $objForm;
		$arr_return["objLookAndFeel"] = $arr_data["objLookAndFeel"];
		$arr_return["objFormRawData"] = $arr_data["objFormRawData"];

		//trigger pre config event for form
		$objFormRawData = $arr_data["objFormRawData"];
		$objLookAndFeel = $arr_data["objLookAndFeel"];

		switch ($objFormRawData->form_types_behaviour)
		{
			case '__web':
				//trigger event to allow external modules to set configuration where required
				$result = $this->getEventManager()->trigger('web_form_post_config_load', $this, array('objFormRawData' => $objFormRawData, 'objLookAndFeel' => $objLookAndFeel, 'objForm' => $objForm));

				//reassign data after event triggered
				$arr_return["objForm"] = $objForm;
				$arr_return["objFormRawData"] = $objFormRawData;
				$arr_return["objLookAndFeel"] = $objLookAndFeel;
				break;
		}//end switch

		return $arr_return;
	}//end function

	public function loadFormPostSubmit($form_id, $reg_id)
	{
		//request latest data from api
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//check if user is logged in
		$objUserLoginDetails = $this->setUserLogin($form_id);
		$objApiRequest->setAPIKey($objUserLoginDetails->api_key);

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/external/$form_id");
		$arr_params = array(
				"post_submit" => 1,
				"reg_id" => $reg_id,
		);

		//execute request to load raw data
		$objFormRawData = $objApiRequest->performGETRequest($arr_params)->getBody()->data;

		//request look and feel data where set
 		if (isset($objFormRawData->template_id) && $objFormRawData->template_id > 0)
 		{
 			try {
 				$objLookAndFeel = self::loadFormLookAndFeel($objFormRawData->template_id, $form_id);
 				$objFormRawData->objLookAndFeel = $objLookAndFeel;
 			} catch (\Exception $e) {
 				//ignore layout errors
 //@TODO implement error reporting here if needed
 			}//end catch
 		}//end if

		return $objFormRawData;
	}//end function

	/**
	 * Load contact details where requested
	 * @param int $form_id
	 * @param str $reg_id
	 * @param str $comm_history_id
	 * @param str $replace_content - base 64 encoded replace content
	 */
	public function loadContact($form_id, $reg_id, $comm_history_id = '', $replace_content = '')
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//check if user is logged in
		$objUserLoginDetails = $this->setUserLogin($form_id);
		$objApiRequest->setAPIKey($objUserLoginDetails->api_key);

		$replace_string = '';
		if ($replace_content != '')
		{
			$arr_matches = array();
			preg_match_all('/#(.*?)#/s', ($replace_content), $arr_matches);
			if (count($arr_matches[0]) > 0)
			{
				$replace_string = '&' . http_build_query(array('replace_content' => $arr_matches[0]));
			}//end if
		}//end if

		//request contact details from the api
		$objApiRequest->setApiAction("contacts/$reg_id?fid=$form_id&cid=$comm_history_id" . $replace_string);

		try {
			$objContact = $objApiRequest->performGETRequest()->getBody()->data;
			return $objContact;
		} catch (\Exception $e) {
			//@TODO do something with the error
var_dump($e->getMessage()); exit;
		}//end catch
	}//end function

	/**
	 * Submit form to API for submission
	 * @param mixed $form_id
	 * @param array $arr_form_data
	 * @param mixed $arr_additional_data - append additional options to be set as query params for API call
	 * @return Ambigous <\Zend\Json\mixed, mixed, NULL, \Zend\Json\$_tokenValue, multitype:, multitype:Ambigous <\Zend\Json\mixed, \Zend\Json\$_tokenValue, NULL> , stdClass, multitype:Ambigous <\Zend\Json\mixed, \Zend\Json\$_tokenValue, multitype:, multitype:Ambigous <\Zend\Json\mixed, \Zend\Json\$_tokenValue, NULL> , NULL> >
	 */
	public function processFormSubmit($form_id, $arr_form_data, $arr_additional_data = FALSE)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//check if user is logged in
		$objUserLoginDetails = $this->setUserLogin($form_id);
		$objApiRequest->setAPIKey($objUserLoginDetails->api_key);

		$add_params_str = "";
		if (is_array($arr_additional_data))
		{
			$add_params_str .= "&";
			foreach ($arr_additional_data as $k => $v)
			{
				$add_params_str .= "$k=$v&";
			}//end foreach
			$add_params_str = rtrim($add_params_str, "&");
		}//end if

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/external?fid=$form_id" . $add_params_str);

		//remove some form values
		if (isset($arr_form_data["captcha"]))
		{
			unset($arr_form_data["captcha"]);
		}//end if

		//send data
		$objResult = $objApiRequest->performPOSTRequest($arr_form_data)->getBody();

		return $objResult;
	}//end function

	/**
	 * Accessor method for obtaining form api key
	 * @param mixed $form_id
	 * @return stdClass
	 */
	public function getUserFormLogin($form_id)
	{
		return $this->setUserLogin($form_id);
	}//end function

	public function executeConsoleTask($objRequest)
	{
		//extract data
		$objData = json_decode($objRequest->getParam("argument1"));
		if (!is_object($objData))
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : No data received", 500);
		}//end if

		switch ($objRequest->getParam("execute-task"))
		{
			case 'TrackFormCommHistory':
				//create the request object
				$objApiRequest = $this->getApiRequestModel();

				//check if user is logged in
				$objUserLoginDetails = $this->setUserLogin($objData->form_id);
				$objApiRequest->setAPIKey($objUserLoginDetails->api_key);

				//setup the object and specify the action
				$objApiRequest->setApiAction("forms/external/" . $objData->form_id);
				$arr_params = array('cid' => $objData->comm_history_id, 'fid' => $objData->form_id, 'reg_id' => $objData->reg_id);

				//execute request to load raw data
				$objForm = $objApiRequest->performGETRequest($arr_params)->getBody()->data;
				return array($objForm);
				break;
		}//end switch

		throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : An invalid console request has been received", 500);
	}//end function

	/**
	 * Construct a form based on data received from the API
	 * @param object $objFormData
	 * @return \Zend\Form\Form
	 */
	protected function constructForm($objFormData, $objFormRawData)
	{
		//convert data to array
		$arr_form_data = \Zend\Json\Json::decode(\Zend\Json\Json::encode($objFormData), TRUE);

		//create form object
		$objForm = new MajesticExternalFormBase();
		$objForm->setAttributes($arr_form_data["attributes"]);
		$objForm->setOptions($arr_form_data["options"]);

		//add form elements
		foreach ($arr_form_data["arr_fields"] as $arr_element)
		{
			switch (strtolower($arr_element["attributes"]["type"]))
			{
				default:
					if ($arr_element["attributes"]["type"] == "")
					{
						$arr_element["type"] = "text";
					} else {
						$arr_element["type"] = $arr_element["attributes"]["type"];
					}//end if
					break;
			}//end switch

			//check if field marked as a hidden field
			if ($arr_element['hidden'] == 1)
			{
				$arr_element['type'] = 'hidden';
			}//end if

			//check if field is require, if not set additional options
			if (!isset($arr_element["attributes"]["required"]))
			{
				$arr_element["required"] = FALSE;
				$arr_element["allowEmpty"] = TRUE;
			}//end if

			$arr_element["name"] = $arr_element["attributes"]["name"];
			$objForm->add($arr_element);
		}//end foreach

		return $objForm;
	}//end function

	/**
	 * Request form look and feel from the API
	 * @param mixed $template_id
	 * @param mixed $form_id
	 * @return object
	 */
	protected function loadFormLookAndFeel($template_id, $form_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//check if user is logged in
		$objUserLoginDetails = $this->setUserLogin($form_id);
		$objApiRequest->setAPIKey($objUserLoginDetails->api_key);

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/external-form-template/$template_id");

		//execute request to load raw data
		$objFormLookAndFeel = $objApiRequest->performGETRequest()->getBody()->data;
		return $objFormLookAndFeel;
	}//end function

	/**
	 * Check if a user is logged in
	 * If not, setup a session with the correct key for form submission to work
	 * @param int $form_id
	 * @return stdClass
	 */
	private function setUserLogin($form_id)
	{
		//check if user is logged into frontend
		$objUserSession = FrontUserSession::isLoggedIn();
		if (!$objUserSession)
		{
			$cache_key = "ex_form_" . $form_id . "_" . $_SERVER["HTTP_HOST"] . "_key";

			//check if data has been cached
			$objData = $this->getFormsCacheModel()->readFormCache($cache_key);
			if (!$objData || is_null($objData) || !is_object($objData))
			{
				//create the request object
				$objApiRequest = $this->getApiRequestModel();

				//disable api session login
				$objApiRequest->setAPISessionLoginDisable();

				//load master user details
				$arr_user = $this->getServiceLocator()->get("config")["master_user_account"];
				//check for possible master accounts amalgamated in config array
				if (count($arr_user['uname']) > 1)
				{
					echo '<h3>There is a possible problem with the configuration for this profile, please contact support to resolve.</h3>';
					exit;
				}//end if

				//set api request authentication details
				$objApiRequest->setAPIKey($arr_user['apikey']);
				$objApiRequest->setAPIUser(md5($arr_user['uname']));
				$objApiRequest->setAPIUserPword(md5($arr_user['pword']));

				//setup the object and specify the action
				$objApiRequest->setApiAction("user/authenticate-form");

				//set payload
				$arr_data = array(
						"fid" => $form_id,
						"tstamp" => time(),
						'key' => $arr_user['apikey'],
				);

				$objData = $objApiRequest->performPOSTRequest($arr_data)->getBody();

				//cache the data
				$this->getFormsCacheModel()->setFormCache($cache_key, $objData);
			}//end if

			return $objData->data;
		}//end function

		return FALSE;
	}//end function

	/**
	 * Create an instance of the External Forms Cache Model using the Service Manager
	 * @return \MajesticExternalForms\Models\MajesticExternalFormsCacheModel
	 */
	private function getFormsCacheModel()
	{
		if (!$this->model_cache)
		{
			$this->model_cache = $this->getServiceLocator()->get("MajesticExternalForms\Models\MajesticExternalFormsCacheModel");
		}//end if

		return $this->model_cache;
	}//end function
}//end class
