<?php
namespace FrontCommsAdmin\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCommsAdminCommAttachmentsModel extends AbstractCoreAdapter
{
	/**
	 * Load the Communication Attachement Form
	 * @return \Zend\Form\Form
	 */
	public function getCommAttachmentForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
					->getSystemForm("Core\Forms\SystemForms\CommsAdmin\CommAttachmentForm", NULL, array("filters" => 0, "validators" => 0));
		
		return $objForm;
	}//end function
	
	/**
	 * Load a specific communication attachment
	 * @param mixed $comm_id
	 * @param mixed $id
	 * @return stdClass
	 */
	public function fetchCommAttachment($comm_id, $id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comm-attachments/$id?comm_id=$comm_id");
		
		//execute
		$objResult = $objApiRequest->performGETRequest(array())->getBody();
		return $objResult->data;
	}//end function
	
	/**
	 * Load a collection of communication attachments
	 * @param mixed $comm_id
	 * @return stdClass
	 */
	public function fetchCommAttachments($comm_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comm-attachments?comm_id=$comm_id");
		
		//execute
		$objResult = $objApiRequest->performGETRequest(array())->getBody();
		return $objResult->data;
	}//end function
	
	/**
	 * Add an attachment to a communication
	 * @param mixed $comm_id
	 * @param array $arr_data
	 * @return stdClass
	 */
	public function createCommAttachment($comm_id, $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comm-attachments?comm_id=$comm_id");

		//execute
		$objResult = $objApiRequest->performPOSTRequest($arr_data)->getBody();
		return $objResult->data;
	}//end function
	
	/**
	 * Remove an attachment from a communication
	 * @param mixed $comm_id - Communication ID
	 * @param mixed $id - Attachment ID
	 * @return stdClass
	 */
	public function deleteCommAttachment($comm_id, $id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comm-attachments/$id?comm_id=$comm_id");
		
		$objResult = $objApiRequest->performDELETERequest(array())->getBody();
		return $objResult->data;
	}//end function
}//end class