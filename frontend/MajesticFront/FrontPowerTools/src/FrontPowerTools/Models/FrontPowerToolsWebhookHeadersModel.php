<?php
namespace FrontPowerTools\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontPowerTools\Entities\FrontPowerToolsWebhookEntity;
use FrontPowerTools\Entities\FrontPowerToolsWebhookHeaderEntity;

class FrontPowerToolsWebhookHeadersModel extends AbstractCoreAdapter
{
	/**
	 * Load Webhook Header Admin Form
	 * @return \Zend\Form\Form
	 */
	public function getWebhookHeaderForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
		->getSystemForm("Core\Forms\SystemForms\Webhooks\WebhooksHeaderForm");
		
		return $objForm;
	}//end function
		
	/**
	 * Load a specific Webhook Header for a defined Webhook
	 * @param mixed $id
	 * @param mixed $webhook_id
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookHeaderEntity
	 */
	public function fetchWebhookHeader($id, $webhook_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/headers/admin/$id?webhook_id=$webhook_id");
		
		//execute
		$objResult = $objApiRequest->performGETRequest(array())->getBody();
		
		//create entity
		$objWebhookHeader = $this->createWebhookHeaderEntity($objResult->data);
		
		return $objWebhookHeader;
	}//end function
	
	/**
	 * Load a collection of Webhook Headers for a defined Webhook
	 * @param array $arr_where
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookHeaderEntity
	 */
	public function fetchWebhookHeaders(array $arr_where)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/headers/admin");
		
		//execute
		$objResult = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		//create entities
		foreach($objResult->data as $webhook_header)
		{
			//create entity
			$objWebhookHeader = $this->createWebhookHeaderEntity($webhook_header);
		
			if ($webhook_header->id == "")
			{
				continue;
			}//end if
			
			$arr[] = $objWebhookHeader;
		}//end foreach
		
		return (object) $arr;
	}//end function
	
	/**
	 * Create a Webhook Header for a defined Webhook
	 * @trigger : createWebhookHeader.pre, createWebhookHeader.post
	 * @param array $arr_data
	 * @param mixed $webhook_id
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookHeaderEntity
	 */
	public function createWebhookHeader(array $arr_data, $webhook_id)
	{
		//create webhook header entity
		$objWebhookHeader = $this->createWebhookHeaderEntity($arr_data);
		
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objWebhookHeader" => $objWebhookHeader));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/headers/admin?webhook_id=$webhook_id");
		
		//execute
		$objResult = $objApiRequest->performPOSTRequest($objWebhookHeader->getArrayCopy())->getBody();
		
		//recreate the entity
		$objWebhookHeader = $this->createWebhookHeaderEntity($objResult->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objWebhookHeader" => $objWebhookHeader));
		
		return $objWebhookHeader;
	}//end function
	
	/**
	 * Update a Webhook Header for a given Webhook
	 * @trigger : editWebhookHeader.pre, editWebhookHeader.post
	 * @param FrontPowerToolsWebhookHeaderEntity $objWebhookHeader
	 * @param mixed $webhook_id
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookHeaderEntity
	 */
	public function editWebhookHeader(FrontPowerToolsWebhookHeaderEntity $objWebhookHeader, $webhook_id)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objWebhookHeader" => $objWebhookHeader));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/headers/admin/" . $objWebhookHeader->get("id") . "?webhook_id=$webhook_id");
		
		//execute
		$objResult = $objApiRequest->performPUTRequest($objWebhookHeader->getArrayCopy())->getBody();
		
		//recreate the entity
		$objWebhookHeader = $this->createWebhookHeaderEntity($objResult->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objWebhookHeader" => $objWebhookHeader));
		
		return $objWebhookHeader;
	}//end function
	
	/**
	 * Delete a Webhook Header for a defined Webhook
	 * @trigger : deleteWebhookHeader.pre, deleteWebhookHeader.post
	 * @param mixed $id
	 * @param mixed $webhook_id
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookHeaderEntity
	 */
	public function deleteWebhookHeader($id, $webhook_id)
	{
		//load Header
		$objWebhookHeader = $this->fetchWebhookHeader($id, $webhook_id);
		
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objWebhookHeader" => $objWebhookHeader));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/headers/admin/" . $objWebhookHeader->get("id") . "?webhook_id=$webhook_id");
		
		//execute
		$objResult = $objApiRequest->performDELETERequest(array())->getBody();
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objWebhookHeader" => $objWebhookHeader));
		
		return $objWebhookHeader;
	}//end function
	
	/**
	 * Create an instance of the Webhook Header Entity using the Service Manager
	 * @param mixed $objData
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookHeaderEntity
	 */
	private function createWebhookHeaderEntity($objData)
	{
		$objWebhookHeader = $this->getServiceLocator()->get("FrontPowerTools\Entities\FrontPowerToolsWebhookHeaderEntity");
		
		//set data
		$objWebhookHeader->set($objData);
		
		return $objWebhookHeader;
	}//end function
}//end function