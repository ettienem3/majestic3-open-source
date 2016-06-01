<?php
namespace FrontLocations\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontLocations\Entities\FrontLocationsEntity;

class FrontLocationsModel extends AbstractCoreAdapter
{
	/**
	 * Fetch a specific country
	 * @param mixed $id
	 * @return \FrontLocations\Entities\FrontLocationsEntity
	 */
	public function fetchCountry($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("locations/countries/$id");

		//execute
		$objCountry = $objApiRequest->performGETRequest()->getBody();

		//create country entity
		$objCountry = $this->createLocationEntity($objCountry->data);

		return $objCountry;
	}//end function

	/**
	 * Fetch a collection of countries
	 * @param array $arr_where - Optional
	 * @return StdClass
	 */
	public function fetchCountries($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("locations/countries");

		//execute
		$objCountries = $objApiRequest->performGETRequest($arr_where)->getBody();

		return $objCountries->data;
	}//end function

	/**
	 * Fetch a specifc province
	 * @param mixed $id
	 * @return \FrontLocations\Entities\FrontLocationsEntity
	 */
	public function fetchProvince($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("locations/provinces/$id");

		//execute
		$objProvince = $objApiRequest->performGETRequest()->getBody();

		//create province entity
		$objProvince = $this->createLocationEntity($objProvince->data);

		return $objProvince;
	}//end function

	/**
	 * Fetch a collection of provinces
	 * @param array $arr_where - Optional
	 * @return StdClass
	 */
	public function fetchProvinces($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("locations/provinces");

		//execute
		$objProvince = $objApiRequest->performGETRequest($arr_where)->getBody();

		return $objProvince->data;
	}//end function

	/**
	 * Fetch a specific city
	 * @param mixed $id
	 * @return \FrontLocations\Entities\FrontLocationsEntity
	 */
	public function fetchCity($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("locations/cities/$id");

		//execute
		$objCity = $objApiRequest->performGETRequest()->getBody();

		//create city entity
		$objCity = $this->createLocationEntity($objCity->data);

		return $objCity;
	}//end function

	/**
	 * Fetch a collection of cities
	 * @param array $arr_where - Optional
	 * @return StdClass
	 */
	public function fetchCities($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("locations/cities");

		//execute
		$objCities = $objApiRequest->performGETRequest($arr_where)->getBody();

		return $objCities->data;
	}//end function

	/**
	 * Create an entity object
	 * @param mixed $id
	 * @param mixed $objData
	 * @return \FrontLocations\Entities\FrontLocationsEntity
	 */
	private function createLocationEntity($id, $objData)
	{
		$entity_location = $this->getServiceLocator()->get("FrontLocations\Entities\FrontLocationsEntity");

		$entity_location->set($objData);

		if (is_string($id) || is_numeric($id))
		{
			$entity_location->set("id", $id);
		}//end if

		return $entity_location;
	}//end function
}//end class
