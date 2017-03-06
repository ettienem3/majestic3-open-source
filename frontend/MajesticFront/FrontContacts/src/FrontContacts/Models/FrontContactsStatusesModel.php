<?php
namespace FrontContacts\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontContacts\Entities\FrontContactsContactStatusEntity;

class FrontContactsStatusesModel extends AbstractCoreAdapter
{
	/**
	 * Fecth the contact status history for the set contact
	 * @param mixed $contact_id
	 * @param array $arr_params - Optional
	 * @return \FrontContacts\Entities\FrontContactsContactStatusEntity
	 */
	public function fetchContactStatusHistory($contact_id, $arr_params = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/statuses");
		
		//execute
		$objContactStatusHistory = $objApiRequest->performGETRequest($arr_params)->getBody();
		$objHypermedia = $objContactStatusHistory->data->hypermedia;
		
		//create data entities
		foreach ($objContactStatusHistory->data as $objContactStatus)
		{
			if (!is_numeric($objContactStatus->id))
			{
				continue;	
			}//end if
			
			$objContactStatusEntity = $this->getServiceLocator()->get("FrontContacts\Entities\FrontContactsContactStatusEntity");
			$objContactStatusEntity->set($objContactStatus);
			$arr[] = $objContactStatusEntity;
		}//end foreach
		
		$objData = (object) $arr;
		$objData->hypermedia = $objHypermedia;
		return $objData;
	}//end function
	
	/**
	 * Update a contact status for the set contact
	 * @param mixed $contact_id
	 * @param array $arr_data
	 */
	public function updateContactStatus($contact_id, array $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/statuses");
		
		//add contact id to data
		$arr_data["reg_id"] = $contact_id;
		
		//check if behaviour is set
		if ($arr_data["behaviour"] == "")
		{
			$arr_data["behaviour"] = "__system";
		} else {
			if (substr($arr_data["behaviour"], 0, 2) != "__")
			{
				$arr_data["behaviour"] = "__" . strtolower($arr_data["behaviour"]);
			}//end if
		}//end if
	
		//execute
		$objResult = $objApiRequest->performPUTRequest($arr_data)->getBody();

		return $objResult;
	}//end function
}//end class