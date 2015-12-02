<?php
namespace FrontContacts\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontContactCommsTemplatesModel extends AbstractCoreAdapter
{
	/**
	 * Load the applicable Comm Template form for sending
	 * @param string $form_type
	 * @return \Zend\Form\Form
	 */
	public function loadCommTemplateForm($comm_type)
	{
		switch(strtolower($comm_type))
		{
			case "__email":
				$namespace = "Core\Forms\SystemForms\Comms\CommTemplateSendEmailForm";
				break;
				
			case "__sms":
				$namespace = "Core\Forms\SystemForms\Comms\CommTemplateSendSmsForm";
				break;
				
			case "__fax":
				$namespace = "Core\Forms\SystemForms\Comms\CommTemplateSendFaxForm";
				break;
				
			default:
				throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Comm Template Send form could not be loaded. Unknown Comm Template type has been received. ($comm_type)", 500);
				break;
		}//end switch
		
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm($namespace);
		
		return $objForm;
	}//end function
	
	/**
	 * Load a preview of a comm template for a contact
	 * @param mixed $contact_id
	 * @param mixed $comms_id
	 * @return \FrontContacts\Entities\FrontContactsCommTemplateEntity
	 */
	public function loadCommTemplate($contact_id, $comms_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/comm-templates");

		//execute
		$objCommTemplate = $objApiRequest->performGETRequest(array("operation" => "preview", "comms_id" => $comms_id))->getBody();
		
		//create entity
		$objCommTemplateEntity = $this->getServiceLocator()->get("FrontContacts\Entities\FrontContactsCommTemplateEntity");
		$objCommTemplateEntity->set($objCommTemplate->data);
		return $objCommTemplateEntity;
	}//end function
	
	/**
	 * Submit a comm template for preview/sending
	 * @param mixed $contact_id
	 * @param array $arr_data - Data from form to submit comm with
	 * @param sting $action - preview/send
	 */
	public function sendCommTemplate($contact_id, $arr_data, $action)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//send the request and wait for response
		$objApiRequest->setApiAction("contacts/data/$contact_id/comm-templates");
		
		//execute
		$objResult = $objApiRequest->performPUTRequest($arr_data->getArrayCopy())->getBody();
		
		return $objResult;
	}//end function
}//end class