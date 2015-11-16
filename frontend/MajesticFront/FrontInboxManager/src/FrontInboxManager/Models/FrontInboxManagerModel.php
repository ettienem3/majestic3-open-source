<?php
namespace FrontInboxManager\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontInboxManagerModel extends AbstractCoreAdapter
{
	public function fetchInboxMessages($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify action
		$objApiRequest->setApiAction("inbox/manager");
		
		//execute
		$objResult = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		//extract data and create entities
		if (is_object($objResult->data))
		{
			foreach ($objResult->data as $objMessage)
			{
				$objMessageEntity = $this->getServiceLocator()->get("FrontInboxManager\Entities\FrontInboxManagerMessageEntity");
				$objMessageEntity->set($objMessage);
				
				$arr[] = $objMessageEntity;
			}//end function
		} else {
			$arr = array();
		}//end if
		
		return (object) $arr;
	}//end function
	
	public function fetchInboxMessage($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify action
		$objApiRequest->setApiAction("inbox/manager/$id");
		
		//execute
		$objResult = $objApiRequest->performGETRequest()->getBody();
		
		$objMessageEntity = $this->getServiceLocator()->get("FrontInboxManager\Entities\FrontInboxManagerMessageEntity");
		$objMessageEntity->set($objResult->data);
		
		//update the message new status
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify action
		$objApiRequest->setApiAction("inbox/manager/$id");
		
		//execute
		$objResult = $objApiRequest->performPUTRequest(array("new" => 0))->getBody();
		
		return $objMessageEntity;
	}//end function
	
	public function updateInboxMessage($id, $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify action
		$objApiRequest->setApiAction("inbox/manager/$id");
		
		$objResult = $objApiRequest->performPUTRequest($arr_data)->getBody();
	}//end function
	
	public function deleteInboxMessage($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify action
		$objApiRequest->setApiAction("inbox/manager/$id");
		
		$objResult = $objApiRequest->performDELETERequest(array())->getBody();
	}//end function
}//end class
