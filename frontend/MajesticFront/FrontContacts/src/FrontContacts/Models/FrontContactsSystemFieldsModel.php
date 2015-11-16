<?php
namespace FrontContacts\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontContactsSystemFieldsModel extends AbstractCoreAdapter
{
	/**
	 * Request a list of distinct Sources available for the profile
	 * @return stdClass
	 */
	public function fetchDistinctContactSources()
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/system-fields/sources");
		
		//excute the request
		$objContactSources = $objApiRequest->performGETRequest(array())->getBody();

		return $objContactSources->data;
	}//end function
	
	/**
	 * Request a list of distinct References available for the profile
	 */
	public function fetchDistinctContactReferences()
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/system-fields/references");
		
		//excute the request
		$objContactReferences = $objApiRequest->performGETRequest(array())->getBody();
		
		return $objContactReferences->data;
	}//end fucntino
}//end class