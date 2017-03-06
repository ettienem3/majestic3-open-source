<?php
namespace FrontCommsAdmin\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontJourneysTestModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Front Contacts Model
	 * @var \FrontContacts\Models\FrontContactsModel
	 */
	private $model_front_contacts;
	
	/**
	 * Container for the Journeys Model
	 * @var \FrontCommsAdmin\Models\FrontJourneysModel
	 */
	private $model_front_journeys;
	
	/**
	 * Load available journeys
	 * @return stdClass
	 */
	public function fetchJourneys($arr_params = array())
	{
		$objJourneys = $this->getFrontJourneysModel()->fetchJourneys($arr_params, false);
		return $objJourneys;
	}//end function
	
	public function fetchJourney($id)
	{
		$objJourneys = $this->getFrontJourneysModel()->fetchJourney($id);
		return $objJourneys;
	}//end function
	
	/**
	 * Validate a contact where a test record is being created
	 * @param int $id
	 */
	public function validateContact($id)
	{
		$objContact = $this->getFrontContactsModel()->fetchContact($id);
		if (!is_object($objContact))
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : The set contact is not valid", 500);
		}//end if
		
		return $objContact;
	}//end function
	
	/**
	 * Load current test journeys
	 * @return stdClass
	 */
	public function fetchJourneyTests($arr_params = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/test-journeys");
		
		//execute
		$objTests = $objApiRequest->performGETRequest($arr_params)->getBody();
		return $objTests->data;
	}//end function
	
	/**
	 * Create a test
	 */
	public function createJourneyTest($arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/test-journeys");
		
		//execute
		$objTest = $objApiRequest->performPOSTRequest($arr_data)->getBody();
		return $objTest->data;
	}//end function
	
	/**
	 * Remove a specific test record
	 */
	public function deleteJourneyTest($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/test-journeys/$id");
		
		//execute
		$objTest = $objApiRequest->performDELETERequest()->getBody();
		return $objTest->data;
	}//end function
	
	/**
	 * Remove all test records on a specific journey
	 */
	public function deleteJourneyTestJourney($journey_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/test-journeys/0");
		
		//execute
		$objTest = $objApiRequest->performDELETERequest(array('journey_id' => $journey_id))->getBody();
		return $objTest->data;
	}//end function
	
	/**
	 * Remove all test records on a specific contact
	 */
	public function deleteJourneyTestContact($contact_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/test-journeys/0");
		
		//execute
		$objTest = $objApiRequest->performDELETERequest(array('contact_id' => $contact_id))->getBody();
		return $objTest->data;
	}//end function
	
	/**
	 * Create an instance of the Contacts Model
	 * @return \FrontContacts\Models\FrontContactsModel
	 */
	private function getFrontContactsModel()
	{
		if (!$this->model_front_contacts)
		{
			$this->model_front_contacts = $this->getServiceLocator()->get('FrontContacts\Models\FrontContactsModel');
		}//end function
		
		return $this->model_front_contacts;
	}//end function
	
	/**
	 * Create an instance of the Journeys Model
	 * @return \FrontCommsAdmin\Models\FrontJourneysModel
	 */
	private function getFrontJourneysModel()
	{
		if (!$this->model_front_journeys)
		{
			$this->model_front_journeys = $this->getServiceLocator()->get('FrontCommsAdmin\Models\FrontJourneysModel');
		}//end if
		
		return $this->model_front_journeys;
	}//end function
}//end class