<?php
namespace FrontPowerTools\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontPowerToolsCommsAutomationModel extends AbstractCoreAdapter
{
	/**
	 * Trigger Communication Queueing Process
	 * @return stdClass
	 */
	public function queueComms()
	{
		throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : This module is not available currently", 500);
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("automation/comms/queue");
		
		$objResult = $objApiRequest->performGETRequest()->getBody();
		return $objResult->data;
	}//end function
	
	/**
	 * Trigger Communication Sending Process
	 * @return stdClass
	 */
	public function sendComms()
	{
		throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : This module is not available currently", 500);
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("automation/comms/send");
		
		$objResult = $objApiRequest->performGETRequest()->getBody();
		return $objResult->data;
	}//end function
	
	/**
	 * Trigger Communication Queueing and Sending Process
	 * @return stdClass
	 */
	public function queueSendComms()
	{
		throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : This module is not available currently", 500);
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("automation/comms/queue-and-send");
		
		$objResult = $objApiRequest->performGETRequest()->getBody();
		return $objResult->data;
	}//end function
	
	public function processProfileActivity()
	{
		throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : This module is not available currently", 500);
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
	
		//setup the object and specify the action
		$objApiRequest->setApiAction("automation/profile/administration/0?f=process_profile_activity");
	
		$objResult = $objApiRequest->performGETRequest()->getBody();
		return $objResult->data;
	}//end function
	
	public function processUserActivity()
	{
		throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : This module is not available currently", 500);
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("automation/profile/administration/0?f=process_user_activity");
		
		$objResult = $objApiRequest->performGETRequest()->getBody();
		return $objResult->data;
	}//end function
}//end class