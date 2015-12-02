<?php
namespace FrontPowerTools\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontPowerTools\Entities\FrontPowerToolsWebhookEntity;

class FrontPowerToolsWebhooksModel extends AbstractCoreAdapter
{
	/**
	 * Load the Webhooks Admin Form
	 * @return \Zend\Form\Form
	 */
	public function getWebhooksForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm("Core\Forms\SystemForms\Webhooks\WebhooksHookForm");
		
		return $objForm;
	}//end function
	
	/**
	 * Load a specific webhook
	 * @param mixed $id
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookEntity
	 */
	public function fetchWebhook($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/admin/$id");
		
		//execute
		$objResult = $objApiRequest->performGETRequest(array())->getBody();
		
		//create entity
		$objWebhook = $this->createWebhookEntity($objResult->data);
		
		return $objWebhook;
	}//end function
	
	/**
	 * Load a collection of webhooks
	 * @param array $arr_where - Optional
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookEntity
	 */
	public function fetchWebhooks($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/admin");
		
		//execute
		$objResult = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		//create entities
		foreach($objResult->data as $webhook)
		{
			//create entity
			$objWebhook = $this->createWebhookEntity($webhook);

			$arr[] = $objWebhook;
		}//end foreach
		
		return (object) $arr;
	}//end function
	
	/**
	 * Create a webhook
	 * @trigger : createWebhook.pre, createWebhook.post
	 * @param array $arr_data
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookEntity
	 */
	public function createWebhook(array $arr_data)
	{
		//create entity
		$objWebhook = $this->createWebhookEntity($arr_data);
		
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objWebhook" => $objWebhook));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/admin");
		
		//execute 
		$objResult = $objApiRequest->performPOSTRequest($objWebhook->getArrayCopy())->getBody();
		
		//recreate the entity
		$objWebhook = $this->createWebhookEntity($objResult->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objWebhook" => $objWebhook));
		
		return $objWebhook;
	}//end function
	
	/**
	 * Update a webhook
	 * @trigger : editWebhook.pre, editWebhook.post
	 * @param FrontPowerToolsWebhookEntity $objWebhook
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookEntity
	 */
	public function editWebhook(FrontPowerToolsWebhookEntity $objWebhook)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objWebhook" => $objWebhook));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/admin/" . $objWebhook->get("id"));
		
		//execute
		$objResult = $objApiRequest->performPUTRequest($objWebhook->getArrayCopy())->getBody();
		
		//recreate the entity
		$objWebhook = $this->createWebhookEntity($objResult->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objWebhook" => $objWebhook));
		
		return $objWebhook;
	}//end function
	
	/**
	 * Delete a webhook
	 * @trigger : deleteWebhook.pre, deleteWebhook.post
	 * @param mixed $id
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookEntity
	 */
	public function deleteWebhook($id)
	{
		//load webhook
		$objWebhook = $this->fetchWebhook($id);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objWebhook" => $objWebhook));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/admin/" . $objWebhook->get("id"));
		
		//execute
		$objResult = $objApiRequest->performDELETERequest(array())->getBody();
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objWebhook" => $objWebhook));
		
		return $objWebhook;
	}//end function
	
	/**
	 * Create an instance of the Webhook entity using the Service Manager and set data received
	 * @param mixed $objData
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookEntity
	 */
	private function createWebhookEntity($objData)
	{
		$entity_webhook = $this->getServiceLocator()->get("FrontPowerTools\Entities\FrontPowerToolsWebhookEntity");
		
		//populate data
		$entity_webhook->set($objData);
		
		return $entity_webhook;
	}//end function
}//end function