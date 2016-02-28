<?php
namespace FrontContacts\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use FrontUserLogin\Models\FrontUserSession;

class IndexController extends AbstractActionController
{
	/**
	 * Container for the Front Contacts Model
	 * @var \FrontContacts\Models\FrontContactsModel
	 */
	private $model_contacts;

	/**
	 * Container for the Forms admin model
	 * @var \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private $model_forms_admin;

	/**
	 * Container for the Front Users Model
	 * @var \FrontUsers\Models\FrontUsersModel
	 */
	private $model_users;

	/**
	 * Container for the Contact Status Model
	 * @var \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private $model_contact_statuses;

	/**
	 * Container for the Front Contact System Fields Model
	 * @var \FrontContacts\Models\FrontContactsSystemFieldsModel
	 */
	private $model_front_contact_system_fields;

    public function indexAction()
    {
    	//extract form id from url
    	$form_id = $this->params()->fromQuery("fid", "");

    	//select contact profile form to use
    	if ($form_id == "")
    	{
    		//check if form id is set in user session
    		$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    		if (isset($objUserStorage->readUserNativePreferences()->cpp_layout_id) && is_numeric($objUserStorage->readUserNativePreferences()->cpp_layout_id))
    		{
    			//load the specified form's fields
    			$form_id = $objUserStorage->readUserNativePreferences()->cpp_layout_id;
    			$objForm = $this->getFormAdminModel()->getForm($form_id);
    		} else {
    			$form_id = "none";
    		}//end if
    	} else {
    		if ($form_id != "none" && is_numeric($form_id))
    		{
    			$objForm = $this->getFormAdminModel()->getForm($form_id);
    		}//end if
    	}//end if

    	//extract search params from query
    	$arr_params = $this->params()->fromQuery();

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$arr_params = array_merge($arr_params, (array) $request->getPost());
    	}//end foreach

    	//load contacts
		$objContacts = $this->getContactsModel()->fetchContacts($arr_params);
		
		if (!isset($objForm))
		{
			$objForm = FALSE;
		}//end if

		return array(
					"objForm" => $objForm,
					"objContacts" => $objContacts,
					"arr_params" => $arr_params,
				);
    }//end function

    public function ajaxSearchValuesAction()
    {
    	try {
	    	switch($this->params()->fromQuery("param"))
	    	{
	    		case "regtbl_source":
					$objSources = $this->getFrontContactsSystemFieldsModel()->fetchDistinctContactSources();
					foreach ($objSources as $objSource)
					{
						if ($objSource->source == "")
						{
							continue;
						}//end if

						$arr_data[] = array("id" => $objSource->source, "val" => $objSource->source);
					}//end foreach
	    			break;

	    		case "regtbl_ref":
	    			$objReferences = $this->getFrontContactsSystemFieldsModel()->fetchDistinctContactReferences();
	    			foreach ($objReferences as $objReference)
	    			{
	    				if ($objReference->reference == "")
	    				{
	    					continue;
	    				}//end if

	    				$arr_data[] = array("id" => $objReference->reference, "val" => $objReference->reference);
	    			}//end foreach
	    			break;

	    		case "regtbl_user":
	    			$objUsers = $this->getUsersModel()->fetchUsers();
	    			foreach ($objUsers as $objUser)
	    			{
	    				if (!is_numeric($objUser->id))
	    				{
	    					continue;
	    				}//end if
	    				$arr_data[] = array("id" => $objUser->id, "val" => $objUser->uname);
	    			}//foreach
	    			break;

	    		case "regtbl_status":
	    			$objStatuses = $this->getContactStatusModel()->fetchContactStatuses();
	    			foreach ($objStatuses as $objStatus)
	    			{
	    				if (!is_numeric($objStatus->id))
	    				{
	    					continue;
	    				}//end if
	    				$arr_data[] = array("id" => $objStatus->id, "val" => $objStatus->status);
	    			}//end foreach
	    			break;
	    	}//end switch
    	} catch (\Exception $e) {
    		echo json_encode(array(
    			"error" => 1,
    			"response" => $e->getMessage(),
    		));
    		exit;
    	}//end catch

    	echo json_encode(array(
    		"error" => 0,
    		"response" => $arr_data,
    	), JSON_FORCE_OBJECT);
    	exit;
    }//end function

    public function viewContactAction()
    {
    	//set contact id
    	$reg_id = $this->params()->fromRoute("id", "");

    	if ($reg_id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Contact could not be loaded. Id is not set");

    		//return to contacts index page
    		return $this->redirect()->toRoute("front-contacts");
    	}//end if

    	//extract form id from url
    	$form_id = $this->params()->fromQuery("fid", "");
    	$form = $this->getContactsModel()->getContactProfileForm($form_id);

    	//select contact profile form to use
    	if (is_array($form) && $form_id == "")
    	{
    		//check if form id is set in user session
    		$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    		if (isset($objUserStorage->readUserNativePreferences()->cpp_form_id) && is_string($objUserStorage->readUserNativePreferences()->cpp_form_id))
    		{
    			$form_id = $objUserStorage->readUserNativePreferences()->cpp_form_id;
    			$form = $this->getContactsModel()->getContactProfileForm($form_id);
    		} else {
    			//redirect to select profile form action
    			return $this->redirect()->toUrl($this->url()->fromRoute("front-contacts", array("action" => "select-profile-form")) . "?redirect=" . $this->url()->fromRoute("front-contacts", array("action" => "view-contact", "id" => $reg_id)));
    		}//end if
    	}//end if

    	try {
    		//load the contact
    		$objContact = $this->getContactsModel()->fetchContact($reg_id);
    	} catch (\Exception $e) {
    		//set error message
    		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
    		return $this->redirect()->toRoute("front-contacts");
    	}//end catch

    	if (!$form instanceof \Zend\Form\Form)
    	{
    		$this->flashMessenger()->addErrorMessage("Contact data cannot be displayed. Profile form could not be located");
    		return $this->redirect()->toRoute("front-contacts");
    	}//end if

    	//bind contact data to form
    	$form->bind($objContact);

    	//remove some form elements
    	$form->remove("submit");

    	//disable form elements
    	foreach ($form as $element)
    	{
			switch ($element->getAttribute("type"))
			{
				case "checkbox":
				case "radio":
				case "select":
					$form->get($element->getAttribute("name"))->setAttribute("disabled", "disabled");
					break;

				default:
					$form->get($element->getAttribute("name"))->setAttribute("readonly", "readonly");
					break;
			}//end switch
    	}//end foreach

    	return array(
    		"form" => $form,
    		"objContact" => $objContact,
    	);
    }//end function

    public function createContactAction()
    {
    	//extract form id from url
    	$form_id = $this->params()->fromQuery("fid", "");
		$form = $this->getContactsModel()->getContactProfileForm($form_id);

		//select contact profile form to use
		if (is_array($form) && $form_id == "")
		{
			//check if form id is set in user session
			$objUserStorage = FrontUserSession::getUserLocalStorageObject();
			if (is_numeric($objUserStorage->readUserNativePreferences()->cpp_form_id))
			{
				$form_id = $objUserStorage->readUserNativePreferences()->cpp_form_id;
			} else {
				//redirect to select profile form action
				return $this->redirect()->toRoute("front-contacts", array("action" => "select-profile-form"));
			}//end if
		}//end if

		//check if cpp form is available
		if (!is_numeric($form_id))
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Contact Profile Page could not be loaded. Form is not set");

			//redirect to index page
			return $this->redirect()->toRoute("front-contacts");
		}//end if

		//load the form
		if (!$form instanceof \Zend\Form\Form)
		{
			$form = $this->getContactsModel()->getContactProfileForm($form_id);
		}//end if

		//load the request
		$request = $this->getRequest();
		if ($request->isPost())
		{
			//populate form
			$form->setData($request->getPost());

			//validate the form
			if ($form->isValid($request->getPost()))
			{
				try {
					//create the contact
					$objContact = $this->getContactsModel()->createContact($form->getData(), $form_id);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Contact created successfully");

					//redirect to contact profile page
					return $this->redirect()->toRoute("front-contacts", array("action" => "view-contact", "id" => $objContact->get("reg_id")));
				} catch (\Exception $e) {
					//extract errors from the request return by the API
					$arr_response = explode("||", $e->getMessage());
					$objResponse = json_decode($arr_response[1]);

					if (is_object($objResponse))
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
								//set form errors
								$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
								break;
						}//end switch
					} else {
						//set form errors
						$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
					}//end if
				}//end catch
			}//end if
		}//end if

		return array("form" => $form);
    }//end function


    public function editContactAction()
    {
    	//set contact id
    	$reg_id = $this->params()->fromRoute("id", "");

    	if ($reg_id == "")
    	{
    		//contact id is not, set error message and return to index page
    		$this->flashMessenger()->addErrorMessage("Contact could not be loaded. Id is not set");

    		return $this->redirect()->toRoute("front-contacts");
    	}//end if

    	//extract form id from url
    	$form_id = $this->params()->fromQuery("fid", "");
    	$form = $this->getContactsModel()->getContactProfileForm($form_id);

    	//select contact profile form to use
    	if (is_array($form) && $form_id == "")
    	{
    		//check if form id is set in user session
    		$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    		if (isset($objUserStorage->readUserNativePreferences()->cpp_form_id) && $objUserStorage->readUserNativePreferences()->cpp_form_id != "")
    		{
    			$form_id = $objUserStorage->readUserNativePreferences()->cpp_form_id;
    			$form = $this->getContactsModel()->getContactProfileForm($form_id);
    		} else {
    			//redirect to select profile form action
    			return $this->redirect()->toUrl($this->url()->fromRoute("front-contacts", array("action" => "select-profile-form")) . "?redirect=" . $this->url()->fromRoute("front-contacts", array("action" => "edit-contact", "id" => $reg_id)));
    		}//end if
    	}//end if

    	//check if cpp form is available
    	if (!is_string($form_id))
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Contact Profile Page could not be loaded. Form is not defined");

    		//redirect to index page
    		return $this->redirect()->toRoute("front-contacts");
    	}//end if

    	//load the form
    	if (!$form instanceof \Zend\Form\Form)
    	{
    		$form = $this->getContactsModel()->getContactProfileForm($form_id);
    	}//end if

    	//load the contact
    	$objContact = $this->getContactsModel()->fetchContact($reg_id);

    	//bind data to form
    	$form->bind($objContact);

    	//load the request
    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		//populate form
    		$form->setData($request->getPost());

    		//validate the form
    		if ($form->isValid($request->getPost()))
    		{
    			try {
    				$objContact = $form->getData();
    				$objContact->set("id", $reg_id);

    				//update the contact
    				$objContact = $this->getContactsModel()->updateContact($objContact, $form_id);

    				//set success message
    				$this->flashMessenger()->addSuccessMessage("Contact update successfully");

    				//redirect to contact profile page
    				return $this->redirect()->toRoute("front-contacts", array("action" => "view-contact", "id" => $objContact->get("reg_id")));
    			} catch (\Exception $e) {
					//extract errors from the request return by the API
					$arr_response = explode("||", $e->getMessage());
					$objResponse = json_decode($arr_response[1]);

					if (is_object($objResponse))
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
								//set form errors
								$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
								break;
						}//end switch
					} else {
						//set form errors
						$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
					}//end if
    			}//end catch
    		}//end if
    	}//end if

    	return array(
    			"form" => $form,
    			"reg_id" => $reg_id,
    			"objContact" => $objContact,
    	);
    }//end function

    public function deleteContactAction()
    {

    }//end function

    public function selectProfileLayoutAction()
    {
    	$request = $this->getRequest();
    	$form = new \Zend\Form\Form;

    	//load contact profile forms
    	$arr_forms = $this->getContactsModel()->getContactProfileForm();

    	//set no option
    	$arr_element_value_options["none"] = "No Layout";

    	//create radio button options
    	foreach ($arr_forms as $key => $form_name)
    	{
    		if (is_numeric($key))
    		{
    			$arr_element_value_options[$key] = $form_name;
    		}//end if
    	}//end foreach

    	//add radio group to form
    	$form->add(array(
    			"type" => "radio",
    			"name" => "cpp_form_id",
    			"options" => array(
    					"label" => "Please select the form you would like to use:",
    					"value_options" => $arr_element_value_options,
    			),
    	));

    	//add remember option radio button
    	$form->add(array(
    			'type' => 'checkbox',
    			'name' => 'remember_layout',
    			'attributes' => array(
    					'id' => 'remember_layout',
    			),
    			'options' => array(
    					'label' => 'Remember my option',
    					'use_hidden_element' => true,
    					'checked_value' => '1',
    					'unchecked_value' => '0'
    			)
    	));

    	$form->add(array(
    			"name" => "submit",
    			"attributes" => array(
    					"value" => "Submit",
    			),
    			"options" => array(
    					"ignore" => TRUE,
    			),
    	));

    	//check if local storage has been enabled
    	$arr_config = $this->getServiceLocator()->get("config");
    	if (!isset($arr_config["logged_in_user_settings"]))
    	{
    		$storage_disabled = TRUE;
    	} elseif (isset($arr_config["logged_in_user_settings"]) && $arr_config["logged_in_user_settings"]["storage_enabled"] !== TRUE) {
    		$storage_disabled = TRUE;
    	}//end if
    	
    	if (isset($storage_disabled))
    	{
			$form->remove("remember_layout");
    	}//end if
    	
    	//load user session data
    	$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    	if (isset($objUserStorage->readUserNativePreferences()->cpp_layout_id) && $objUserStorage->readUserNativePreferences()->cpp_layout_id != "")
    	{
    		$form->get("cpp_form_id")->setValue($objUserStorage->readUserNativePreferences()->cpp_layout_id);
    	}//end if

    	if ($request->isPost()) {

            //validate form submitted
    		$form->setData($request->getPost());
    		if ($form->isValid()) {

                $arr_form_data = $form->getData();
    			$form_id = $arr_form_data["cpp_form_id"];

    			if (isset($arr_form_data["remember_layout"]) && $arr_form_data["remember_layout"] == 1) 
    			{
    				//persist user preference
    				$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    				if (is_object($objUserStorage))
    				{
    					$objUserStorage->setUserNativePreferences('cpp_layout_id', $form_id);
    				}//end if
    			}//end if

    			//redirect back to the index page if no layout is selected
    			if ($arr_form_data["cpp_form_id"] == "none")
    			{
    				return $this->redirect()->toRoute("front-contacts");
    			}//end if

    			//redirect back to the contact edit screen with form id specified
    			$url = $this->url()->fromRoute("front-contacts");

    			//execute redirect
    			$response = $this->getResponse();
    			$response->getHeaders()->addHeaderLine('Location', $url . "?fid=$form_id");
    			$response->setStatusCode(302);
    			return $response;
    		}//end if
    	}//end if

    	return array("form" => $form);
    }//end function

    public function selectProfileFormAction()
    {
    	$request = $this->getRequest();
    	$form = new \Zend\Form\Form;
    	$redirect = $this->params()->fromQuery("redirect", "");

    	//load contact profile forms
    	$arr_forms = $this->getContactsModel()->getContactProfileForm();

    	//create radio button options
    	foreach ($arr_forms as $key => $form_name) {
   			$arr_element_value_options[$key] = $form_name;
    	} //end foreach

    	//add radio group to form
    	$form->add(array(
    			"type" => "radio",
    			"name" => "cpp_form_id",
    			"options" => array(
    				"label" => "Please select the form you would like to use:",
    				"value_options" => $arr_element_value_options,
    			),
    	));

    	//add remember option radio button
    	$form->add(array(
             'type' => 'checkbox',
             'name' => 'remember_form',
             'options' => array(
                     'label' => 'Remember my option',
                     'use_hidden_element' => true,
                     'checked_value' => '1',
                     'unchecked_value' => '0'
             )
    	));

    	$form->add(array(
    			"name" => "submit",
    			"attributes" => array(
    					"value" => "Submit",
    			),
    			"options" => array(
    					"ignore" => TRUE,
    			),
    	));

    	//check if local storage has been enabled
    	$arr_config = $this->getServiceLocator()->get("config");
    	if (!isset($arr_config["logged_in_user_settings"]))
    	{
    		$storage_disabled = TRUE;
    	} elseif (isset($arr_config["logged_in_user_settings"]) && $arr_config["logged_in_user_settings"]["storage_enabled"] !== TRUE) {
    		$storage_disabled = TRUE;
    	}//end if
    	 
    	if (isset($storage_disabled))
    	{
    		$form->remove("remember_form");
    	}//end if
    	
    	//load user session data
    	$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    	if (is_numeric($objUserStorage->readUserNativePreferences()->cpp_form_id))
    	{
    		$form->get("cpp_form_id")->setValue($objUserStorage->readUserNativePreferences()->cpp_form_id);
    	}//end if

    	if ($request->isPost())
    	{
    		//validate form submitted
    		$form->setData($request->getPost());
    		if ($form->isValid())
    		{
    			$arr_form_data = $form->getData();
    			$form_id = $arr_form_data["cpp_form_id"];

    			if (isset($arr_form_data["remember_form"]) && $arr_form_data["remember_form"] == 1)
    			{
    				//persist user preference
    				if (is_object($objUserStorage))
    				{
    					$objUserStorage->setUserNativePreferences('cpp_form_id', $form_id);
    					$objUserData->cookie_data->cpp_form_id = $form_id;
    				}//end if
    			}//end if

    			//check if redirect has been specified
    			if ($redirect != "")
    			{
    				//redirect received
    				return $this->redirect()->toUrl($redirect . "?fid=$form_id");
    			}//end if

    			//redirect back to the contact edit screen with form id specified
    			$url = $this->url()->fromRoute("front-contacts", array("action" => "create-contact"));

    			//execute redirect
    			$response = $this->getResponse();
    			$response->getHeaders()->addHeaderLine('Location', $url . "?fid=$form_id");
    			$response->setStatusCode(302);
    			return $response;
    		}//end if
    	}//end if

    	return array("form" => $form, "redirect" => $redirect);
    }//end function

    /**
     * Load toolkit sections available
     * @return \Zend\View\Model\JsonModel
     */
    public function iframeContactToolkitSectionsAction()
    {
    	//set layout to toolkit
    	$this->layout('layout/toolkit-parent');

    	$contact_id = $this->params()->fromRoute("id", "");
    	//load contact
    	$objContact = $this->getContactsModel()->fetchContact($contact_id);

    	$arr = array(
	    			"comments" 			=> array("title" => "Comments", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-comments", "id" => $contact_id))),
	    			"forms" 			=> array("title" => "Forms Completed", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-forms-completed", "id" => $contact_id))),
	    			"journeys"			=> array("title" => "Journeys", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-journeys", "id" => $contact_id))),
	    			"status-history" 	=> array("title" => "Contact Status", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-status-history", "id" => $contact_id))),
    				"user-tasks"		=> array("title" => "To-do", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-user-tasks", "id" => $contact_id))),
    				"sales-funnels"		=> array("title" => "Trackers", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-sales-funnels", "id" => $contact_id))),
    	);

    	//check plugins enabled
    	$objUser = \FrontUserLogin\Models\FrontUserSession::isLoggedIn();
    	if (!in_array("to_do_list", $objUser->profile->plugins_enabled))
    	{
    		unset($arr["user-tasks"]);
    	}//end if

    	if (!in_array("sales_funnels", $objUser->profile->plugins_enabled))
    	{
    		unset($arr["sales-funnels"]);
    	}//end if

    	return array(
    		"arr_sections" => $arr,
    		"objContact" => $objContact,
    	);
    }//end function

    public function updateContactSystemFieldsAction()
    {
    	//set layout for dialog
    	$this->layout("layout/layout-body");

    	$contact_id = $this->params()->fromRoute("id", "");

    	//load the contact
    	$objContact = $this->getContactsModel()->fetchContact($contact_id);

		//load form
		$form = $this->getContactsModel()->getContactSystemFieldsForm();

    	//manually set form data
    	$form->get("source")->setValue($objContact->get("source"));
    	$form->get("reference")->setValue($objContact->get("reference"));
    	$form->get("user_id")->setValue($objContact->get("user_id"));

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$form->remove("source_dropdown");
    		$form->remove("reference_dropdown");

    		$form->setData($request->getPost());
    		if ($form->isValid($request->getPost()))
    		{
    			//allocate the data
    			$objContact->set($form->getData());

    			//request update
    			try {
    				$this->getContactsModel()->updateContact($objContact, "systemfields");
    				echo json_encode(array("error" => 0, "response" => "Contact details updated"), JSON_FORCE_OBJECT); exit;
    			} catch (\Exception $e) {
    				echo json_encode(array("error" => 1, "response" => $e->getMessage()), JSON_FORCE_OBJECT); exit;
    			}//end try
    		}//end if
    	}//end if

    	return array(
    		"contact_id" => $contact_id,
    		"form" => $form,
    	);
    }//end function

    public function ajaxLoadSourceListAction()
    {
    	//load form
    	$form = $this->getContactsModel()->getContactSystemFieldsForm();
    	$arr_source_data = $form->get('source_dropdown')->getValueOptions();
    	return new JsonModel($arr_source_data);
    }//end function
    
    /**
     * Create an instance of the Contacts Model using the Service Manager
     * @return \FrontContacts\Models\FrontContactsModel
     */
	private function getContactsModel()
	{
		if (!$this->model_contacts)
		{
			$this->model_contacts = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsModel");
		}//end if

		return $this->model_contacts;
	}//end function

	/**
	 * Create an instance of the Forms Admin model using the Service Manager
	 * @return \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private function getFormAdminModel()
	{
		if (!$this->model_forms_admin)
		{
			$this->model_forms_admin = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontFormAdminModel");
		}//end function

		return $this->model_forms_admin;
	}//end function

	private function getUsersModel()
	{
		if (!$this->model_users)
		{
			$this->model_users = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersModel");
		}//end if

		return $this->model_users;
	}//end function

	/**
	 * Create an instance of the Front Contacts System Fields Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsSystemFieldsModel
	 */
	private function getFrontContactsSystemFieldsModel()
	{
		if (!$this->model_front_contact_system_fields)
		{
			$this->model_front_contact_system_fields = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsSystemFieldsModel");
		}//end if

		return $this->model_front_contact_system_fields;
	}//end function

	/**
	 * Create an instance of the Front Contact Status Model using the Service Manager
	 * @return \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private function getContactStatusModel()
	{
		if (!$this->model_contact_statuses)
		{
			$this->model_contact_statuses = $this->getServiceLocator()->get("FrontStatuses\Models\FrontContactStatusesModel");
		}//end if

		return $this->model_contact_statuses;
	}//end function
}//end class
