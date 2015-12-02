<?php
namespace FrontContacts\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontContacts\Entities\FrontContactsFormsEntity;

class FrontContactsFormsModel extends AbstractCoreAdapter
{
	/**
	 * Fetch a collection of forms completed by the contact
	 * @param mixed $contact_id
	 * @param string $form_type - Optional. Specify a certain type of form ie web or viral
	 * @return \FrontContacts\Entities\FrontContactsFormsEntity
	 */
	public function fetchContactFormsCompleted($contact_id, $form_type = NULL)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/forms-completed");
		
		//execute
		$objContactForms = $objApiRequest->performGETRequest()->getBody();
	
		foreach ($objContactForms->data as $objForm)
		{
			if (!is_numeric($objForm->id))
			{
				continue;	
			}//end if
			
			$objFormCompletedEntity = $this->getServiceLocator()->get("FrontContacts\Entities\FrontContactsFormsEntity");
			$objFormCompletedEntity->set($objForm);

			$arr[] = $objFormCompletedEntity;
		}//end foreach
		
		return (object) $arr;
	}//end function
	
	/**
	 * Load a collection of Sales Funnel existing for the Contact
	 * @param mixed $contact_id
	 * @return \FrontContacts\Entities\FrontContactsFormsEntity
	 */
	public function fetchContactSalesFunnelsCompleted($contact_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/sales-funnels");
		
		//execute
		$objSalesFunnels = $objApiRequest->performGETRequest()->getBody();
		
		foreach ($objSalesFunnels->data as $objSalesFunnel)
		{
			if (!is_numeric($objSalesFunnel->id))
			{
				continue;
			}//end if
				
			$objFormCompletedEntity = $this->getServiceLocator()->get("FrontContacts\Entities\FrontContactsFormsEntity");
			$objFormCompletedEntity->set($objSalesFunnel);
		
			$arr[] = $objFormCompletedEntity;
		}//end foreach
		
		return (object) $arr;
	}//end function
}//end class