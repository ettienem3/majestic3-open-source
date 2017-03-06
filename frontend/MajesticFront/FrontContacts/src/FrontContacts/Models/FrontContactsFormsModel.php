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
	 * @param array $arr_params - Optional.
	 * @return \FrontContacts\Entities\FrontContactsFormsEntity
	 */
	public function fetchContactFormsCompleted($contact_id, $form_type = NULL, $arr_params = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/forms-completed");
		
		//execute
		$objContactForms = $objApiRequest->performGETRequest($arr_params)->getBody();
	
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
		
		$objData = (object) $arr;
		$objData->hypermedia = $objContactForms->data->hypermedia;
		return $objData;
	}//end function
	
	/**
	 * Load a collection of Sales Funnel existing for the Contact
	 * @param mixed $contact_id
	 * @param array $arr_params - Optional
	 * @return \FrontContacts\Entities\FrontContactsFormsEntity
	 */
	public function fetchContactSalesFunnelsCompleted($contact_id, $arr_params = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/sales-funnels");
		
		//execute
		$objSalesFunnels = $objApiRequest->performGETRequest($arr_params)->getBody();
		$objHypermedia = $objSalesFunnels->data->hypermedia;
		
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
		
		$objData = (object) $arr;
		$objData->hypermedia = $objHypermedia;
		return $objData;
	}//end function
}//end class