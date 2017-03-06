<?php
namespace FrontContacts\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontContacts\Entities\FrontContactsJourneyEntity;

class FrontContactsJourneysModel extends AbstractCoreAdapter
{
	/**
	 * Load a list of journeys started for the set contact
	 * @param mixed $contact_id
	 * @param array $arr_params - Optional
	 * @return \FrontContacts\Entities\FrontContactsJourneyEntity
	 */
	public function fetchContactJourneysStarted($contact_id, $arr_params = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/journeys");

		//execute
		$objContactJourneysStarted = $objApiRequest->performGETRequest($arr_params)->getBody();

		$objHypermedia = $objContactJourneysStarted->data->hypermedia;

		//create data entities
		$arr = array();
		foreach ($objContactJourneysStarted->data as $objJourney)
		{
			if (!is_object($objJourney) || !is_numeric($objJourney->id))
			{
				continue;
			}//end if

			$objContactJourneyStartedEntity = $this->getServiceLocator()->get("FrontContacts\Entities\FrontContactsJourneyEntity");
			$objContactJourneyStartedEntity->set($objJourney);
			$arr[] = $objContactJourneyStartedEntity;
		}//end foreach

		$objData = (object) $arr;
		$objData->hypermedia = $objHypermedia;
		return $objData;
	}//end function

	/**
	 * Start a journey for a contact
	 * @trigger: startContactJourney.pre, startContactJourney.post
	 * @param mixed $contact_id
	 * @param mixed $journey_id
	 * @return object
	 */
	public function startContactJourney($contact_id, $journey_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/journeys");

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("contact_id" => $contact_id, "journey_id" => $journey_id));

		//execute
		$objData = $objApiRequest->performPUTRequest(array("operation" => "start", "journey_id" => $journey_id))->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("contact_id" => $contact_id, "journey_id" => $journey_id, "objData" => $objData));

		return $objData;
	}//end function

	/**
	 * Stop a journey for a contact
	 * @trigger : stopContactJourney.pre, stopContactJourney.post
	 * @param mixed $contact_id
	 * @param mixed $reg_comm_id
	 */
	public function stopContactJourney($contact_id, $reg_comm_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/journeys");

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("contact_id" => $contact_id, "reg_comm_id" => $reg_comm_id));

		//execute
		$objData = $objApiRequest->performPUTRequest(array("operation" => "stop", "reg_comm_id" => $reg_comm_id))->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("contact_id" => $contact_id, "reg_comm_id" => $reg_comm_id, "objData" => $objData));

		return $objData;
	}//end function

	/**
	 * Restart a journey for a contact
	 * @trigger: restartContactJourney.pre, restartContactJourney.post
	 * @param mixed $contact_id
	 * @param mixed $reg_comm_id
	 */
	public function restartContactJourney($contact_id, $reg_comm_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/journeys");

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("contact_id" => $contact_id, "reg_comm_id" => $reg_comm_id));

		//execute
		$objData = $objApiRequest->performPUTRequest(array("operation" => "restart", "reg_comm_id" => $reg_comm_id))->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("contact_id" => $contact_id, "reg_comm_id" => $reg_comm_id, "objData" => $objData));

		return $objData;
	}//end function
}//end class