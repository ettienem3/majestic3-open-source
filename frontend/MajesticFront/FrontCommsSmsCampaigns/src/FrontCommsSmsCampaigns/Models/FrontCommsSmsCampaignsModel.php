<?php
namespace FrontCommsSmsCampaigns\Models;

use FrontCore\Adapters\AbstractCoreAdapter;	
use FrontCommsSmsCampaigns\Entities\CommsSmsCampaignEntity; 

class FrontCommsSmsCampaignsModel extends AbstractCoreAdapter
{
	/**
	 * Loads the admin CommsSmsCampaign object from Core System Form
	 * @return \Zend\Form\Form
	 */
	public function getSmsCampaignSystemForm()
	{
		/**
		 * Get a list of available system forms
		 */
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")->getSystemForm("Core\Forms\SystemForms\Comms\SmsCampaignForm");
		return $objForm;
	}//end function
	
	/**
	 * Request details about a specfic Sms Campaign
	 * @param mixed $id - Mandatory
	 * @return \FrontCommsSmsCampaigns\Entities\CommsSmsCampaignEntity
	 */
	public function fetchSmsCampaign($id)
	{
		// creates the request object
		$objApiRequest = $this->getApiRequestModel();
	
		// setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/sms-campaigns/$id");
	
		// execute
		$objSmsCampaign = $objApiRequest->performGETRequest(array("id" => $id))->getBody();
	
		// create comms_sms_campaign entity
		$objSmsCampaign =  $this->createCommsSmsCampaignEntity($objSmsCampaign->data);
	
		return $objSmsCampaign;
	} // end function
	
	/**
	 * Load a list of Sms Campaigns available for profile
	 * @param array $arr_where - Optional
	 * @return StdClass
	 */
	public function fetchSmsCampaigns($arr_where = array())
	{
		// creates the request object
		$objApiRequest = $this->getApiRequestModel();
		
		// setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/sms-campaigns");
				
		// execute
		$objSmsCampaigns = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		return $objSmsCampaigns->data;
	} // end function
	
	/**
	 * Create an Sms Campaign
	 * @trigger : createCommsSmsCampaign.pre, createCommsSmsCampaign.post
	 * @param array $arr_data
	 * @return \FrontCommsSmsCampaigns\Entities\CommsSmsCampaignEntity
	 */
	public function createCommsSmsCampaign($arr_data)
	{
		// create object entity
		$objSmsCampaign = $this->createCommsSmsCampaignEntity($arr_data);
		
		// trigger .pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSmsCampaign" => $objSmsCampaign));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		// create the request object
		$objApiRequest->setApiAction("comms/admin/sms-campaigns");
		
		// execute 
		$objSmsCampaign = $objApiRequest->performPOSTRequest($objSmsCampaign->getArrayCopy())->getBody();
		
		// recreate CommsSmsCampaign entity.
		$objSmsCampaign = $this->createCommsSmsCampaignEntity($objSmsCampaign);
		
		// trigger .post event 
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objSmsCampaign" => $objSmsCampaign));
		return $objSmsCampaign;
	}//end function
	
	/**
	 * Update an Sms Campaign
	 * @trigger : updateCommsSmsCampaign.pre, updateCommsSmsCampaign.post 
	 * @param CommsSmsCampaignEntity $objSmsCampaign
	 * @return \FrontCommsSmsCampaigns\Entities\CommsSmsCampaignEntity
	 */
	public function updateCommsSmsCampaign(CommsSmsCampaignEntity $objSmsCampaign)
	{
		// trigger .pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSmsCampaign" => $objSmsCampaign));
		
		// create request object from API
		$objApiRequest = $this->getApiRequestModel();
		
		// set the object specific action
		$objApiRequest->setApiAction($objSmsCampaign->getHyperMedia("edit-sms-campaign")->url);
		$objApiRequest->setApiModule(NULL);
		
		// execute
		$objSmsCampaign = $objApiRequest->performPUTRequest($objSmsCampaign->getArrayCopy())->getBody();
		
		// recreate comms_sms_campaign entity
		$objSmsCampaign = $this->createCommsSmsCampaignEntity($objSmsCampaign->data);
		
		// trigger .post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objSmsCampaign" => $objSmsCampaign));
		
		return $objSmsCampaign;
	} // end function
	
	/**
	 * Delete an existing Sms Campaign
	 * @trigger : deleteCommsSmsCampaign.pre, deleteCommsSmsCampaign.post
	 * @param mixed $id - Mandatory
	 */
	public function deleteCommsSmsCampaign($id)
	{
		// create CommsSmsCampaign entity
		$objSmsCampaign = $this->fetchSmsCampaign($id);
		
		// trigger .pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSmsCampaign" => $objSmsCampaign));
		
		// create the APIRequestModel
		$objApiRequest = $this->getApiRequestModel();
		
		// setup the object and specify the action
		$objApiRequest->setApiAction($objSmsCampaign->getHyperMedia("delete-sms-campaign")->url);
		$objApiRequest->setApiModule(NULL);
		
		// execute
		$objSmsCampaign = $objApiRequest->performDELETERequest(array())->getBody();
		
		// trigger .post event
		$this->getEventManager()->trigger(__FUNCTION__ . "post", $this, array("objSmsCampaign" => $objSmsCampaign));
		
		return $objSmsCampaign;
	} //end function
	
	/**
	 * Create Sms Campaign entity object.
	 * @param mixed $objData
	 * @return \FrontCommsSmsCampaigns\Entities\CommsSmsCampaignEntity
	 */
	private function createCommsSmsCampaignEntity($objData)
	{
		$entity_comms_sms_campaign = $this->getServiceLocator()->get("FrontCommsSmsCampaigns\Entities\CommsSmsCampaignEntity");
		// Populate data
		$entity_comms_sms_campaign->set($objData);
		return $entity_comms_sms_campaign;
	} // end function
}//end class
