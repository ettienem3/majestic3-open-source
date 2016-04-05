<?php
namespace FrontFormAdmin\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontFormAdmin\Entities\FrontFormAdminFieldEntity;
use FrontFormAdmin\Forms\FrontFormAdminFieldDeleteForm;

class FrontFieldAdminModel extends AbstractCoreAdapter
{
	/**
	 * Load the Field admin system form
	 * @return \Zend\Form\Form
	 */
	public function getFieldAdminForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm("Core\Forms\SystemForms\Fields\FieldsForm");
		return $objForm;
	}//end function

	/**
	 * Request a collection of custom fields from the API
	 * @param array $arr_where - Optional
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function getCustomFields($arr_where = array())
	{
		//@TODO delete this function, redundant
		return $this->fetchCustomFields($arr_where);
	}//end function

	/**
	 * Request a collection of custom fields from the API
	 * @param array $arr_where - Optional
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function fetchCustomFields($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/fields/custom");

		//execute
		$objFields = $objApiRequest->performGETRequest($arr_where)->getBody();

		//create field entities
		foreach ($objFields->data as $objField)
		{
			$arr[] = $this->createFieldEntity($objField);
		}//end foreach

		if (isset($objFields->data->hypermedia))
		{
			$arr['hypermedia'] = $objFields->data->hypermedia;
		}//end if
		
		$objData = (object) $arr;
		return $objData;
	}//end function

	/**
	 * Request details on a specific custom field from the API
	 * @param mixed $id
	 * @param bool $include_field_data - Optional. Set to 1 to load standard field values along with the field
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function getCustomField($id, $include_field_values = 0)
	{
		//@TODO delete this funtion, redundant
		return $this->fetchCustomField($id, $include_field_values);
	}//end function

	/**
	 * Request details on a specific custom field from the API
	 * @param mixed $id
	 * @param bool $include_field_data - Optional. Set to 1 to load standard field values along with the field
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function fetchCustomField($id, $include_field_values = 0)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/fields/custom/$id?include_field_values=$include_field_values");

		//execute
		$objField = $objApiRequest->performGETRequest(array("id" => $id, "include_field_values" => $include_field_values))->getBody();

		//create link entity
		$entity_field = $this->createFieldEntity($objField->data);

		return $entity_field;
	}//end function

	/**
	 * Request a collection of available standard fields from the API
	 * @param string $arr_where
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function getStandardFields($arr_where = NULL)
	{
		//@TODO delete this function, redundant
		return $this->fetchStandardFields($arr_where);
	}//end function

	/**
	 * Request a collection of available standard fields from the API
	 * @param string $arr_where
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function fetchStandardFields($arr_where = NULL)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/fields/standard");

		//execute
		$objFields = $objApiRequest->performGETRequest($arr_where)->getBody();

		//create field entities
		foreach ($objFields->data as $objField)
		{
			$arr[] = $this->createFieldEntity($objField);
		}//end foreach

		return (object) $arr;
	}//end function

	/**
	 * Request details about a specific standard fields from the API
	 * @param mixed $id
	 * @param bool $include_field_data - Optional. Set to 1 to load standard field values along with the field
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function getStandardField($id, $include_field_values = 0)
	{
		//@TODO delete this function, redundant
		return $this->fetchStandardField($id, $include_field_values = 0);
	}//end function

	/**
	 * Request details about a specific standard fields from the API
	 * @param mixed $id
	 * @param bool $include_field_data - Optional. Set to 1 to load standard field values along with the field
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function fetchStandardField($id, $include_field_values = 0)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/fields/standard/$id?include_field_values=" . (int) $include_field_values);

		//execute
		$objField = $objApiRequest->performGETRequest(array("id" => $id, "include_field_values" => (int) $include_field_values))->getBody();

		//create link entity
		$entity_field = $this->createFieldEntity($objField->data);

		return $entity_field;
	}//end function

	/**
	 * Create a new custom field
	 * @triggers createCustomField.pre, createCustomField.post
	 * @param FrontFormAdminFieldEntity $objField
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function createCustomField(FrontFormAdminFieldEntity $objField)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/fields/custom");

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField, "objApiRequest" => $objApiRequest));

		//execute
		$objResult = $objApiRequest->performPOSTRequest($objField->getArrayCopy())->getBody();

		//create link entity
		$entity_field = $this->createFieldEntity($objResult->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objField" => $entity_field, "objResult" => $objResult));

		return $entity_field;
	}//end function

	/**
	 * Update an existing custom field
	 * @triggers updateCustomField.pre, updateCustomField.post
	 * @param FrontFormAdminFieldEntity $objField
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function updateCustomField(FrontFormAdminFieldEntity $objField)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objField->getHyperMedia("edit-field")->url);
		$objApiRequest->setApiModule(NULL);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField, "objApiRequest" => $objApiRequest));

		//execute
		$objResult = $objApiRequest->performPUTRequest($objField->getArrayCopy())->getBody();

		//create link entity
		$entity_field = $this->createFieldEntity($objResult->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objField" => $entity_field, "objResult" => $objResult));

		return $entity_field;
	}//end function

	/**
	 * Permanently remove a custom fields from a Profile/Site via the API.
	 * Any data stored in this field will be lost.
	 * @param mixed $id
	 */
	public function deleteCustomField($id)
	{
		//load the field details
		$objField = $this->getCustomField($id);

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objField->getHyperMedia("delete-field")->url);
		$objApiRequest->setApiModule(NULL);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField, "objApiRequest" => $objApiRequest));

		//execute
		$objResult = $objApiRequest->performDELETERequest(NULL)->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objField" => $objField, "objResult" => $objResult));

		return $objField;
	}//end function


	public function detectCustomFieldAllocation(FrontFormAdminFieldEntity $objField)
	{

	}//end function

	/**
	 * Create a field entity
	 * @param object $objData
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	private function createFieldEntity($objData)
	{
		$entity_field = $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminFieldEntity");

		//populate the data
		$entity_field->set($objData);

		return $entity_field;
	}//end function
}//end class
