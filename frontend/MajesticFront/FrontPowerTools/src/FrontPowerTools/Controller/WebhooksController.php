<?php 
namespace FrontPowerTools\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class WebhooksController extends AbstractActionController
{
	/**
	 * Container for the Webhooks Model
	 * @var \FrontPowerTools\Models\FrontPowerToolsWebhooksModel
	 */
	private $model_webhooks;
	
	/**
	 * Container for the Webhook Urls Model
	 * @var \FrontPowerTools\Models\FrontPowerToolsWebhookUrlsModel
	 */
	private $model_webhook_urls;
	
	/**
	 * Container for the Webhook Headers Model
	 * @var \FrontPowerTools\Models\FrontPowerToolsWebhookHeadersModel
	 */
	private $model_webhook_headers;
	
	/*****************************************************************************
	 * Webhooks section
	 *****************************************************************************/
	
	public function webhooksAction()
	{
		//load webhooks
		$objWebhooks = $this->getWebhooksModel()->fetchWebhooks();
		
		return array(
			"objWebhooks" => $objWebhooks,
		);
	}//end function
	
	public function createWebhookAction()
	{
		//load form
		$form = $this->getWebhooksModel()->getWebhooksForm();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			
			if ($form->isValid($request->getPost()))
			{
				try {
					//create the webhook
					$objWebhook = $this->getWebhooksModel()->createWebhook((array) $form->getData());
					
					//set success message
					$this->flashMessenger()->addSuccessMessage("Webhook has been created");
					
					//redirect to the index page
					return $this->redirect()->toRoute("front-power-tools/webhooks");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			} else {
				
			}//end if
		}//end if

		return array(
			"form" => $form,
		);
	}//end function
	
	public function editWebhookAction()
	{
		$id = $this->params()->fromRoute("id");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Webhook could not be loaded. ID is not set");
			
			//redirect to the index page
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end if
		
		//load the hook
		try {
			$objWebhook = $this->getWebhooksModel()->fetchWebhook($id);
			
			if ($objWebhook->get("id") == "")
			{
				$this->flashMessenger()->addErrorMessage("Requested Webhook could not be located");
				
				//redirect to the index page
				return $this->redirect()->toRoute("front-power-tools/webhooks");
			}//end if
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
			
			//redirect to the index page
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end catch
		
		//load the form
		$form = $this->getWebhooksModel()->getWebhooksForm();
		
		//bind data to form
		$form->bind($objWebhook);
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			
			if ($form->isValid($request->getPost()))
			{
				try {
					$objWebhook = $form->getData();
					$objWebhook->set("id", $id);
					
					//update the hook
					$objWebhook = $this->getWebhooksModel()->editWebhook($objWebhook);
					
					//set success message
					$this->flashMessenger()->addSuccessMessage("Webhook has been updated");
					
					//return to the index page
					return $this->redirect()->toRoute("front-power-tools/webhooks");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if
		
		return array(
			"form" => $form,
			"objWebhook" => $objWebhook,
		);
	}//end function
	
	public function deleteWebhookAction()
	{
		$id = $this->params()->fromRoute("id");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Webhook could not be loaded. ID is not set");
				
			//redirect to the index page
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end if
		
		//load the hook
		try {
			$objWebhook = $this->getWebhooksModel()->fetchWebhook($id);
				
			if ($objWebhook->get("id") == "")
			{
				$this->flashMessenger()->addErrorMessage("Requested Webhook could not be located");
		
				//redirect to the index page
				return $this->redirect()->toRoute("front-power-tools/webhooks");
			}//end if
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
				
			//redirect to the index page
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end catch

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{	
				try {
					$this->getWebhooksModel()->deleteWebhook($objWebhook->get("id"));
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				}//end catch
			}//end if
			
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end if
		
		return array(
			"objWebhook" => $objWebhook,
		);
	}//end function
	
	public function toggleWebhookStatusAction()
	{
		$id = $this->params()->fromRoute("id");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Webhook could not be loaded. ID is not set");
				
			//redirect to the index page
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end if
		
		//load the hook
		try {
			$objWebhook = $this->getWebhooksModel()->fetchWebhook($id);
				
			if ($objWebhook->get("id") == "")
			{
				$this->flashMessenger()->addErrorMessage("Requested Webhook could not be located");
		
				//redirect to the index page
				return $this->redirect()->toRoute("front-power-tools/webhooks");
			}//end if
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
				
			//redirect to the index page
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end catch
		
		//update status
		$objWebhook->set("active", (1 - $objWebhook->get("active")));
		
		try {
			$this->getWebhooksModel()->editWebhook($objWebhook);
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
		}//end catch
		
		//redirect to the index page
		return $this->redirect()->toRoute("front-power-tools/webhooks");
	}//end function
	
	
	/*****************************************************************************
	 * Webhook Urls section
	 *****************************************************************************/
	
	public function webhookUrlsAction()
	{
		//load hook urls
		$objWebhookUrls = $this->getWebhookUrlsModel()->fetchWebhookUrls();
		
		return array(
			"objWebhookUrls" => $objWebhookUrls,
		);
	}//end function
	
	public function createWebhookUrlAction()
	{
		//load form
		$form = $this->getWebhookUrlsModel()->getWebhookUrlForm();
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			
			if ($form->isValid($request->getPost()))
			{
				try {
					//create url
					$objWebhookUrl = $this->getWebhookUrlsModel()->createWebhookUrl((array) $form->getData());
					
					//set message
					$this->flashMessenger()->addSuccessMessage("Webhook Url Endpoint has been created");
					
					//redirect back to the urls index 
					return $this->redirect()->toRoute("front-power-tools/webhooks", array("action" => "webhook-urls"));
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if
		
		return array(
			"form" => $form,
		);
	}//end function
	
	public function editWebhookUrlAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Webhook Url could not be loaded. ID is not set");
			
			//return the webhook urls index page
			return $this->redirect()->toRoute("front-power-tools/webhooks", array("action" => "webhook-urls"));
		}//end if
		
		//load data
		try {
			$objWebhookUrl = $this->getWebhookUrlsModel()->fetchWebhookUrl($id);
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
			
			//return the webhook urls index page
			return $this->redirect()->toRoute("front-power-tools/webhooks", array("action" => "webhook-urls"));
		}//end catch
		
		//load form
		$form = $this->getWebhookUrlsModel()->getWebhookUrlForm();
		
		//bind data
		$form->bind($objWebhookUrl);
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			
			try {
				if ($form->isValid($request->getPost()))
				{
					$objWebhookUrl = $form->getData();
					$objWebhookUrl->set("id", $id);
					
					//update the url
					$objWebhookUrl = $this->getWebhookUrlsModel()->editWebhookUrl($objWebhookUrl);
					
					//set message
					$this->flashMessenger()->addSuccessMessage("Webhook Url has been updated");
					
					//return the webhook urls index page
					return $this->redirect()->toRoute("front-power-tools/webhooks", array("action" => "webhook-urls"));
				}//end if	
			} catch (\Exception $e) {
				//set error message
				$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
			}//end catch
		}//end if
		
		return array(
			"form" => $form,
			"objWebhookUrl" => $objWebhookUrl,
		);
	}//end function
	
	public function deleteWebhookUrlAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Webhook Url could not be loaded. ID is not set");
				
			//return the webhook urls index page
			return $this->redirect()->toRoute("front-power-tools/webhooks", array("action" => "webhook-urls"));
		}//end if
		
		//load data
		try {
			$objWebhookUrl = $this->getWebhookUrlsModel()->fetchWebhookUrl($id);
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
				
			//return the webhook urls index page
			return $this->redirect()->toRoute("front-power-tools/webhooks", array("action" => "webhook-urls"));
		}//end catch
		
		$request = $this->getRequest();
		if($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{
				try {
					$this->getWebhookUrlsModel()->deleteWebhookUrl($id);
					
					//set success message
					$this->flashMessenger()->addSuccessMessage("Webhook Url has been removed");
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				}//end catch
			}//end if
			
			//return the webhook urls index page
			return $this->redirect()->toRoute("front-power-tools/webhooks", array("action" => "webhook-urls"));
		}//end if
		
		return array(
			"objWebhookUrl" => $objWebhookUrl,
		);
	}//end function
	
	public function toggleWebhookUrlStatusAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Webhook Url could not be loaded. ID is not set");
				
			//return the webhook urls index page
			return $this->redirect()->toRoute("front-power-tools/webhooks", array("action" => "webhook-urls"));
		}//end if
		
		//load data
		try {
			$objWebhookUrl = $this->getWebhookUrlsModel()->fetchWebhookUrl($id);
			
			//set data
			$objWebhookUrl->set("active", (1 - $objWebhookUrl->get("active")));
			
			$this->getWebhookUrlsModel()->editWebhookUrl($objWebhookUrl);
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
		}//end catch
		
		//return the webhook urls index page
		return $this->redirect()->toRoute("front-power-tools/webhooks", array("action" => "webhook-urls"));
	}//end function
	
	/*****************************************************************************
	 * Webhook Headers section
	 *****************************************************************************/
	
	public function webhookHeadersAction()
	{
		//load webhook
		try {
			$objWebhook = $this->loadWebhookData();
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
			
			//return to the webhooks index page
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end catch
		
		//load headers
		$objWebhookHeaders = $this->getWebhookHeadersModel()->fetchWebhookHeaders(array(
			"webhook_id" => $objWebhook->get("id"),
		));
		
		return array(
			"objWebhook" => $objWebhook,
			"objWebhookHeaders" => $objWebhookHeaders,
		);
	}//end function
	
	public function createWebhookHeaderAction()
	{
		//load webhook
		try {
			$objWebhook = $this->loadWebhookData();
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
				
			//return to the webhooks index page
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end catch
		
		//load form
		$form = $this->getWebhookHeadersModel()->getWebhookHeaderForm();
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			
			if ($form->isValid($request->getPost()))
			{
// 				try {
					//create the header
					$objWebhookHeader = $this->getWebhookHeadersModel()->createWebhookHeader((array) $form->getData(), $objWebhook->get("id"));
					
					//set success message
					$this->flashMessenger()->addSuccessMessage("Webhook Header has been created");
					
					//return to webhook headers index page
					return $this->redirect()->toUrl($this->url()->fromRoute("front-power-tools/webhooks", array("action" => "webhook-headers")) . "?webhook_id=" . $objWebhook->get("id"));
// 				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
// 				}//end catch
			}//end if
		}//end if
		
		return array(
			"form" => $form,
			"objWebhook" => $objWebhook,
		);
	}//end function
	
	public function editWebhookHeaderAction()
	{
		//load webhook
		try {
			$objWebhook = $this->loadWebhookData();
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
				
			//return to the webhooks index page
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end catch
		
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Webhook Header could not be loaded. ID is not set");
			
			//return to webhook headers index page
			return $this->redirect()->toUrl($this->url()->fromRoute("front-power-tools/webhooks", array("action" => "webhook-headers")) . "?webhook_id=" . $objWebhook->get("id"));
		}//end if
		
		try {
			//load data
			$objWebhookHeader = $this->getWebhookHeadersModel()->fetchWebhookHeader($id, $objWebhook->get("id"));
			
			//load form
			$form = $this->getWebhookHeadersModel()->getWebhookHeaderForm();
			
			//bind data to the form
			$form->bind($objWebhookHeader);
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
			
			//return to webhook headers index page
			return $this->redirect()->toUrl($this->url()->fromRoute("front-power-tools/webhooks", array("action" => "webhook-headers")) . "?webhook_id=" . $objWebhook->get("id"));
		}//end catch
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			
			if ($form->isValid($request->getPost()))
			{
				try {
					$objWebhookHeader = $form->getData();
					$objWebhookHeader->set("id", $id);
					
					//update the header
					$objWebhookHeader = $this->getWebhookHeadersModel()->editWebhookHeader($objWebhookHeader, $objWebhook->get("id"));
					
					//set success message
					$this->flashMessenger()->addSuccessMessage("Webhook Header has been saved");
					
					//return to webhook headers index page
					return $this->redirect()->toUrl($this->url()->fromRoute("front-power-tools/webhooks", array("action" => "webhook-headers")) . "?webhook_id=" . $objWebhook->get("id"));
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if
		
		return array(
			"form" => $form,
			"objWebhook" => $objWebhook,
		);
	}//end function
	
	public function deleteWebhookHeaderAction()
	{
		//load webhook
		try {
			$objWebhook = $this->loadWebhookData();
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
				
			//return to the webhooks index page
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end catch
		
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Webhook Header could not be loaded. ID is not set");
				
			//return to webhook headers index page
			return $this->redirect()->toUrl($this->url()->fromRoute("front-power-tools/webhooks", array("action" => "webhook-headers")) . "?webhook_id=" . $objWebhook->get("id"));
		}//end if
		
		try {
			//load data
			$objWebhookHeader = $this->getWebhookHeadersModel()->fetchWebhookHeader($id, $objWebhook->get("id"));
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
				
			//return to webhook headers index page
			return $this->redirect()->toUrl($this->url()->fromRoute("front-power-tools/webhooks", array("action" => "webhook-headers")) . "?webhook_id=" . $objWebhook->get("id"));
		}//end catch
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{	
				try {
					$this->getWebhookHeadersModel()->deleteWebhookHeader($objWebhookHeader->get("id"), $objWebhook->get("id"));
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				}//end catch
			}//end if
			
			//return to webhook headers index page
			return $this->redirect()->toUrl($this->url()->fromRoute("front-power-tools/webhooks", array("action" => "webhook-headers")) . "?webhook_id=" . $objWebhook->get("id"));
		}//end if
		
		return array(
			"objWebhookHeader" => $objWebhookHeader,
			"objWebhook" => $objWebhook,
		);
	}//end fucntion
	
	public function toggleWebhookHeaderStatusAction()
	{
		//load webhook
		try {
			$objWebhook = $this->loadWebhookData();
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
				
			//return to the webhooks index page
			return $this->redirect()->toRoute("front-power-tools/webhooks");
		}//end catch
		
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Webhook Header could not be loaded. ID is not set");
		
			//return to webhook headers index page
			return $this->redirect()->toUrl($this->url()->fromRoute("front-power-tools/webhooks", array("action" => "webhook-headers")) . "?webhook_id=" . $objWebhook->get("id"));
		}//end if
		
		try {
			//load data
			$objWebhookHeader = $this->getWebhookHeadersModel()->fetchWebhookHeader($id, $objWebhook->get("id"));
			
			//set data
			$objWebhookHeader->set("active", (1 - $objWebhookHeader->get("active")));
			
			//save the data
			$this->getWebhookHeadersModel()->editWebhookHeader($objWebhookHeader, $objWebhook->get("id"));
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
		}//end catch
		
		//return to webhook headers index page
		return $this->redirect()->toUrl($this->url()->fromRoute("front-power-tools/webhooks", array("action" => "webhook-headers")) . "?webhook_id=" . $objWebhook->get("id"));
	}//end function
	
	private function loadWebhookData()
	{
		$webhook_id = $this->params()->fromQuery("webhook_id", "");
		if ($webhook_id == "")
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Webhook could not be loaded. ID is not set", 500);
		}//end

		//load data
		$objWebhook = $this->getWebhooksModel()->fetchWebhook($webhook_id);
		return $objWebhook;
	}//end function
	
	/**
	 * Create an instance of the Front Power Tools Webhooks Model using the Service Manager
	 * @return \FrontPowerTools\Models\FrontPowerToolsWebhooksModel
	 */
	private function getWebhooksModel()
	{
		if (!$this->model_webhooks)
		{
			$this->model_webhooks = $this->getServiceLocator()->get("FrontPowerTools\Models\FrontPowerToolsWebhooksModel");
		}//end if
		
		return $this->model_webhooks;
	}//end function
	
	/**
	 * Create an instance of the Front Power Tools Webhook Headers Model using the Service Manager
	 * @return \FrontPowerTools\Models\FrontPowerToolsWebhookHeadersModel
	 */
	private function getWebhookHeadersModel()
	{
		if (!$this->model_webhook_headers)
		{
			$this->model_webhook_headers = $this->getServiceLocator()->get("FrontPowerTools\Models\FrontPowerToolsWebhookHeadersModel");
		}//end if
		
		return $this->model_webhook_headers;
	}//end function
	
	/**
	 * Create an intance of the Front Power Tools Webhook Headers Model using the Service Manager
	 * @return \FrontPowerTools\Models\FrontPowerToolsWebhookUrlsModel
	 */
	private function getWebhookUrlsModel()
	{
		if (!$this->model_webhook_urls)
		{
			$this->model_webhook_urls = $this->getServiceLocator()->get("FrontPowerTools\Models\FrontPowerToolsWebhookUrlsModel");
		}//end if
		
		return $this->model_webhook_urls;
	}//end function
}//end class