<?php
namespace FrontFormAdmin\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontFormAdmin\Entities\FrontFormAdminReplaceFieldEntity;

class FrontReplaceFieldsAdminModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Cache object
	 * @var \FrontCore\Caches\FrontCachesRedis
	 */
	private $objCache;
	private $cache_key = "front-replace-fields-list";
	
	public function getReplaceFieldSystemForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
							->getSystemForm("Core\Forms\SystemForms\Fields\ReplaceFieldsForm");
		return $objForm;
	}//end functin

	/**
	 * Fetch a specific replace field
	 * @param mixed $id
	 * @return \FrontFormAdmin\Entities\FrontFormAdminReplaceFieldEntity
	 */
	public function fetchReplaceField($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/fields/replace/$id");

		//execute
		$objField = $objApiRequest->performGETRequest()->getBody();

		//convert data into field entity
		$objField = $this->createReplaceFieldEntity($objField->data);

		return $objField;
	}//end function

	/**
	 * Fetch a collection of replace fields
	 * @param array $arr_where - Optional
	 * @return StdClass
	 */
	public function fetchReplaceFields($arr_where = array(), $use_cache = FALSE)
	{
		//check if data is cached
		$this->objCache = $this->getServiceLocator()->get("FrontCore\Caches\Cache");
		
		if ($use_cache == TRUE && $this->objCache->readCacheItem($this->cache_key) != FALSE)
		{
			return $this->objCache->readCacheItem($this->cache_key);
		}//end if
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/fields/replace");

		//execute
		$objFields = $objApiRequest->performGETRequest($arr_where)->getBody();

		//save data to cache
		$this->objCache->setCacheItem($this->cache_key, $objFields->data, array("ttl" => 300));
		
		return $objFields->data;
	}//end function

	/**
	 * Create a new replace field
	 * @trigger : createReplaceField.pre, createReplaceField.post
	 * @param array $arr_data
	 * @return \FrontFormAdmin\Entities\FrontFormAdminReplaceFieldEntity
	 */
	public function createReplaceField($arr_data)
	{
		//convert data to entity
		$objField = $this->createReplaceFieldEntity($arr_data);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms/fields/replace");

		//execute
		$objField = $objApiRequest->performPOSTRequest($objField->getArrayCopy())->getBody();

		//recreate replace field entity
		$objField = $this->createReplaceFieldEntity($objField->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		return $objField;
	}//end function

	/**
	 * Update a replace field
	 * @trigger : updateReplaceField.pre, updateReplaceField.post
	 * @param FrontFormAdminReplaceFieldEntity $objField
	 * @return \FrontFormAdmin\Entities\FrontFormAdminReplaceFieldEntity
	 */
	public function updateReplaceField(FrontFormAdminReplaceFieldEntity $objField)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objField->getHyperMedia("edit-replace-field")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objField = $objApiRequest->performPUTRequest($objField->getArrayCopy())->getBody();

		//recreate replace field entity
		$objField = $this->createReplaceFieldEntity($objField->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		return $objField;
	}//end function

	/**
	 * Delete a replace field
	 * @trigger : deleteReplaceField.pre, deleteReplaceField.post
	 * @param FrontFormAdminReplaceFieldEntity $objField
	 * @return \FrontFormAdmin\Entities\FrontFormAdminReplaceFieldEntity
	 */
	public function deleteReplaceField(FrontFormAdminReplaceFieldEntity $objField)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objField->getHyperMedia("delete-replace-field")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objField = $objApiRequest->performDELETERequest(array())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objField" => $objField));

		//recreate field entity
		$objField = $this->createReplaceFieldEntity($objField->data);

		return $objField->data;
	}//end function

	/**
	 * Create a Field entity object
	 * @param mixed $objData
	 * @return \FrontFormAdmin\Entities\FrontFormAdminReplaceFieldEntity
	 */
	private function createReplaceFieldEntity($objData)
	{
		$entity_field = $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminReplaceFieldEntity");

		//populate the data
		$entity_field->set($objData);

		return $entity_field;
	}//end function
}//end class