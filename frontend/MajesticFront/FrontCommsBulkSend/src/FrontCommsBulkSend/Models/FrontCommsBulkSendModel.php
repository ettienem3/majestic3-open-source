<?php
namespace FrontCommsBulkSend\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontCommsBulkSend\Entities\FrontCommsBulkSendJourneyEntity;

class FrontCommsBulkSendModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Form Admin Model
	 * @var \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private $model_form_admin;
	
	/**
	 * Container for the fields model
	 * @var \FrontFormAdmin\Models\FrontFieldAdminModel
	 */
	private $model_fields_admin;
	
	/**
	 * Container for the Registration Status Model
	 * @var \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private $model_contact_status;
	
	/**
	 * Container for the Users Model
	 * @var \CoreUsers\Models\CoreUsersModel
	 */
	private $model_users;
	
	/**
	 * Load a list of available journeys to trigger bulk send
	 * @param array $arr_where - Optional
	 */
	public function fetchJourneys($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/bulksend/journeys");
		
		//execute
		$objJourneys = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		return $objJourneys->data;
	}//end function
	
	/**
	 * Fetch data about a specific journey
	 * @param int $id
	 * @return \FrontCommsBulkSend\Entities\FrontCommsBulkSendJourneyEntity
	 */
	public function fetchJourney($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/bulksend/journeys/$id");
		
		//execute request
		$objData = $objApiRequest->performGETRequest()->getBody();
		
		//create entity
		$objJourney = $this->getServiceLocator()->get("FrontCommsBulkSend\Entities\FrontCommsBulkSendJourneyEntity");
		$objJourney->set($objData->data);
		
		return $objJourney;
	}//end function
	
	/**
	 * Load a list of webforms available
	 * @return stdClass
	 */
	public function fetchWebForms()
	{
		return $this->getFormAdminModel()->fetchForms();
	}//end function
	
	/**
	 * Fetch fields added to a form
	 * @param mixed $id
	 * @return array
	 */
	public function fetchWebFormFields($id)
	{
		$objForm = $this->getFormAdminModel()->fetchForm($id);
		$objFields = $objForm->getFormFieldEntities();
		foreach ($objFields as $objField)
		{
			if ($objField->get("field_std_id") != "")
			{
				//standard field
				$arr["standard_fields"][$objField->get("field_std_id")] = $objField->get("field_std_description");
			} else {
				//custom field
				$arr["custom_fields"][$objField->get("field_custom_id")] = $objField->get("field_custom_description");
			}//end if
		}//end foreach
		
		return $arr;
	}//end function
	
	/**
	 * Load a list of Standard Fields
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function fetchStandardFields()
	{
		//set a list of fields that are allowed
		$arr_exclude_fields = array(

		);
		
		$objFields = $this->getFieldsAdminModel()->fetchStandardFields();
		
		foreach ($objFields as $key => $objField)
		{
			if (!in_array($objField->get("field"), $arr_exclude_fields))
			{
				$arr[$objField->get("id")] = $objField;
			}//end if
		}//end foreach
		
		return (object) $arr;
	}//end function
	
	/**
	 * Load data for a specific standard field
	 * @param mixed $id
	 * @return string
	 */
	public function fetchStandardField($id, $value = FALSE, $objParam = FALSE)
	{
		$objField = $this->getFieldsAdminModel()->fetchStandardField($id, 1);
		
		//load helper
		$objStandardFieldHelper = $this->getServiceLocator()->get("FrontCommsBulkSend\Helpers\FrontCommsBulkSendStandardFieldHelper");
		return $objStandardFieldHelper->generateStandardFieldCriteriaHTML($objField, $value, $objParam);
	}//end function
	
	/**
	 * Load a list of Custom Fields
	 * @return \FrontFormAdmin\Entities\FrontFormAdminFieldEntity
	 */
	public function fetchCustomFields()
	{
		return $this->getFieldsAdminModel()->fetchCustomFields();
	}//end function
	
	/**
	 * Load data for a specific custom field
	 * @param mixed $id
	 * @return string
	 */
	public function fetchCustomField($id, $value = FALSE, $objParam = FALSE)
	{
		$objField = $this->getFieldsAdminModel()->fetchCustomField($id, 1);
		
		//load helper
		$objCustomFieldHelper = $this->getServiceLocator()->get("FrontCommsBulkSend\Helpers\FrontCommsBulkSendCustomFieldHelper");
		return $objCustomFieldHelper->generateCustomFieldCriteriaHTML($objField, $value, $objParam);
	}//end function
	
	/**
	 * Data about a specific user
	 * @param mixed $id
	 * @return \FrontUsers\Entities\FrontUserEntity
	 */
	public function fetchUser($id)
	{
		return $this->getUsersModel()->fetchUser($id);
	}//end function
	
	/**
	 * Load a list of available contact statuses
	 * @return object
	 */
	public function fetchContactStatuses()
	{
		$objContactStatuses = $this->getContactStatusModel()->fetchContactStatuses(array());

		foreach ($objContactStatuses as $objStatus)
		{
			$arr[] = array("value" => $objStatus->id, "text" => $objStatus->status);
		}//end foreach
		
		return $arr;
	}//end fucntion
	
	/**
	 * Load a collection of Bulk Send Requests
	 * @param array $arr_where - Optional
	 * @return stdClass
	 */
	public function fetchBulkSendRequests($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/bulksend/request");
		
		//execute request
		$objData = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		return $objData->data;
	}//end function
	
	/**
	 * Load a specific bulk send request
	 * @param mixed $id
	 * @return \FrontCommsBulkSend\Entities\FrontCommsBulkSendRequestEntity
	 */
	public function fetchBulkSendRequest($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/bulksend/request/$id");
		
		//execute request
		$objData = $objApiRequest->performGETRequest()->getBody();
		
		//create entity
		$objBulkSend = $this->getServiceLocator()->get("FrontCommsBulkSend\Entities\FrontCommsBulkSendRequestEntity");
		$objBulkSend->set($objData->data);
		
		return $objBulkSend;
	}//end function
	
	/**
	 * Fetch specific user data
	 * @param mixed $id
	 * @return \FrontUsers\Entities\FrontUserEntity
	 */
	public function readBulkSendRequest($id)
	{
		return $this->getUsersModel()->fetchUser($id);
	}//end function
	
	/**
	 * Create a Bulk Send Request
	 * @param arrat $arr_data
	 * @return \FrontCommsBulkSend\Entities\FrontCommsBulkSendRequestEntity
	 */
	public function createBulkSendRequest($arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/bulksend/request");
		
		//execute request
		$objData = $objApiRequest->performPOSTRequest($arr_data)->getBody();
		
		//create entity
		$objBulkSendRequest = $this->getServiceLocator()->get("FrontCommsBulkSend\Entities\FrontCommsBulkSendRequestEntity");
		$objBulkSendRequest->set($objData->data);

		return $objBulkSendRequest;
	}//end function
	
	/**
	 * Update the Bulk Send Request
	 * @param mixed $id
	 * @param array $arr_data
	 * @return \FrontCommsBulkSend\Entities\FrontCommsBulkSendRequestEntity
	 */
	public function editBulkSendRequest($id, $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/bulksend/request/$id");
		
		//execute request
		$objData = $objApiRequest->performPUTRequest($arr_data)->getBody();
		
		//create entity
		$objBulkSendRequest = $this->getServiceLocator()->get("FrontCommsBulkSend\Entities\FrontCommsBulkSendRequestEntity");
		$objBulkSendRequest->set($objData->data);
		
		return $objBulkSendRequest;
	}//end function
	
	/**
	 * Delete the Bulk Send Request
	 * @param mixed $id
	 */
	public function deleteBulkSendRequest($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/bulksend/request/$id");
		
		//execute request
		$objData = $objApiRequest->performDELETERequest($arr_data)->getBody();
	}//end function
	
	/**
	 * Request first level approval for a bulk comm
	 * @param mixed $id
	 */
	public function requestBulkSendRequestApproval($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/bulksend/request/$id?action=request-approval");
		
		//create data to submit
		$arr_data = array(
			"time" => time(),	
		);
		
		//execute the request
		$objData = $objApiRequest->performPUTRequest($arr_data)->getBody();
	}//end function
	
	/**
	 * Authorize a bulk send request
	 * @param mixed $id
	 * @param array $arr_data
	 * @return \FrontCommsBulkSend\Entities\FrontCommsBulkSendRequestEntity
	 */
	public function authorizeBulkSendRequest($id, $arr_data = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/bulksend/request/$id?action=authorize-final-approval");
		
		//execute the request
		$objData = $objApiRequest->performPUTRequest($arr_data)->getBody();
		
		//create entity
		$objBulkSend = $this->getServiceLocator()->get("FrontCommsBulkSend\Entities\FrontCommsBulkSendRequestEntity");
		$objBulkSend->set($objData->data);
		
		return $objBulkSend;
	}//end function
	
	/**
	 * Request cancellation for first level approval for a Bulk Send Request
	 * @param mixed $id
	 */
	public function requestBulkSendApprovalCancellation($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/bulksend/request/$id?action=cancel-approval");
		
		//create data to submit
		$arr_data = array(
				"time" => time(),
		);
		
		//execute the request
		$objData = $objApiRequest->performPUTRequest($arr_data)->getBody();
	}//end function
	
	/**
	 * Create and instance of the Front Form Admin Model using the Service Manager
	 * @return \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private function getFormAdminModel()
	{
		if (!$this->model_form_admin)
		{
			$this->model_form_admin = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontFormAdminModel");
		}//end if
		
		return $this->model_form_admin;
	}//end function
	
	/**
	 * Create an instance of the Front Fields Admin Model using the Service Manager
	 * @return \FrontFormAdmin\Models\FrontFieldAdminModel
	 */
	private function getFieldsAdminModel()
	{
		if (!$this->model_fields_admin)
		{
			$this->model_fields_admin = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontFieldAdminModel");
		}//end if
		
		return $this->model_fields_admin;
	}//end function
	
	/**
	 * Create an instance of the Contact Status Model using the Service Manager\
	 * @return \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private function getContactStatusModel()
	{
		if (!$this->model_contact_status)
		{
			$this->model_contact_status = $this->getServiceLocator()->get("FrontStatuses\Models\FrontContactStatusesModel");
		}//end if
		
		return $this->model_contact_status;
	}//end fucntion
	
	/**
	 * Create an instance for the Users Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUsersModel
	 */
	private function getUsersModel()
	{
		if (!$this->model_users)
		{
			$this->model_users = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersModel");
		}//end if
		
		return $this->model_users;
	}//end function
}//end class
