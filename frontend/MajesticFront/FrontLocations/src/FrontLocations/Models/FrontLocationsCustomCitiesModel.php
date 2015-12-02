<?php
namespace FrontLocations\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontLocations\Entities\FrontLocationsCustomCityEntity;

class FrontLocationsCustomCitiesModel extends AbstractCoreAdapter
{
	/**
	 * Load the admin form for custom cities from Core System Forms
	 * @return \Zend\Form\Form
	 */
	public function getCustomCitySystemForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
								->getSystemForm("Core\Forms\SystemForms\Locations\CustomCityForm");

		return $objForm;
	}//end function

	/**
	 * Request details about a specfic custom city
	 * @param mixed $id
	 * @return \FrontLocations\Entities\FrontLocationsCustomCityEntity
	 */
	public function fetchCustomCity($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("locations/admin/custom-cities/$id");

		//execute
		$objCity = $objApiRequest->performGETRequest(array("id" => $id))->getBody();

		//create custom city entity
		$objCity = $this->createCustomCityEntity($objCity->data);

		return $objCity;
	}//end function

	/**
	 * Load a list of custom cities available for profile
	 * @param array $arr_where - Optional
	 * @return object
	 */
	public function fetchCustomCities($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("locations/admin/custom-cities");

		//execute
		$objCities = $objApiRequest->performGETRequest($arr_where)->getBody();

		return $objCities->data;
	}//end function

	/**
	 * Create a custom city
	 * @trigger : createCustomCity.pre, createCustomCity.post
	 * @param array $arr_data
	 * @return \FrontLocations\Entities\FrontLocationsCustomCityEntity
	 */
	public function createCustomCity($arr_data)
	{
		//create link entity
		$objCity = $this->createCustomCityEntity($arr_data);

		//trigger pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objCity" => $objCity));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("locations/admin/custom-cities");

		//execute
		$objCity = $objApiRequest->performPOSTRequest($objCity->getArrayCopy())->getBody();

		//recreate link entity
		$objCity = $this->createCustomCityEntity($objCity);

		//trigger post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objCity" => $objCity));

		return $objCity;
	}//end function

	/**
	 * Update a custom city
	 * @trigger : updateCustomCity.pre, updateCustomCity.post
	 * @param FrontLocationsCustomCityEntity $objCity
	 * @return \FrontLocations\Entities\FrontLocationsCustomCityEntity
	 */
	public function updateCustomCity(FrontLocationsCustomCityEntity $objCity)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objCity" => $objCity));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objCity->getHyperMedia("edit-custom-city")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objCity = $objApiRequest->performPUTRequest($objCity->getArrayCopy())->getBody();

		//recreate link entity
		$objCity = $this->createCustomCityEntity($objCity->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objCity" => $objCity));

		return $objCity;
	}//end function

	/**
	 * Delete an existing custom city
	 * @param mixed $id
	 * @return \FrontLocations\Entities\FrontLocationsCustomCityEntity
	 */
	public function deleteCustomCity($id)
	{
		//load custom city entity
		$objCity = $this->fetchCustomCity($id);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objCity" => $objCity));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objCity->getHyperMedia("delete-custom-city")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objCity = $objApiRequest->performDELETERequest(array())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objCity" => $objCity));

		return $objCity;
	}//end function

	/**
	 * Create a custom city entity object
	 * @param object $objData
	 * @return \FrontLocations\Entities\FrontLocationsCustomCityEntity
	 */
	private function createCustomCityEntity($objData)
	{
		$entity_custom_city = $this->getServiceLocator()->get("FrontLocations\Entities\FrontLocationsCustomCityEntity");

		//populate the data
		$entity_custom_city->set($objData);

		return $entity_custom_city;
	}//end function
}//end class
