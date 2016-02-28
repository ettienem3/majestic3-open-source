<?php
namespace FrontFormAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

/**
 * Deals with fields within the fields pool
 * @author ettiene
 *
 */
class FieldsController extends AbstractActionController
{
	/**
	 * Container for the FrontFieldAdminModel
	 * @var \FrontFormAdmin\Models\FrontFieldAdminModel
	 */
	private $model_fields_admin;

	/**
	 * List all available custom fields
	 * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
	 */
    public function indexAction()
    {
    	$arr_params = (array) $this->params()->fromQuery();
    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$arr_params = array_merge($arr_params, (array) $request->getPost());
    	}//end foreach

        $objFields = $this->getFieldsModel()->fetchCustomFields($arr_params);
        return array(
        		"objFields" => $objFields,
        		"arr_params" => $arr_params,
        );
    }//end function

    public function ajaxSearchValuesAction()
    {
    	try {
    		switch($this->params()->fromQuery("param"))
    		{
    			case "fields_custom_type_id":
    				//load field types
    				$form = $this->getFieldsModel()->getFieldAdminForm();

    				$arr_field_types = $form->get("fk_field_type_id")->getValueOptions();
    				foreach ($arr_field_types as $key => $value)
    				{
    					$arr_data[] = array("id" => $key, "val" => $value);
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

    /**
     * Create a new custom field
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Zend\Form\Form
     */
    public function createFieldAction()
    {
		$form = $this->getFieldsModel()->getFieldAdminForm();

		$request = $this->getRequest();
		try {
			if ($request->isPost())
			{
				$form->setData($request->getPost());
				if ($form->isValid())
				{
					//create field entity
					$objField = $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminFieldEntity");
					//populate the entity
					$objField->set($form->getData());

					//create the field
					$objField = $this->getFieldsModel()->createCustomField($objField);

					//set success message
					$this->flashMessenger()->addSuccessMessage($objField->get("description") . " created successfully");

					//should we redirect back to the form instead?
					if ($this->params()->fromQuery("fid", "") != "")
					{
						$this->flashMessenger()->addInfoMessage("A new field has been created. To allocate the field to the form, complete the details below");

						//redirect to form allocate field page
						return $this->redirect()->toRoute("front-form-admin/form-fields", array(
												"action" => "assign-field",
												"form_id" => $this->params()->fromQuery("fid"),
												"field_id" => $objField->get("id"),
												"field_type" => "custom",
										));
					}//end if

					//redirect to fields index page
					return $this->redirect()->toRoute("front-form-admin/fields", array("action" => "index"));
				}//end if
			}//end if
		} catch (\Exception $e) {
			//set error message
			$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
		}//end catch

		return array("form" => $form);
    }//end function

    /**
     * Update an existing custom field
     */
    public function editFieldAction()
    {
		//get the field id
		$field_id = $this->params()->fromRoute("id", "");

		if ($field_id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Field could not be loaded. Id is not set");
			//redirect to index page
			return $this->redirect()->toRoute("front-form-admin/fields", array("action" => "index"));
		}//end if

		//load the field
		$objField = $this->getFieldsModel()->getCustomField($field_id);

		//load form
		$form = $this->getFieldsModel()->getFieldAdminForm();
		//bind the data to the form
		$form->bind($objField);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			try {
				if ($form->isValid())
				{
					$objField = $form->getData();
					$objField->set("id", $field_id);

					//update the field
					$objField = $this->getFieldsModel()->updateCustomField($objField);

					//set success message
					$this->flashMessenger()->addSuccessMessage($objField->get("description") . " has been saved");
					//redirect to index page
					return $this->redirect()->toRoute("front-form-admin/fields", array("action" => "index"));
				}//end if
			} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
			}//end catch
		}//end if

		return array(
				"form" => $form,
				"objField" => $objField,
		);
    }//end function

    /**
     * Delete an existing custom field
     */
    public function deleteFieldAction()
    {
    	//get the field id
    	$field_id = $this->params()->fromRoute("id", "");

    	if ($field_id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Field could not be loaded. Id is not set");
    		//redirect to index page
    		return $this->redirect()->toRoute("front-form-admin/fields");
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
			if (strtolower($request->getPost("del")) != "delete")
			{
				//set cancel message
				$this->flashMessenger()->addInfoMessage("Field not deleted. Action was cancelled");
				//redirect back to the fields index page
				return $this->redirect()->toRoute("front-form-admin/fields", array("action" =>"index"));
			}//end if

	    	try {
	    		$objField = $this->getFieldsModel()->deleteCustomField($field_id);
	    		//set message
	    		$this->flashMessenger()->addSuccessMessage($objField->get("description") . " has been deleted");
	    	} catch (\Exception $e) {
					//set error message
    				$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
	    	}//end catch

	    	//redirect to index page
	    	return $this->redirect()->toRoute("front-form-admin/fields", array("action" => "index"));
    	}//end if

    	//load field details
    	$objField = $this->getFieldsModel()->getCustomField($field_id);

    	return array(
    			"objField" => $objField
    	);
    }//end function

    /**
     * Create toolkit section for fields
     * Replace fields
     * Generic fields
     * File library
     */
    public function ajaxFieldsToolkitSectionAction()
    {
    	//pickup vars for elements to attach
    	$arr = array(
    			array("title" => "Generic Fields", "url" => $this->url()->fromRoute("front-form-admin/generic-fields", array("action" => "ajax-index"))),
    			array("title" => "Replace Fields", "url" => $this->url()->fromRoute("front-form-admin/replace-fields", array("action" => "ajax-index"))),
//     			array("title" => "Links", "url" => $this->url()->fromRoute()),
    	);

    	return new JsonModel($arr);
    }//end function

    public function iframeFieldsToolkitSectionAction()
    {
    	//set layout to toolkit
    	$this->layout('layout/toolkit-parent');

    	$arr_selection = array();

    	if ($this->params()->fromQuery("generic-fields") == 1)
    	{
    		$arr_selection["generic-fields"] = array("title" => "Generic Fields", "url" => $this->url()->fromRoute("front-form-admin/generic-fields", array("action" => "ajax-index")));
    	}//end if

    	if ($this->params()->fromQuery("replace-fields") == 1)
    	{
    		$arr_selection["replace-fields"] = array("title" => "All Fields", "url" => $this->url()->fromRoute("front-form-admin/replace-fields", array("action" => "ajax-index")));
    	}//end if

    	if ($this->params()->fromQuery("links") == 1)
    	{
    		//$arr_selection["links"] = array("title" => "Links", "url" => $this->url()->fromRoute("front-form-admin/generic-fields", array("action" => "ajax-index")));
    	}//end if

		return array(
				"arr_sections" => $arr_selection,
		);
    }//end function

    /**
     * Retrieve field values from the API
     * This is used to obtain values for a field where the field is either dropdown or radio field type
     */
    public function ajaxLoadSpecifiedFieldValuesAction()
    {
    	//get the field id
    	$field_id 					= $this->params()->fromRoute("id", "");
    	$field_type 				= $this->params()->fromQuery("field_type", "");
    	$fields_all_id 				= $this->params()->fromQuery("fields_all_id");
    	$include_field_values 		= $this->params()->fromQuery("include_field_values", 0);

    	if ($field_id == "" || $field_type == "")
    	{
    		//set error message
    		return new JsonModel(array("error" => "Field information could not be loaded. Field id or Field Type is not available"));
    	}//end if

    	//load the field
    	switch (strtolower($field_type))
    	{
    		case "standard":
    			$objField = $this->getFieldsModel()->fetchStandardField($field_id, $include_field_values);
    			break;

    		case "custom":
    			$objField = $this->getFieldsModel()->fetchCustomField($field_id, $include_field_values);
    			break;

    		default:
    			//set error message
    			return new JsonModel(array("error" => "Field information could not be loaded. Field Type specifid is unknown"));
    			break;
    	}//end switch

    	//return data
    	return new JsonModel($objField->getArrayCopy());
    }//end function

    /**
     * Create an instance of the FrontFieldAdminModel usign the Service Manager
     * @return \FrontFormAdmin\Models\FrontFieldAdminModel
     */
    private function getFieldsModel()
    {
    	if (!$this->model_fields_admin)
    	{
    		$this->model_fields_admin = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontFieldAdminModel");
    	}//end if

    	return $this->model_fields_admin;
    }//end function
}//end class
