<?php
namespace FrontFormAdmin\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontFormAdmin\Entities\FrontFormAdminFieldEntity;

class FrontGenericFieldsAdminModel extends AbstractCoreAdapter
{
	public function getGenericFieldSystemForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
							->getSystemForm("Core\Forms\SystemForms\Fields\GenericFieldsForm");
		return $objForm;
	}//end functin

	/**
	 * Fetch a specific generic field
	 * @param mixed $id
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function fetchGenericField($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/fields/generic/$id");

		//execute
		$objField = $objApiRequest->performGETRequest()->getBody();

		//convert data into field entity
		$objField = $this->createGenericFieldEntity($objField->data);

		return $objField;
	}//end function

	/**
	 * Fetch a collection of generic fields
	 * @param array $arr_where - Optional
	 * @return StdClass
	 */
	public function fetchGenericFields($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/fields/generic");

		//execute
		$objFields = $objApiRequest->performGETRequest($arr_where)->getBody();

		return $objFields->data;
	}//end function

	/**
	 * Create a new geneeric field
	 * @trigger : createGenericField.pre, createGenericField.post
	 * @param arrat $arr_data
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function createGenericField($arr_data)
	{
		//convert data to entity
		$objField = $this->createGenericFieldEntity($arr_data);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/fields/generic");

		//execute
		$objField = $objApiRequest->performPOSTRequest($objField->getArrayCopy())->getBody();

		//recreate generic field entity
		$objField = $this->createGenericFieldEntity($objField->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		return $objField;
	}//end function

	/**
	 * Update a generic field
	 * @trigger : updateGenericField.pre, updateGenericField.post
	 * @param FrontFormAdminFieldEntity $objField
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function updateGenericField(FrontFormAdminFieldEntity $objField)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objField->getHyperMedia("edit-generic-field")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objField = $objApiRequest->performPUTRequest($objField->getArrayCopy())->getBody();

		//recreate generic field entity
		$objField = $this->createGenericFieldEntity($objField->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		return $objField;
	}//end function

	/**
	 * Delete a generic field
	 * @trigger : deleteGenericField.pre, deleteGenericField.post
	 * @param FrontFormAdminFieldEntity $objField
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function deleteGenericField(FrontFormAdminFieldEntity $objField)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objField->getHyperMedia("delete-generic-field")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objField = $objApiRequest->performDELETERequest(array())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		//recreate field entity
		$objField = $this->createGenericFieldEntity($objField->data);

		return $objField->data;
	}//end function

	/**
	 * Create a Field entity object
	 * @param mixed $objData
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	private function createGenericFieldEntity($objData)
	{
		$entity_field = $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminFieldEntity");

		//populate the data
		$entity_field->set($objData);

		return $entity_field;
	}//end function
}//end class