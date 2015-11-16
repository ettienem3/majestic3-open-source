<?php
namespace FrontPowerTools\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontPowerTools\Entities\FrontPowerToolsWebhookUrlEntity;

class FrontPowerToolsWebhookUrlsModel extends AbstractCoreAdapter
{
	/**
	 * Load the Webhook Urls Admin Form
	 * @return \Zend\Form\Form
	 */
	public function getWebhookUrlForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
		->getSystemForm("Core\Forms\SystemForms\Webhooks\WebhooksUrlForm");
		
		return $objForm;
	}//end function
	
	/**
	 * Load a specifc Webhook Url
	 * @param mixed $id
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookUrlEntity
	 */
	public function fetchWebhookUrl($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/urls/admin/$id");
		
		//execute
		$objResult = $objApiRequest->performGETRequest(array())->getBody();
		
		//create entity
		$objWebhookUrl = $this->createWebhookUrlEntity($objResult->data);
		
		return $objWebhookUrl;
	}//end function
	
	/**
	 * Load a collection of Webhook Urls
	 * @param array $arr_where - Optional
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookUrlEntity
	 */
	public function fetchWebhookUrls($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/urls/admin");
		
		//execute
		$objResult = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		//create entities
		foreach($objResult->data as $webhook)
		{
			//create entity
			$objWebhookUrl = $this->createWebhookUrlEntity($webhook);
		
			$arr[] = $objWebhookUrl;
		}//end foreach
		
		return (object) $arr;
	}//end function
	
	/**
	 * Create a Webhook Url 
	 * @trigger : createWebhookUrl.pre, createWebhookUrl.post
	 * @param array $arr_data
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookUrlEntity
	 */
	public function createWebhookUrl(array $arr_data)
	{
		//create entity
		$objWebhookUrl = $this->createWebhookUrlEntity($arr_data);
		
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objWebhookUrl" => $objWebhookUrl));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/urls/admin");
		
		//execute
		$objResult = $objApiRequest->performPOSTRequest($objWebhookUrl->getArrayCopy())->getBody();
		
		//recreate the entity
		$objWebhookUrl = $this->createWebhookUrlEntity($objResult->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objWebhookUrl" => $objWebhookUrl));
		
		return $objWebhookUrl;
	}//end function
	
	/**
	 * Update a Webhook Url
	 * @trigger : editWebhookUrl.pre, editWebhookUrl.post
	 * @param FrontPowerToolsWebhookUrlEntity $objWebhookUrl
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookUrlEntity
	 */
	public function editWebhookUrl(FrontPowerToolsWebhookUrlEntity $objWebhookUrl)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objWebhookUrl" => $objWebhookUrl));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/urls/admin/" . $objWebhookUrl->get("id"));
		
		//execute
		$objResult = $objApiRequest->performPUTRequest($objWebhookUrl->getArrayCopy())->getBody();
		
		//recreate the entity
		$objWebhookUrl = $this->createWebhookUrlEntity($objResult->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objWebhookUrl" => $objWebhookUrl));
		
		return $objWebhookUrl;
	}//end function
	
	/**
	 * Delete a webhook url
	 * @trigger : deleteWebhookUrl.pre, deleteWebhookUrl.post
	 * @param mixed $id
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookUrlEntity
	 */
	public function deleteWebhookUrl($id)
	{
		//load webhook
		$objWebhookUrl = $this->fetchWebhookUrl($id);
		
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objWebhookUrl" => $objWebhookUrl));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("webhooks/urls/admin/" . $objWebhookUrl->get("id"));
		
		//execute
		$objResult = $objApiRequest->performDELETERequest(array())->getBody();
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objWebhookUrl" => $objWebhookUrl));
		
		return $objWebhookUrl;
	}//end function
	
	/**
	 * Create an instance of the Webhook Url Entity using the Service Manager
	 * @param mixed $objData
	 * @return \FrontPowerTools\Entities\FrontPowerToolsWebhookUrlEntity
	 */
	private function createWebhookUrlEntity($objData)
	{
		//create the entity
		$objWebhookEntity = $this->getServiceLocator()->get("FrontPowerTools\Entities\FrontPowerToolsWebhookUrlEntity");
		
		//populate data
		$objWebhookEntity->set($objData);
		
		return $objWebhookEntity;
	}//end function
}//end function