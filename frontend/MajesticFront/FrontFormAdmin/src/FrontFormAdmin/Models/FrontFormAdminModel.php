<?php
namespace FrontFormAdmin\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontFormAdmin\Entities\FrontFormAdminFormEntity;
use FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity;

/**
 * Manage forms and their associated fields
 * @author ettiene
 *
 */
class FrontFormAdminModel extends AbstractCoreAdapter
{
	/**
	 * Load the admin form for forms from the Core
	 * @param string $form_type - Optional
	 * @return \Zend\Form\Form
	 */
	public function getFormAdminForm($form_type = "")
	{
		//cater for different system forms based on form type
		switch (strtolower(($form_type)))
		{
			case "__cpp":
			case "cpp":
				$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
								->getSystemForm("Core\Forms\SystemForms\Forms\FormsContactProfileForm");
				break;

			case "__tracker":
			case "tracker":
				$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
								->getSystemForm("Core\Forms\SystemForms\Forms\FormsTrackerForm");
				break;

			default:
				$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
								->getSystemForm("Core\Forms\SystemForms\Forms\FormsForm");
				break;
		}//end switch

		//change some form values
		$arr_form_types = $objForm->get("fk_form_type_id")->getValueOptions();
		foreach ($arr_form_types as $k => $v)
		{
			switch (strtolower(str_replace(" ", "", $v)))
			{
				case "salesfunnel":
					$arr_form_types[$k] = "Tracker";
					break;
			}//end switch
		}//end foreach
		$objForm->get("fk_form_type_id")->setValueOptions($arr_form_types);

		return $objForm;
	}//end function

	/**
	 * Load the admin form for form fields from the Core
	 * @return \Zend\Form\Form
	 */
	public function getFormFieldAdminForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm("Core\Forms\SystemForms\Forms\FormFieldsForm");
		return $objForm;
	}//end function

	public function getForms($arr_where = NULL)
	{
return $this->fetchForms($arr_where);
	}//end function

	/**
	 * Load a list of forms
	 * @param array $arr_where - optional
	 */
	public function fetchForms($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms");

		//execute
		$objForms = $objApiRequest->performGETRequest($arr_where)->getBody();

		return $objForms->data;
	}//end function


	public function getForm($id)
	{
return $this->fetchForm($id);
	}//end function

	/**
	 * Load details for a specific form
	 * @param mixed $id
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormEntity
	 */
	public function fetchForm($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/form/$id");

		//execute
		$objForm = $objApiRequest->performGETRequest(array("include_fields" => 1))->getBody();

		//create link entity
		//form data is segmented ->form = form data ->fields = field data
		$entity_form = $this->createFormEntity($objForm->data);

		return $entity_form;
	}//end function

	/**
	 * Create a new form
	 * @param array $arr_data
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormEntity
	 */
	public function createForm($arr_data)
	{
		//create form entity
		$objForm = $this->createFormEntity($arr_data);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objForm" => $objForm));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/form");

		//execute
		$objForm = $objApiRequest->performPOSTRequest($objForm->getArrayCopy())->getBody();

		//recreate link entity
		$objForm = $this->createFormEntity($objForm->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objForm" => $objForm));

		return $objForm;
	}//end function

	/**
	 * Update an existing form
	 * @param FrontFormAdminFormEntity $objForm
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormEntity
	 */
	public function editForm(FrontFormAdminFormEntity $objForm)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objForm" => $objForm));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objForm->getHyperMedia("edit-form")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objForm = $objApiRequest->performPUTRequest($objForm->getArrayCopy())->getBody();

		//recreate link entity
		$objForm = $this->createFormEntity($objForm->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objForm" => $objForm, "form_id" => $objForm->get("id")));

		return $objForm;
	}//end function

	/**
	 * Delete an existing form
	 * @triggers deleteForm.pre, deleteForm.post
	 * @param mixed $id
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormEntity
	 */
	public function deleteForm($id)
	{
		//create form entity
		$objForm = $this->getForm($id);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objForm" => $objForm));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objForm->getHyperMedia("delete-form")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objForm = $objApiRequest->performDELETERequest(array())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objForm" => $objForm, "form_id" => $id));

		return $objForm;
	}//end function

	/**
	 * Allocates a field to a form
	 * @triggers allocateFieldtoForm.pre, allocateFieldtoForm.post
	 * @param FrontFormAdminFormFieldEntity $objField
	 * @param FrontFormAdminFormEntity $objForm
	 * @param string $field_type
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity
	 */
	public function allocateFieldtoForm(FrontFormAdminFormFieldEntity $objField, FrontFormAdminFormEntity $objForm, $field_type)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/form/" . $objForm->get("id") . "/field/" . $objField->get("id") . "/$field_type");

		//execute
		$objResult = $objApiRequest->performPOSTRequest($objField->getArrayCopy());

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objField" => $objField, "form_id" => $objForm->get("id"), "objForm" => $objForm));

		return $objField;
	}//end function

	/**
	 * Fetch details about a specific form field
	 * @param mixed $form_id
	 * @param mixed $field_id
	 * @param string $field_type
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity
	 */
	public function getFormField($form_id, $field_id, $field_type)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/form/$form_id/field/$field_id/$field_type");

		//execute
		$objResult = $objApiRequest->performGETRequest()->getBody();

		//create form field entiry
		$objField = $this->createFormFieldEntity($objResult->data);

		return $objField;
	}//end function

	/**
	 * Update a field allocated to a form
	 * @triggers updateFormField.pre, updateFormField.post
	 * @param FrontFormAdminFormFieldEntity $objField
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormEntity
	 */
	public function updateFormField(FrontFormAdminFormFieldEntity $objField)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objField->getHyperMedia("edit-form-field")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objApiRequest->performPUTRequest($objField->getArrayCopy())->getBody();

		//reset the API Request object
		$this->model_api_request = FALSE;

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objField" => $objField, "form_id" => $objField->get("fk_form_id")));

		return $objField;
	}//end function

	public function updateFormFieldsOrder($form_id, $arr_data)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . '.pre', $this, array('form_id' => $form_id));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		$objApiRequest->setApiAction('forms/form/' . $form_id . '/field-order');
		//execute
		$objResult = $objApiRequest->performPUTRequest($arr_data)->getBody();
		return $objResult;
	}//end function

	/**
	 * Remove a field allocated to a form
	 * @triggers removeFormField.pre, removeFormField.post
	 * @param FrontFormAdminFormFieldEntity $objField
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormEntity
	 */
	public function removeFormField(FrontFormAdminFormFieldEntity $objField)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objField->getHyperMedia("remove-form-field")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$result = $objApiRequest->performDELETERequest(array())->getBody();

		//reset the API Request object
		$this->model_api_request = FALSE;

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objField" => $objField, "form_id" => $objField->get("fk_form_id")));

		return $objField;
	}//end function

	/**
	 * Fetch field groups already set on form
	 * @param mixed $form_id
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity
	 */
	public function fetchFormFieldGroups($form_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/additional-info/$form_id");

		//execute
		$objResult = $objApiRequest->performGETRequest(array("callback" => "fetchFormFieldGroups"))->getBody();

		return $objResult->data;
	}//end function

	/**
	 * Load the admin form for form fields from the Core
	 * @return \Zend\Form\Form
	 */
	public function getSalesFunnelDealNumberFieldForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
							->getSystemForm("Core\Forms\SystemForms\Fields\SalesFunnelDealNumberForm");
		return $objForm;
	}//end function

	/**
	 * Load salesx funnel deal number information
	 * @param mixed $form_id
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity
	 */
	public function fetchSalesFunnelDealNumberField($form_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/sales-funnel/$form_id/deal-number");

		//execute
		$objResult = $objApiRequest->performGETRequest()->getBody();

		$objField = $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity");
		$objField->set($objResult->data);
		return $objField;
	}//end function

	/**
	 * Load the admin form for form fields from the Core
	 * @return \Zend\Form\Form
	 */
	public function getSalesFunnelDealStatusFieldForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
							->getSystemForm("Core\Forms\SystemForms\Fields\SalesFunnelDealStatusForm");
		return $objForm;
	}//end function

	public function fetchSalesFunnelStatusField($form_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/sales-funnel/$form_id/deal-status");

		//execute
		$objResult = $objApiRequest->performGETRequest()->getBody();

		$objField = $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity");
		$objField->set($objResult->data);
		return $objField;
	}//end function

	/**
	 * Create a form admin entity
	 * @param object $objData
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormEntity
	 */
	private function createFormEntity($objData)
	{
		$entity_form = $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminFormEntity");

		//populate the data
		$entity_form->set($objData);

		return $entity_form;
	}//end function

	/**
	 * Create a form field admin entity
	 * @param object $objData
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity
	 */
	private function createFormFieldEntity($objData)
	{
		$entity_form_field = $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity");

		//populate the data
		$entity_form_field->set($objData);

		return $entity_form_field;
	}//end function
}//end class
