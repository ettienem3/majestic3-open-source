<?php
namespace FrontFormAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

/**
 * Dealing with fields assigned to a specific form
 * @author ettiene
 *
 */
class FormFieldsController extends AbstractActionController
{
	/**
	 * Container for FrontFormAdminModel
	 * @var \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private $model_form_admin;

	/**
	 * Container for FrontFormAdminFieldModel
	 * @var \FrontFormAdmin\Models\FrontFieldAdminModel
	 */
	private $model_fields;

	/**
	 * Container for the Front Behaviours Config Model
	 * @var \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
	 */
	private $model_front_behaviours_config;

	/**
	 * Lists fields that are available
	 */
	public function indexAction()
	{
		$form_id = $this->params()->fromRoute("form_id", "");

		if ($form_id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Form could not be loaded");
			//redirect back to form index page
			return $this->redirect()->toRoute("front-form-admin");
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if ($request->getPost('fields_custom_description'))
			{
				
			}//end if
		}//end if
		
		//load form details
		$objForm = $this->getFormAdminModel()->getForm($form_id);

		//load fields
		$objStandardFields = $this->getFieldsModel()->fetchStandardFields(array("active" => 1));
		
		//set params for custom fields
		$arr_custom_fields_params = array();
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			if ($request->getPost('fields_custom_description') != '')
			{
				$arr_custom_fields_params['fields_custom_description'] = trim($request->getPost('fields_custom_description'));
			}//end if
		}//end if
		
		if ($this->params()->fromQuery('qp_cf_limit', '') != '')
		{
			$arr_custom_fields_params['qp_limit'] = $this->params()->fromQuery('qp_cf_limit');
		} else {
			$arr_custom_fields_params['qp_limit'] = 60;
		}//end if
		
		if ($this->params()->fromQuery('qp_cf_start', '') != '')
		{
			$arr_custom_fields_params['qp_start'] = $this->params()->fromQuery('qp_cf_start');
		}//end if

		//load custom fields
		$objCustomFields = $this->getFieldsModel()->fetchCustomFields($arr_custom_fields_params);

		return array(
				"objForm" 				=> $objForm,
				"objStandardFields" 	=> $objStandardFields,
				"objCustomFields" 		=> $objCustomFields,
		);
	}//end function

	/**
	 * Add a field to a form
	 * This could either be a standard field, or a custom field
	 */
	public function assignFieldAction()
    {
    	//gather params
		$form_id = $this->params()->fromRoute("form_id", "");
		$field_type = $this->params()->fromRoute("field_type", "standard");
		$field_id = $this->params()->fromRoute("field_id", "");

		if ($form_id == "" || $field_id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Field could not be allocated. Form Id or Field id is not available");
			//redirect back to form index page
			return $this->redirect()->toRoute("front-form-admin");
		}//end if

		//load form details
		$objForm = $this->getFormAdminModel()->getForm($form_id);

		//load field data
		switch (strtolower($field_type))
		{
			case "standard":
				$objField = $this->getFieldsModel()->getStandardField($field_id);
				break;

			case "custom":
				$objField = $this->getFieldsModel()->getCustomField($field_id);
				break;

			default:
				//set error message
				$this->flashMessenger()->addErrorMessage("Field could not be allocated. Field type is invalid");
				//redirect back to form index page
				return $this->redirect()->toRoute("front-form-admin");
				break;
		}//end switch

		//load form
		$form = $this->getFormAdminModel()->getFormFieldAdminForm();

		//remove display in cpp index field if form is not a cpp form
		if ($objForm->get("behaviour") !== "__cpp")
		{
			$form->get("display_on_index")->setAttribute("type", "hidden");
		}//end if

		$request = $this->getRequest();

		if ($request->isPost())
		{
			//populate the form
			$form->setData($request->getPost());

			if ($form->isValid())
			{
				try {
					//create a new field entity
					$objFormField = $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity");
					$objField->set($form->getData());
					$objFormField->set($objField->getArrayCopy());
					$objFormField = $this->getFormAdminModel()->allocateFieldtoForm($objFormField, $objForm, $field_type);

					//set success message
					$this->flashMessenger()->addSuccessMessage($objFormField->get("description") . " added to Form (" . $objForm->get("form") . ")");
					//redirect to form page
					return $this->redirect()->toRoute("front-form-admin/form", array("action" => "edit-form", "id" => $objForm->get("id")));
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if

		//bind data late in order to recreate field entity above
		$form->bind($objField);

		return array(
				"form_id" 				=> $form_id,
				"field_id"				=> $field_id,
				"field_type"			=> $field_type,
				"form" 					=> $form,
				"objForm" 				=> $objForm,
				"objField"				=> $objField,
		);
    }//end function

    public function ajaxCppFieldDisplayAction()
    {
    	//gather params
    	$form_id = $this->params()->fromRoute("form_id", "");
    	$field_type = $this->params()->fromRoute("field_type", "standard");
    	$field_id = $this->params()->fromRoute("field_id", "");
    	$form_field_id = $this->params()->fromQuery("form_field_id");

    	if ($form_id == "" || $field_id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Field could not be updated. Form ID or Field ID is not available");
    		//redirect back to form index page
    		return $this->redirect()->toRoute("front-form-admin");
    	}//end if

    	//load form details
    	$objForm = $this->getFormAdminModel()->getForm($form_id);
    	foreach ($objForm->getFormFieldEntities() as $objField)
    	{
    		if ($objField->get("id") == $form_field_id)
    		{
    			//update the field
    			$objField->set("display_on_index", (1 - $objField->get("display_on_index")));

    			try {
	    			//save the data
	    			$objField = $this->getFormAdminModel()->updateFormField($objField);
	    			echo json_encode(array(
	    					"error" => 0,
	    					"response" => $objField->get("display_on_index"),
	    				),
	    					JSON_FORCE_OBJECT);
	    			exit;
    			} catch (\Exception $e) {
    				//set error message
    				echo json_encode(array(
    						"error" => 1,
    						"response" => $e->getMessage(),
    					),
    						JSON_FORCE_OBJECT);
    				exit;
    			}//end catch
    		}//end if
    	}//end foreach
    }//end function

    public function autoAssignFieldAction()
    {
    	//gather params
    	$form_id = $this->params()->fromRoute("form_id", "");
    	$field_type = $this->params()->fromRoute("field_type", "standard");
    	$field_id = $this->params()->fromRoute("field_id", "");

    	if ($form_id == "" || $field_id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Field could not be allocated. Form ID or Field ID is not available");
    		//redirect back to form index page
    		return $this->redirect()->toRoute("front-form-admin");
    	}//end if

    	//load form details
    	$objForm = $this->getFormAdminModel()->getForm($form_id);

    	//load field data
    	switch (strtolower($field_type))
    	{
    		case "standard":
    			$objField = $this->getFieldsModel()->getStandardField($field_id);
    			break;

    		case "custom":
    			$objField = $this->getFieldsModel()->getCustomField($field_id);
    			break;

    		default:
    			//set error message
				echo json_encode(array(
					"error" => 1,
					"response" => "Field could not be allocated. Field type is invalid",
				),
				JSON_FORCE_OBJECT);
				exit;
    			break;
    	}//end switch

    	try {
    		//create a new field entity
    		$objFormField = $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity");
    		$arr_field_data = $objField->getArrayCopy();

    		//set some default values
    		$arr_field_data["css_style_text"] 			= "";
    		$arr_field_data["active"] 					= "1";
    		$arr_field_data["css_style2"]		 		= "";
    		$arr_field_data["mandatory"] 				= "0";
    		$arr_field_data["readonly"] 				= "0";
    		$arr_field_data["default_content"] 			= "0";
    		$arr_field_data["populate"] 				= "1";
    		$arr_field_data["hidden"] 					= "0";
    		$arr_field_data["hidden_not_logged_in"] 	= "0";
    		$arr_field_data["display_on_index"] 		= "0";
    		$arr_field_data["field_order"]				= "";
    		$arr_field_data["field_duplicate"]			= "";

    		$objFormField->set($arr_field_data);
    		$objFormField = $this->getFormAdminModel()->allocateFieldtoForm($objFormField, $objForm, $field_type);

    		//set success message
			echo json_encode(array(
				"error" => 0,
				"response" => "Field has been allocated",
			),
			JSON_FORCE_OBJECT);
			exit;
    	} catch (\Exception $e) {
    		//set success message
			echo json_encode(array(
				"error" => 1,
				"response" => $this->frontControllerErrorHelper()->formatErrors($e) . ". Try allocating the field manually",
			),
			JSON_FORCE_OBJECT);
			exit;
    	}//end catch
    }//end function

    /**
     * Edit a field allocated a form
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Zend\Form\Form
     */
    public function editFieldAction()
    {
    	//gather params
    	$form_id = $this->params()->fromRoute("form_id", "");
    	$field_id = $this->params()->fromRoute("field_id", "");
    	$field_type = $this->params()->fromRoute("field_type", "");

    	if ($form_id == "" || $field_id == "" || $field_type == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Field could not be loaded. Form/Field id or Field type is not available");
    		//redirect back to form index page
    		return $this->redirect()->toRoute("front-form-admin");
    	}//end if

    	//load form details
    	$objForm = $this->getFormAdminModel()->getForm($form_id);

    	//load field data
		$objField = $this->getFormAdminModel()->getFormField($form_id, $field_id, $field_type);

		//add field type to entity
		$objField->set("url_field_type", strtolower($field_type));

    	//load form
    	$form = $this->getFormAdminModel()->getFormFieldAdminForm();

    	//remove display in cpp index field if form is not a cpp form
    	if ($objForm->get("form_types_behaviour") !== "__cpp")
    	{
    		$form->remove("display_on_index");
    	}//end if

    	$request = $this->getRequest();

    	if ($request->isPost())
    	{
    		//populate the form
    		$form->setData($request->getPost());
    		if ($form->isValid())
    		{
    			try {
    				$objField->set($request->getPost());
    				//update the field
    				$objFormField = $this->getFormAdminModel()->updateFormField($objField);

    				//set success message
    				$this->flashMessenger()->addSuccessMessage($objFormField->get("description") . " has been updated");
    				//redirect to form page
    				return $this->redirect()->toRoute("front-form-admin/form", array("action" => "edit-form", "id" => $form_id));
    			} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			}//end catch
    		}//end if
    	}//end if

    	//bind data late in order to recreate field entity above
    	$form->bind($objField);

    	//assign data to view
    	$arr_view_data = array(
    			"form_id" => $form_id,
    			"field_id" => $field_id,
    			"field_type" => $field_type,
    			"form" => $form,
    			"objField" => $objField,
    			"objForm" => $objForm,
    	);

    	//set approriate view for form and field
    	switch (strtolower(str_replace("_", "", $objForm->get("form_types_behaviour"))))
    	{
    		case "salesfunnel":
    		case "tracker":
    			//make some changes to the form
    			$form->remove("field_duplicate");
    			$form->remove("hidden");
    			$form->remove("hidden_not_logged_in");
    			$form->add(array(
    				"name" => "field_duplicate",
    				"type" => "hidden",
    				"attributes" => array(
    					"value" => 0,
    				)
    			));

    			$form->add(array(
    					"name" => "hidden",
    					"type" => "hidden",
    					"attributes" => array(
    							"value" => 0,
    					)
    			));

    			$form->add(array(
    					"name" => "hidden_not_logged_in",
    					"type" => "hidden",
    					"attributes" => array(
    							"value" => 0,
    					)
    			));

    			$arr_view_data["form"] = $form;

    			//generate view object
    			$objView = new ViewModel($arr_view_data);
    			$objView->setTemplate('form-fields-layout/edit-tracker-field');
    			return $objView;
    			break;
    	}//end switch

    	return $arr_view_data;
    }//end function

    /**
     * Remove a field allocated to a form
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function removeFieldAction()
    {
    	//gather params
    	$form_id = $this->params()->fromRoute("form_id", "");
    	$field_id = $this->params()->fromRoute("field_id", "");
    	$field_type = $this->params()->fromRoute("field_type", "");

    	if ($form_id == "" || $field_id == "" || $field_type == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Field could not be loaded. Form/Field id or Field type is not available");
    		//redirect back to form index page
    		return $this->redirect()->toRoute("front-form-admin");
    	}//end if

    	//load field data
    	$objField = $this->getFormAdminModel()->getFormField($form_id, $field_id, $field_type);

    	//add field type to entity
    	$objField->set("url_field_type", strtolower($field_type));
//@TODO add delete confirmation
    	try {
    		//remove the field
    		$this->getFormAdminModel()->removeFormField($objField);

    		//set success message
    		$this->flashMessenger()->addSuccessMessage("'" . $objField->get("description") . "' field successfully removed");
    	} catch (\Exception $e) {
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Field could not be removed from form. " . $this->frontControllerErrorHelper()->formatErrors($e));
    	}//end catch

    	//redirect back to the form edit view
    	return $this->redirect()->toRoute("front-form-admin/form", array("action" => "edit-form", "id" => $form_id));
    }//end function

    /**
     * Load and manage Form Field Behaviours
     */
    public function formFieldBehavioursAction()
    {
	    //set layout
	    $this->layout("layout/behaviours-view");

	    //set data array to collect behaviours and pass url data to view
	    $arr_behaviour_params = array(
	    		"form_id" => $this->params()->fromRoute("form_id"),
	    		"field_id" => $this->params()->fromQuery("fields_all_id"),
	    		"behaviour" => "form_fields",
	    );

		//load behaviours form
		try {
			$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm("form_fields", $arr_behaviour_params);
		} catch (\Exception $e) {
			//$this->flashMessenger()->addErrorMessage($e->getMessage());
			$viewModel = new ViewModel(array(
					//existing behaviours
					"objBehaviours" 		=> $objBehaviours,
					//behaviour params
					"arr_behaviour_params" 	=> $arr_behaviour_params,
					//action descriptions
					"arr_descriptors" 		=> $arr_descriptors,
					//load form data
					"objForm" 				=> $objForm,
					//load form field
					"objFormFieldElement"	=> $objFormFieldElement,
					//set header
					"behaviours_header" 	=> "Behaviours configured for <span class=\"text-info\">Form Fields</span>",
			));
			$viewModel->setTemplate('front-behaviours-config/index/configure-behaviours.phtml');
			return $viewModel;
		}//end catch
		
		$form = $arr_config_form_data["form"];
		$arr_descriptors = $arr_config_form_data["arr_descriptors"];

	    //load current field behaviours...
	    $objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);

	    //load fields available of form
	    $objForm = $this->getFormAdminModel()->getForm($this->params()->fromRoute("form_id"));

	    //extract field data
	    foreach ($objForm->getFormFieldEntities() as $objElement)
	    {
	    	if ($objElement->get("id") == (int) $this->params()->fromQuery("fields_all_id"))
	    	{
	    		$objFormFieldElement = $objElement;
	    		break;
	    	}//end if
	    }//end foreach

	    //check if behaviour is being reconfigured
	    if (is_numeric($this->params()->fromQuery("behaviour_id", "")))
	    {
	    	$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviour($this->params()->fromQuery("behaviour_id"));
	    } else {
	    	$objBehaviour = FALSE;
	    }//end if

	    $request = $this->getRequest();
	    if ($request->isPost())
	    {
	    	$form->setData($request->getPost());

	    	if ($form->isValid())
	    	{
	    		$arr_params = $form->getData();
	    		$arr_params["behaviour"] = "form_fields";
	    		
	    		//add some additional values to assist in generating the correct form
	    		$arr_params['form_id'] = $arr_behaviour_params['form_id'];
	    		$arr_params['field_id'] = $arr_behaviour_params['field_id'];
	    		$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_params);

	    		//extract field information
				if (is_numeric($objFormFieldElement->get('fields_std_id')))
				{
					//standard field
					$field_value = ucwords($objFormFieldElement->get('fields_std_field'));
				} else {
					//custom field
					$field_value = ucwords($objFormFieldElement->get('fields_custom_field'));
				}//end if
	    		
	    		//check if a local defined form exists for the behaviour, sometime needed since the api wont render the form correctly
	    		$class = "\\FrontBehavioursConfig\\Forms\\FormFields\\Behaviour" . str_replace(" ", "", ucwords(str_replace("_", " ", $arr_params['beh_action']))) . $field_value . "Form";
	    		
	    		if (class_exists($class))
	    		{
	    			$form = new $class($form);
	    		}//end if
	    		
	    		//set behaviour action param for view
	    		$arr_behaviour_params["beh_action"] = $arr_params["beh_action"];

	    		//assign data to form is behaviour is being reconfigured
	    		if ($objBehaviour instanceof \FrontBehaviours\Entities\FrontBehavioursBehaviourConfigEntity)
	    		{
	    			$form->bind($objBehaviour);
	    		}//end if

	    		//check if submitted form is the complete behaviour config
	    		if ($this->params()->fromPost("setup_complete", 0) == 1)
	    		{
		    		//revalidate the form
		    		$form->setData($request->getPost());
		    		if ($form->isValid())
		    		{
		    			if ($objBehaviour === FALSE)
		    			{
		    				//set additional params
		    				$arr_form_data = $form->getData();
		    				$arr_form_data["form_id"] = $this->params()->fromRoute("form_id");
		    				$arr_form_data["field_id"] = $this->params()->fromQuery("fields_all_id");

//@TODO field_value is not received from form???
							if ($arr_form_data["field_value"] == "" && $this->params()->fromPost("field_value_label") != "")
							{
								$arr_form_data["field_value"] = $this->params()->fromPost("field_value_label");
							}//end if

		    				//create/update the behaviour
		    				$objBehaviour = $this->getFrontBehavioursModel()->createBehaviourAction($arr_form_data);

		    				//redirect back to the "index" view
		    				return $this->redirect()->toUrl($this->url()->fromRoute("front-form-admin/form-fields", array("action" => "form-field-behaviours", "form_id" => $this->params()->fromRoute("form_id"), "field_id" => $this->params()->fromRoute("field_id"), "field_type" => $this->params()->fromRoute("field_type")))  . "?fields_all_id=" . $this->params()->fromQuery("fields_all_id"));
		    			} else {
		    				//set additional params
		    				$objBehaviour = $form->getData();
		    				$objBehaviour->set("form_id", $this->params()->fromRoute("form_id"));
		    				$objBehaviour->set("field_id", $this->params()->fromQuery("fields_all_id"));

		    				//update the behaviour
		    				$objBehaviour = $this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);

		    				//redirect back to the "index" view
							return $this->redirect()->toUrl($this->url()->fromRoute("front-form-admin/form-fields", array("action" => "form-field-behaviours", "form_id" => $this->params()->fromRoute("form_id"), "field_id" => $this->params()->fromRoute("field_id"), "field_type" => $this->params()->fromRoute("field_type")))  . "?fields_all_id=" . $this->params()->fromQuery("fields_all_id"));
		    			}//end if
		    		}//end if
	    		}//end if
	    	}//end if
	    }//end if

	    $viewModel = new ViewModel(array(
				//form to add behavours
				"form"      			=> $form,
				//existing behaviours
				"objBehaviours" 		=> $objBehaviours,
				//behaviour params
				"arr_behaviour_params" 	=> $arr_behaviour_params,
				//action descriptions
				"arr_descriptors" 		=> $arr_descriptors,
	    		//load form data
	    		"objForm" 				=> $objForm,
	    		//load form field
	    		"objFormFieldElement"	=> $objFormFieldElement,
	    		//set header
	    		"behaviours_header" 	=> "Behaviours configured for <span class=\"text-info\">Form Fields</span>",
	    ));
	    $viewModel->setTemplate('front-behaviours-config/index/configure-behaviours.phtml');

	    return $viewModel;
    }//end function


    /**
     * Load form field groups from api
     */
    public function ajaxLoadFieldGroupsAction()
    {
    	//gather params
    	$form_id = $this->params()->fromRoute("form_id", "");
    	$field_id = $this->params()->fromRoute("field_id", "");
    	$field_type = $this->params()->fromRoute("field_type", "");

    	if ($form_id == "" || $field_id == "" || $field_type == "")
    	{
    		//set error message
    		return new JsonModel(array("error" => "Field information could not be loaded. Form/Field id or Field type is not available"));
    	}//end if

    	$objFieldGroups = $this->getFormAdminModel()->fetchFormFieldGroups($form_id);

    	if (is_object($objFieldGroups))
    	{
    		foreach ($objFieldGroups as $objFieldGroup)
    		{
    			$arr[] = $objFieldGroup->field_group;
    		}//end foreach
    		return new JsonModel($arr);
    	}//end if

    	exit;
    }//end function

    public function ajaxSaveFieldsOrderAction()
    {
    	$form_id = $this->params()->fromRoute("form_id", "");

    	if ($form_id == "")
    	{
    		echo json_encode(array("error" => 1, "response" => "Field Order could not be saved. The Form ID is not set"), JSON_FORCE_OBJECT);
    		exit;
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		foreach ($request->getPost() as $key => $value)
    		{
    			//extract field data
    			$arr_field = explode("_", $key);
    			$field_type = $arr_field[1];
    			$field_id = $arr_field[2];

    			try {
	    			//load field data
    				$objField = $this->getFormAdminModel()->getFormField($form_id, $field_id, $field_type);

    				//set field data
    				$objField->set("field_order", $value);

    				//save the data
    				$objField = $this->getFormAdminModel()->updateFormField($objField);
    			} catch (\Exception $e) {
    				//record errors
    				$arr_errors[] = $e->getMessage();
    			}//end catch
    		}//end foreach
    	}//end if

    	if (is_array($arr_errors))
    	{
    		echo json_encode(array("error" => 1, "response" => implode("<br/>", $arr_errors)), JSON_FORCE_OBJECT);
    	} else {
    		echo json_encode(array("error" => 0, "redirect" => $this->url()->fromRoute("front-form-admin/form", array("action" => "edit-form", "id" => $form_id))), JSON_FORCE_OBJECT);
    	}//end if

    	exit;
    }//end function

    /**
     * Create an instance of the FrontFormAdminModel using the Service Manager
     * @return \FrontFormAdmin\Models\FrontFormAdminModel
     */
    private function getFormAdminModel()
    {
    	if (!$this->model_form_admin)
    	{
    		$this->model_form_admin = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontFormAdminModel");
    	}//end if

    	return $this->model_form_admin;
    }//end function

    /**
     * Create an instance of the FrontFieldAdminModel using the Service Manager
     * @return \FrontFormAdmin\Models\FrontFieldAdminModel
     */
    private function getFieldsModel()
    {
    	if (!$this->model_fields)
    	{
    		$this->model_fields = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontFieldAdminModel");
    	}//end if

    	return $this->model_fields;
    }//end function

    /**
     * Create an instance of the Front Behaviours Config Model using the Service Manager
     * @return \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
     */
    private function getFrontBehavioursModel()
    {
    	if (!$this->model_front_behaviours_config)
    	{
    		$this->model_front_behaviours_config = $this->getServiceLocator()->get("FrontBehavioursConfig\Models\FrontBehavioursConfigModel");
    	}//end if

    	return $this->model_front_behaviours_config;
    }//end function
}//end class
