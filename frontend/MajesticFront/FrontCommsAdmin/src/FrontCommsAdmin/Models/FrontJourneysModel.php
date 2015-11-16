<?php
namespace  FrontCommsAdmin\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontCommsAdmin\Entities\FrontJourneysEntity;


class FrontJourneysModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Front Comms Admin Model
	 * @var \FrontCommsAdmin\Models\FrontCommsAdminModel
	 */
	private $model_front_comms_admin;
	
	/**
	 * Container for the journeys cache model
	 * @var \FrontCommsAdmin\Caches\FrontCommsJourneyCache
	 */
	private $cache_journeys;
	
	/**
	 * Load the Admin for the Journey from the Core System forms
	 * @return \Zend\Form\Form
	 */
	public function getJourneysForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm("Core\Forms\SystemForms\Comms\JourneyForm");
	
		return $objForm;
	}//end function
	
	/**
	 * Load a list of Journeys
	 * @param array $arr_where - Optional
	 * @param boolean $use_cache - Optional. Indicate to use cache to load data where required
	 * @return Object
	 */
	public function fetchJourneys($arr_where = array(), $use_cache = FALSE)
	{
		//check if data is cached
		if ($use_cache === TRUE)
		{
			$objData = $this->getJourneysCacheManager()->readCacheItem("journeys");
			if ($objData !== FALSE)
			{
				return $objData;	
			}//end if
		}//end if
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/journeys");
		
		//execute
		$objJourneys = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		//cache data
		if ($use_cache === TRUE)
		{
			$this->getJourneysCacheManager()->setCacheItem("journeys", $objJourneys->data);
		}//end if
		
		return $objJourneys->data;
	} //end function
	
	/**
	 * Load a list of comm templates
	 * @param array $arr_where - Optional
	 * @return Object
	 */
	public function fetchTemplates($arr_where = NULL)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/templates");
		
		//@TODO apply the search vars
		if (is_array($arr_where))
		{
			//...
		}//end if
		
		//execute
		$objJourneys = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		return $objJourneys->data;
	}//end function
	
	/**
	 * Request details about a specific Journey
	 * @param mixed $id
	 * @return \FrontCommsAdmin\Entities\FrontJourneysEntity
	 */
	public function fetchJourney($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/journeys/$id");
		
		//execute
		$objJourney = $objApiRequest->performGETRequest(array("id" => $id))->getBody();
		
		//create the journey entity
		$entity_journey = $this->createJourneyEntity($objJourney->data);
		
		return $entity_journey;
	}//end function
	
	/**
	 * Create a Journey entry
	 * @trigger createJourney.pre, createJourney.post
	 * @param array $arr_data
	 * @return \FrontCommsAdmin\Entities\FrontJourneysEntity
	 */
	public function createJourney($arr_data)
	{
		//create the journey entity
		$objJourney = $this->createJourneyEntity($arr_data);
		
		//trigger the pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objJourney" => $objJourney));
		
		//create the request object 
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/journeys");
		
		//execute
		$objJourney = $objApiRequest->performPOSTRequest($objJourney->getArrayCopy())->getBody();
		
		//recreate the journey entity
		$objJourney = $this->createJourneyEntity($objJourney);
		
		//trigger the post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objJouney" => $objJourney));
		
		//clear cache
		$this->getJourneysCacheManager()->clearCacheItem("journeys");
		
		return $objJourney;
	}//end function
	
	/**
	 * Update a Journey
	 * @trigger updateJourney.pre, updateJourney.post
	 * @param JourneyEntity $objJourney
	 * @return \FrontCommsAdmin\Entities\FrontJourneysEntity
	 */
	public function updateJourney(FrontJourneysEntity $objJourney)
	{
		//trigger the pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objJourney" => $objJourney));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action 
		$objApiRequest->setApiAction($objJourney->getHyperMedia("edit-journey")->url);
		$objApiRequest->setApiModule(NULL);
		
		//execute
		$objJourney = $objApiRequest->performPUTRequest($objJourney->getArrayCopy())->getBody();
		
		//recreate the journey entity
		$objJourney = $this->createJourneyEntity($objJourney->data);
		
		//trigger the post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objJourney" => $objJourney));
		
		//clear cache
		$this->getJourneysCacheManager()->clearCacheItem("journeys");
		
		return $objJourney;
	}//end function
	
	/**
	 * Delete a Journey
	 * @param mixed $id
	 * @return \FrontCommsAdmin\Entities\FrontJourneysEntity
	 */
	public function deleteJourney($id)
	{
		//load the journey
		$objJourney = $this->fetchJourney($id);
		
		//trigger the pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objJourney" => $objJourney));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction($objJourney->getHyperMedia("delete-journey")->url);
		$objApiRequest->setApiModule(NULL);
		
		//execute
		$objJourney = $objApiRequest->performDELETERequest(array())->getBody();
		
		//trigger the post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objJourney" => $objJourney));
		
		//clear cache
		$this->getJourneysCacheManager()->clearCacheItem("journeys");
		
		return $objJourney;
	}//end function
	
	public function createJourneyFlowDiagram($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/journeys/$id");
		
		//execute
		$objJourney = $objApiRequest->performGETRequest(array("id" => $id, "flow_diagram" => 1))->getBody();
		return $objJourney->data;
	}//end function
	
	/**
	 * Create the Journey entity object.
	 * @param object $objData
	 * @return \FrontCommsAdmin\Entities\FrontJourneysEntity
	 */
	private function createJourneyEntity($objData)
	{
		$entity_journey = $this->getServiceLocator()->get("FrontCommsAdmin\Entities\FrontJourneysEntity");
		
		//populate the data
		$entity_journey->set($objData);
		
		return $entity_journey;
	}//end function
	
	/**
	 * Create an instance of the Front Comms Admin Model using the Service Manager
	 * @return \FrontCommsAdmin\Models\FrontCommsAdminModel
	 */
	private function getFrontCommsAdminModel()
	{
		if (!$this->model_front_comms_admin)
		{
			$this->model_front_comms_admin = $this->getServiceLocator()->get("FrontCommsAdmin\Models\FrontCommsAdminModel");
		}//end if
		
		return $this->model_front_comms_admin;
	}//end function
	
	/**
	 * Create an instance of the Journeys Cache Manager using the Service Manager
	 * @return \FrontCommsAdmin\Caches\FrontCommsJourneyCache
	 */
	private function getJourneysCacheManager()
	{
		if (!$this->cache_journeys)
		{
			$this->cache_journeys = $this->getServiceLocator()->get("FrontCommsAdmin\Caches\FrontCommsJourneyCache");
		}//end if
		
		return $this->cache_journeys;
	}//end function
}//end class