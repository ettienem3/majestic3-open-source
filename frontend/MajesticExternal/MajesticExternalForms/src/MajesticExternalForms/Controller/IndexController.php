<?php
namespace MajesticExternalForms\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use MajesticExternalForms\Forms\MajesticExternalFormsForm;
use FrontUserLogin\Models\FrontUserSession;

class IndexController extends AbstractActionController
{
	/**
	 * Container for instance of forms model
	 * @var \MajesticExternalForms\Models\MajesticExternalFormsModel
	 */
	private $model_forms;

    /**
     * Submit a webform
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|Ambigous <unknown, \Zend\Form\Form>
     */
    public function bfAction()
    {
    	//set container for additional params
    	$arr_additional_params = array();

    	$form_id = $this->params()->fromRoute("fid");
    	$reg_id = $this->params()->fromRoute("reg_id", NULL);
    	$arr_additional_params["reg_id"] = $reg_id;

    	//check if reg id is encoded, if not, do not process
    	if (is_numeric($reg_id))
    	{
    		$this->flashMessenger()->addErrorMessage("An error occured attempting to load data");

    		//redirect back to form
			return $this->redirect()->toRoute("majestic-external-forms/bf", array("fid" => $form_id));
    	}//end if

    	//load comm history id
    	$comm_history_id = $this->params()->fromQuery("cid", "");
    	if ($comm_history_id != "")
    	{
    		$arr_additional_params["cid"] = $comm_history_id;
    	}//end if

    	//check form id has been set
    	if (!is_string($form_id))
    	{
    		echo "Form could not be loaded. Required information is not available.";
    		exit;
    	}//end if

    	try {
	    	//load form details
	    	$arr_return = $this->getExternalFormsModel()->loadForm($form_id, $reg_id, $arr_additional_params);

	    	$arr_return["additional_data"] = $arr_additional_params;

	    	//add plain form url
	    	$arr_return["form_url"] = $this->url()->fromRoute("majestic-external-forms/bf", array("fid" => $form_id));
    	} catch (\Exception $e) {
//@TODO do something with the error
// var_dump($e->getMessage() . " : " . $e->getPrevious()); exit;
			die("The requested form could not be loaded. Response: " . $this->frontControllerErrorHelper()->formatErrors($e));
    	}//end catch

    	if ($arr_return["objFormRawData"]->secure_form == "1")
    	{
    		if (!isset($_SERVER['HTTPS']) || (strtolower($_SERVER['HTTPS']) != "on" && $_SERVER["HTTPS"] != 1 && $_SERVER["SERVER_PORT"] != "443"))
    		{
				header("location:https://" . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI]);
    		}//end if
    	}//end if

    	//should the user be logged in?
    	if ($arr_return["objFormRawData"]->user_login == 1)
    	{
    		$objUserSession = FrontUserSession::isLoggedIn();
			if (!$objUserSession)
			{
				$this->flashMessenger()->addInfoMessage("User must be logged in in order to access form");

				//redirect to login screen
				return $this->redirect()->toRoute("front-user-login");
			}//end if
    	}//end if

    	//should the contact be specified
    	if ($arr_return["objFormRawData"]->id_required == 1 && $reg_id == "")
    	{
    		echo "Form could not be loaded. Contact ID is not set";
    		exit;
    	}//end if

    	//should the form be redirected on loading?
    	if ($arr_return["objFormRawData"]->redirect_on_load != "")
    	{
    		header("location:" . $arr_return["objFormRawData"]->redirect_on_load);
    		exit;
    	}//end if

    	//extract form from result
    	$form = $arr_return["objForm"];

    	//does form have password access enabled?
 //@TODO set proper session data
    	if ($arr_return["objFormRawData"]->form_password != "" && $_SESSION["form_data"]["password"] != $arr_return["objFormRawData"]->form_password)
    	{
    		$form = new \Zend\Form\Form();
    		$form->add(array(
    			"type" => "password",
    			"name" => "password",
    			"attributes" => array(
    				"id" => "password",
    				"required" => "required",
    			),
    			"options" => array(
    				"label" => "Form Password",
    			),
    		));

    		$form->add(array(
    			"type" => "submit",
    			"name" => "submit",
    			"attributes" => array(
    				"value" => "Submit",
    			),
    		));

    		$request = $this->getRequest();
    		if ($request->isPost())
    		{
    			if ($request->getPost("password") == $arr_return["objFormRawData"]->form_password)
    			{
    				$_SESSION["form_data"]["password"] = $request->getPost("password");
    				return $this->redirect()->toRoute("majestic-external-forms/bf", array("fid" => $form_id, "reg_id" => $reg_id));
    			}//end if
    		}//end if

    		if ($_SESSION["form_data"]["password"] != $arr_return["objFormRawData"]->form_password)
    		{
	    		$arr_return["form"] = $form;
	    		return $arr_return;
    		}//end if
    	}//end if

    	//is form captcha enabled?
    	if ($arr_return["objFormRawData"]->captcha == 1)
    	{
    		if (!is_dir("./public/captcha"))
    		{
    			mkdir("./public/captcha", 0755, TRUE);
    		}//end if

    		$objCaptcha = new \Zend\Captcha\Image(array(
    				'expiration' => '300',
    				'wordlen' => '7',
    				'font' => 'data/fonts/arial.ttf',
    				'fontSize' => '20',
    				'imgDir' => 'public/captcha',
    				'imgUrl' => '/captcha',
    				'lineNoiseLevel' => 1,
    				'dotNoiseLevel' => 1,
    		));

    		$form->add(array(
    				"name" => "captcha",
    				"type" => "Zend\Form\Element\Captcha",
    				"attributes" => array(
    						"id" => "captcha",
    						"required" => "required",
    						"autocomplete" => "off",
    				),
    				"options" => array(
    						"label" => "Human verification",
    						"captcha" => $objCaptcha,
    				),
    		));
    	}//end if

    	$arr_return["form_posted"] = FALSE;
    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		if ($form->has("captcha"))
    		{
    			if (!$objCaptcha->isValid($request->getPost("captcha"), $request->getPost()))
    			{
    				$form->setData($request->getPost());
    				$this->flashMessenger()->addErrorMessage("CAPTCHA validation failed");
    				$arr_return["form"] = $form;
    				return $arr_return;
    			}//end if
    		}//end if

    		//set form post flag to stop javascript loading on form error
    		$arr_return["form_posted"] = TRUE;
    		$form->setData($request->getPost());
    		if ($form->isValid($request->getPost()))
    		{
    			try {
	    			//submit the form
	    			$objResult = $this->getExternalFormsModel()->processFormSubmit($form_id, $form->getData(), $arr_additional_params);

	    			//unset form password
	    			if (isset($_SESSION["form_data"]["password"]))
	    			{
	    				unset($_SESSION["form_data"]["password"]);
	    			}//end if

	    			//redirect to post submit page
 	    			return $this->redirect()->toRoute("majestic-external-forms/bfs", array("fid" => $form_id, "reg_id" => $objResult->data->reg_id_encoded));
    			} catch (\Exception $e) {
    				//extract errors from the request return by the API
    				$arr_response = explode("||", $e->getMessage());
    				$objResponse = json_decode($arr_response[1]);

    				//check if user is logged in to display links to duplicate contacts
    				$objUserSession = FrontUserSession::isLoggedIn();
    				if (is_object($objResponse) && is_object($objUserSession))
    				{
    					switch ($objResponse->HTTP_RESPONSE_CODE)
    					{
    						case 409: //duplicates found
    							//extract message
    							$arr_t = explode(":", $objResponse->HTTP_RESPONSE_MESSAGE);
    							$id_string = array_pop($arr_t);
    							$this->flashMessenger()->addErrorMessage(trim(str_replace(array("{", "}"), "", $id_string)));

    							//extract ids and create links to each
    							preg_match('~{(.*?)}~', $id_string, $output);
    							$arr_contact_ids = explode(",", $output[1]);
    							if (is_array($arr_contact_ids) && count($arr_contact_ids) > 0)
    							{
    								foreach ($arr_contact_ids as $k => $id)
    								{
    									$this->flashMessenger()->addInfoMessage("<a href=\"" . $this->url()->fromRoute("front-contacts", array("action" => "view-contact", "id" => $id)) . "\" target=\"_blank\" title=\"View Contact\">Click to view duplicate $id</a>");
    									if ($k > 19)
    									{
    										break;
    									}//end if
    								}//end foreach
    							}//end if
    							break;

    						default:
    							//add errors to the form already where set
    		//@TODO this needs some work, messages  should be generated back into the form directly...
    							if (is_object($objResponse) && isset($objResponse->data))
    							{
    								foreach ($objResponse->data as $k => $objField)
    								{
    									if (is_object($objField) && isset($objField->messages) && isset($objField->attributes->name))
    									{
    										if ($form->has($objField->attributes->name))
    										{
    											$arr_message = (array) $objField->messages;
    											$form->get($objField->attributes->name)->setMessages($arr_message);
    											$form->get($objField->attributes->name)->setValue($request->getPost($objField->attributes->name));
    										}//end if
    									}//end if
    								}//end if
    							}//end if

    							//set form errors
    							$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    							break;
    					}//end switch
    				} else {
    					//@TODO this needs some work, messages  should be generated back into the form directly...
    					if (is_object($objResponse) && isset($objResponse->data))
    					{
    						foreach ($objResponse->data as $k => $objField)
    						{
    							if (is_object($objField) && isset($objField->messages) && isset($objField->attributes->name))
    							{
    										if ($form->has($objField->attributes->name))
    										{
    											$arr_message = (array) $objField->messages;
    											$form->get($objField->attributes->name)->setMessages($arr_message);
    											$form->get($objField->attributes->name)->setValue($request->getPost($objField->attributes->name));
    										}//end if
    							}//end if
    						}//end if
    					}//end if

    					//set form errors
    					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    				}//end if
    			}//end catch
    		}//end if
    	}//end if

    	$arr_return["form"] = $form;
    	$arr_return["form_id"] = $form_id;

    	if ($reg_id != "")
    	{
    		$arr_return["reg_id"] = $reg_id;
    	}//end if

    	return $arr_return;
    }//end function

    public function bfJsonAction()
    {
		//http://www.alpacajs.org/tutorial.html

    	//set json output
    	$this->getResponse()->getHeaders()->addHeaders(array('Content-type' => 'application/json'));
    	$this->layout("layout/external/forms/json");

		//make sure connection is secure...

    	//set container for additional params
    	$arr_additional_params = array();
    	$arr_additional_params['raw_data'] = 1;
    	$form_id = $this->params()->fromRoute("fid");

    	//check form id has been set
    	if (!is_string($form_id))
    	{
    		echo json_encode(array("error" => 1, "response" => "Form could not be loaded. Required information is not available"), JSON_FORCE_OBJECT);
    		exit;
    	}//end if

    	try {
    		//load form details
    		$arr_return = $this->getExternalFormsModel()->loadForm($form_id, NULL, $arr_additional_params);
    		$arr_return["additional_data"] = $arr_additional_params;
    	} catch (\Exception $e) {
    		echo json_encode(array("error" => 1, "response" => $e->getMessage() . " : " . $e->getPrevious()), JSON_FORCE_OBJECT);
    		exit;
    	}//end catch

    	//should the user be logged in?
    	if ($arr_return["objFormRawData"]->user_login == 1)
    	{
    		echo json_encode(array("error" => 1, "response" => "Form configuration prevents this operation"), JSON_FORCE_OBJECT);
    		exit;
    	}//end if

    	//should the contact be specified
    	if ($arr_return["objFormRawData"]->id_required == 1 && $reg_id == "")
    	{
    		echo json_encode(array("error" => 1, "response" => "Form configuration prevents this operation"), JSON_FORCE_OBJECT);
    		exit;
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
			$form = $arr_return["objForm"];
			$form->setData($request->getPost());
			if ($form->isValid($request->getPost()))
			{
				try {
					if ($this->params()->fromQuery("simulate", 0) == 1)
					{
						$arr_return["submit_result"] = "OK";
					} else {
						//submit the form
						$objResult = $this->getExternalFormsModel()->processFormSubmit($form_id, $form->getData(), $arr_additional_params);
						$arr_return["submit_result"] = "OK";
					}//end if
				} catch (\Exception $e) {
					//extract errors from the request return by the API
					$arr_response = explode("||", $e->getMessage());
					$objResponse = json_decode($arr_response[1]);

					//set messages
					if (is_object($objResponse) && isset($objResponse->data))
					{
						foreach ($objResponse->data as $k => $objField)
						{
							if (is_object($objField) && isset($objField->messages) && isset($objField->attributes->name))
							{
								if ($form->has($objField->attributes->name))
								{
									$arr_message = (array) $objField->messages;
									$form->get($objField->attributes->name)->setMessages($arr_message);
									$form->get($objField->attributes->name)->setValue($request->getPost($objField->attributes->name));
								}//end if
							}//end if
						}//end if
					}//end if

					$arr_return["submit_errors"] = $form->getMessages();
					$arr_return["objForm"] = $form;
				}//end catch
			} else {
				$arr_return["submit_errors"] = $form->getMessages();				
				foreach ($form->getMessages() as $key => $arr_messages)
				{
					if ($form->has($key))
					{
						$form->get($key)->setMessage($arr_messages);	
					}//end if
				}//end foreach
				
				$arr_return["objForm"] = $form;
			}//end if
    	}//end if

    	return $arr_return;
    }//end function

    /**
     * Post submit action for webforms
     */
    public function bfsAction()
    {
    	$form_id = $this->params()->fromRoute("fid");
    	$reg_id = $this->params()->fromRoute("reg_id", NULL);

    	//check form id has been set
    	if (!is_string($form_id) || $reg_id == "")
    	{
    		echo "Form could not be loaded. Required information is not available.";
    		exit;
    	}//end if

    	//check if reg id is encoded, if not, do not process
    	if (is_numeric($reg_id))
    	{
    		$this->flashMessenger()->addErrorMessage("An error occured attempting to load data");

    		//redirect back to form
    		return $this->redirect()->toRoute("majestic-external-forms/bf", array("fid" => $form_id));
    	}//end if

    	try {
    		//load form details
    		$objData = $this->getExternalFormsModel()->loadFormPostSubmit($form_id, $reg_id);

 //@TODO move this to proper location
    		//should the user be redirected?
    		if ($objData->redirect != "")
    		{
    			//redirect to form post submit set location
    			header("location:" . $objData->redirect);
    			exit;
    		}//end if
    	} catch (\Exception $e) {
    		var_dump($e->getMessage() . " : " . $e->getPrevious()); exit;
    	}//end catch

		return array(
				"objForm" => $objData,
		);
    }//end function

    /**
     * Submit a viral form
     */
    public function vfAction()
    {

    }//end function

    /**
     * Post submit action for a viral form
     */
    public function vfsAction()
    {

    }//end function

    public function ajaxLoadContactAction()
    {
    	$request = $this->getRequest();
    	$reg_id = $request->getPost("r");
    	$form_id = $request->getPost("f");
    	if ($request->isPost() && $reg_id != "" && $form_id != "")
    	{
    		try {
    			//request contact information
    			$objData = $this->getExternalFormsModel()->loadContact($form_id, $reg_id);
    			echo json_encode(array("error" => 0, "response" => $objData));
    			exit;
    		} catch (\Exception $e) {
    			echo json_encode(array("error" => 1, "response" => $e->getMessage()));
    			exit;
    		}//end catch

    	}//end if

    	echo json_encode(array("error" => 1, "response" => "Data could not be retrieved"));
    	exit;
    }//end function

    /**
     * Create an instance of the Majestic External Forms model using the Service Manager
     * @return \MajesticExternalForms\Models\MajesticExternalFormsModel
     */
    private function getExternalFormsModel()
    {
    	if (!$this->model_forms)
    	{
    		$this->model_forms = $this->getServiceLocator()->get("MajesticExternalForms\Models\MajesticExternalFormsModel");
    	}//end if

    	return $this->model_forms;
    }//end function
}//end class
