<?php
namespace FrontCommsBulkSend\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class BulkSendController extends AbstractActionController
{
	/**
	 * Container for the Front Comms Bulk Send Model
	 * @var \FrontCommsBulkSend\Models\FrontCommsBulkSendModel
	 */
	private $model_front_comms_bulk_send;
	
	public function indexAction()
	{
		$objBulkSendRequests = $this->getFrontCommsBulkSendModel()->fetchBulkSendRequests($this->params()->fromQuery());

		return array(
			"objBulkSendRequests" => $objBulkSendRequests,
			"model_front_comms_bulk_send" => $this->getFrontCommsBulkSendModel(),
		);
	}//end function
	
	public function reviewAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Bulk Send Request could not be loaded. ID is not set");
			
			//redirect to index page
			$this->redirect()->toRoute("front-comms-bulksend-admin");
		}//end if
		
		//load data
		$objBulkSendRequest = $this->getFrontCommsBulkSendModel()->fetchBulkSendRequest($id);
		$objJourney = $this->getFrontCommsBulkSendModel()->fetchJourney($objBulkSendRequest->get("fk_journey_id"));
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			/**
			 * Update the request
			 */
			if (strtolower($request->getPost("submit_update")) == "update")
			{
				try {
					//update the request
					$objBulkSendRequest = $this->getFrontCommsBulkSendModel()->editBulkSendRequest($id, (array) $request->getPost());
						
					//set success message
					$this->flashMessenger()->addSuccessMessage("Bulk Send Request has been updated");
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage("An error occured : " . $e->getMessage());
				}//end catch
			}//end if

			/**
			 * Request admin approval
			 */
			if (strtolower($request->getPost("request_approval")) == "request approval")
			{
				try {
					//submit request for approval
					$this->getFrontCommsBulkSendModel()->requestBulkSendRequestApproval($id);
						
					//set success message
					$this->flashMessenger()->addInfoMessage("Bulk Send Request has been submitted for Administrator Approval");
						
					//redirect back to the index
					return $this->redirect()->toRoute("front-comms-bulksend-admin");
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage("An error occured : " . $e->getMessage());
				}//end catch
			}//end if
			
			/**
			 * Cancel the request
			 */
			if (strtolower($request->getPost("cancel_approval")) == "cancel approval")
			{
				try {
					//submit request for approval cancelation
					$this->getFrontCommsBulkSendModel()->requestBulkSendApprovalCancellation($id);
					
					$this->flashMessenger()->addInfoMessage("Approval cancelation request has been sent");
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage("An error occured : " . $e->getMessage());
				}//end catch
			}//end if
			
			/**
			 * Delete the request
			 */
			if (strtolower($request->getPost("delete_request")) == "delete request")
			{
				try {
					//delete the request
					$this->getFrontCommsBulkSendModel()->deleteBulkSendRequest($id);
					
					//set success message
					$this->flashMessenger()->addSuccessMessage("Bulk Send Request has been deleted");
					
					//redirect back to the index page
					return $this->redirect()->toRoute("front-comms-bulksend-admin");
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage("An error occured : " . $e->getMessage());
				}//end catch
			}//end if
		}//end if
		
		/**
		 * Load possible required models
		 */
		$model_contact_status = $this->getServiceLocator()->get("FrontStatuses\Models\FrontContactStatusesModel");
		
		return array(
			"objBulkSendRequest" => $objBulkSendRequest,
			"objJourney" => $objJourney,
			"model_contact_status" => $model_contact_status,
			"model_front_comms_bulk_send" => $this->getFrontCommsBulkSendModel(),
		);
	}//end function
	
	public function authorizeAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Bulk Send Request could not be loaded. ID is not set");
				
			//redirect to index page
			$this->redirect()->toRoute("front-comms-bulksend-admin");
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			try {
					//execute autorize request
					$objBulkSendRequest = $this->getFrontCommsBulkSendModel()->authorizeBulkSendRequest($id, (array) $request->getPost());
					
					//set success message
					$this->flashMessenger()->addSuccessMessage("Bulk Send Request has been approved");
					
					//redirect back to the index page
					return $this->redirect()->toRoute("front-comms-bulksend-admin");
				} catch (\Exception $e) {
					//extract response from string
					$arr = explode("||", $e->getMessage());
					$objResponse = json_decode($arr[1]);
			
					$this->flashMessenger()->addErrorMessage($objResponse->HTTP_RESPONSE_MESSAGE);
					
					//reload data
					$objBulkSendRequest = $this->getFrontCommsBulkSendModel()->authorizeBulkSendRequest($id, array("time" => time()));
				}//end catch
		} else {
			//simulate authorization in order to get confirmation code
 			$objBulkSendRequest = $this->getFrontCommsBulkSendModel()->authorizeBulkSendRequest($id, array("time" => time()));
		}//end if
		
		return array(
			"objBulkSendRequest" => $objBulkSendRequest
		);
	}//end function
	
	/**
	 * Create an instance of the Front Comms Bulk Send Model using the Service Manager
	 * @return \FrontCommsBulkSend\Models\FrontCommsBulkSendModel
	 */
	private function getFrontCommsBulkSendModel()
	{
		if (!$this->model_front_comms_bulk_send)
		{
			$this->model_front_comms_bulk_send = $this->getServiceLocator()->get("FrontCommsBulkSend\Models\FrontCommsBulkSendModel");
		}//end if
		
		return $this->model_front_comms_bulk_send;
	}//end function
}//end class