<?php
namespace FrontCommsBulkSend\Controller;

use Zend\View\Model\JsonModel;
use FrontCore\Adapters\AbstractCoreActionController;

class BulkSendController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Comms Bulk Send Model
	 * @var \FrontCommsBulkSend\Models\FrontCommsBulkSendModel
	 */
	private $model_front_comms_bulk_send;

	public function indexAction()
	{
		try {
			$objBulkSendRequests = $this->getFrontCommsBulkSendModel()->fetchBulkSendRequests($this->params()->fromQuery());
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
			return $this->redirect()->toRoute('home');
		}//end catch

		return array(
				"objBulkSendRequests" => $objBulkSendRequests,
				"model_front_comms_bulk_send" => $this->getFrontCommsBulkSendModel(),
		);
	}//end function

	public function appAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['bulk-send-tool'] != true)
		{
			$this->flashMessenger()->addInfoMessage('The requested view is not available');
			//redirect to index page
			$this->redirect()->toRoute("front-comms-bulksend");
		}//end if

		$this->layout('layout/angular/app');
	}//end function

	public function ajaxRequestAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['journeys'] != true)
		{
			return new JsonModel(array(
					'error' => 1,
					'response' => 'Requested functionality is not available',
			));
		}//end if

		$arr_params = $this->params()->fromQuery();
		if (isset($arr_params['acrq']))
		{
			$acrq = $arr_params['acrq'];
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_post_data = json_decode(file_get_contents('php://input'), true);
			if (isset($arr_post_data['acrq']))
			{
				$acrq = $arr_post_data['acrq'];
				unset($arr_post_data['acrq']);
			}//end if

			if (isset($arr_post_data['journey_id']))
			{
				$arr_params['journey_id'] = $arr_post_data['journey_id'];
			}//end if
		}//end if

		try {
			switch ($acrq)
			{
				case 'load-pending-requests':
					$objRequests = $this->getFrontCommsBulkSendModel()->fetchBulkSendRequests();

					$arr_requests = array();
					foreach ($objRequests as $objRequest)
					{
						if (isset($objRequest->id))
						{
							$arr_requests[] = $objRequest;
						}//end if
					}//end foreach

					$objResult = new JsonModel(array(
						'error' => 0,
						'objData' => (array) $arr_requests,
					));
					return $objResult;
					break;

				case 'load-request-data':
					$objRequest = $this->getFrontCommsBulkSendModel()->fetchBulkSendRequest($arr_params['id']);

					$objResult = new JsonModel(array(
							'error' => 0,
							'objData' => (object) $objRequest->getArrayCopy(),
					));
					return $objResult;
					break;

				case 'load-contact-statuses':
					$arr_statuses = $this->getFrontCommsBulkSendModel()->fetchContactStatuses();

					$objResult = new JsonModel(array(
						'error' => 0,
						'objData' => (object) $arr_statuses,
					));
					return $objResult;
					break;

				case 'load-web-forms':
					$objForms = $this->getFrontCommsBulkSendModel()->fetchWebForms(array(
						'qp_export_fields' 				=> 'id,form,form_types_behaviour,description', //only load specific fields
						'qp_limit' 						=> 'all', //load all forms
						'qp_disable_hypermedia' 		=> 1, //hypermedia must be disabled for qp_limit => all to be accepted
					));

					$arr = array();
					foreach ($objForms as $objForm)
					{
						if ($objForm->id == "" || str_replace('_', '', $objForm->form_types_behaviour) != 'web')
						{
							continue;
						}//end if

						$arr[] = $objForm;
					}//end foreach

					$objResult = new JsonModel(array(
						'error' => 0,
						'objData' => (object) $arr,
					));
					return $objResult;
					break;

				case 'load-trackers':
					$objForms = $this->getFrontCommsBulkSendModel()->fetchWebForms(array(
							'qp_export_fields' 				=> 'id,form,form_types_behaviour,description', //only load specific fields
							'qp_limit' 						=> 'all', //load all forms
							'qp_disable_hypermedia' 		=> 1, //hypermedia must be disabled for qp_limit => all to be accepted
					));

					$arr = array();
					foreach ($objForms as $objForm)
					{
						if ($objForm->id == "" || str_replace('_', '', $objForm->form_types_behaviour) != 'salesfunnel')
						{
							continue;
						}//end if

						$arr[] = $objForm;
					}//end foreach

					$objResult = new JsonModel(array(
							'error' => 0,
							'objData' => (object) $arr,
					));
					return $objResult;
					break;

				case 'load-users':
					$objUsers = $this->getFrontCommsBulkSendModel()->fetchUsers();
					$objResult = new JsonModel(array(
						'error' => 0,
						'objData' => $objUsers,
					));
					return $objResult;
					break;

				case 'load-target-group':
					$arr_request = $this->formatBulkSendRequestData($arr_post_data['data']);
					$objData = $this->getFrontCommsBulkSendModel()->runBulkSendRequestEstimate($arr_request);

					$objResult = new JsonModel(array(
						'error' => 0,
						'objData' => $objData,
					));
					return $objResult;
					break;

				case 'load-standard-fields':
					$objFields = $this->getFrontCommsBulkSendModel()->fetchStandardFields();

					$arr_fields = array();
					foreach ($objFields as $objField)
					{
						if (!is_object($objField) || $objField->get("id") == "")
						{
							continue;
						}//end if

						//only allow fields with predefined options
						switch ($objField->get('fields_types_input_type'))
						{
							case 'checkbox':
							case 'select':
							case 'radio':
								$arr_fields[] = $objField->getArrayCopy();
								break;
						}//end switch

						//@TODO set options for additional fields such as fname, sname and email...
					}//end foreach

					$objResult = new JsonModel(array(
						'error' => 0,
						'objData' => (object) $arr_fields,
					));
					return $objResult;
					break;

				case 'load-standard-field-details':
 					$objField = $this->getFrontCommsBulkSendModel()->fetchStandardFieldData($arr_params['field_id']);

					$objResult = new JsonModel(array(
							'error' => 0,
							'objData' => (object) array(
									'objField' => (object) $objField->getArrayCopy()
							)
					));
					return $objResult;
					break;

				case 'load-custom-fields':
					$objFields = $this->getFrontCommsBulkSendModel()->fetchCustomFields(array(
						'qp_export_fields' 				=> 'id,field,description,fields_types_input_type,fields_types_field_type', //only load specific fields
						'qp_limit' 						=> 'all', //load all forms
						'qp_disable_hypermedia' 		=> 1, //hypermedia must be disabled for qp_limit => all to be accepted
					));

					$arr_fields = array();
					foreach ($objFields as $objField)
					{
						if (!$objField instanceof \FrontFormAdmin\Entities\FrontFormAdminFieldEntity)
						{
							continue;
						}//end if

						if (!is_object($objField) || $objField->get("id") == "")
						{
							continue;
						}//end if

						//only allow fields with predefined options
						switch ($objField->get('fields_types_input_type'))
						{
							case 'checkbox':
							case 'select':
							case 'radio':
								$arr_fields[] = $objField->getArrayCopy();
								break;
						}//end switch
					}//end foreach

					$objResult = new JsonModel(array(
						'error' => 0,
						'objData' => (object) $arr_fields,
					));
					return $objResult;
					break;

				case 'load-custom-field-details':
					$objField = $this->getFrontCommsBulkSendModel()->fetchCustomFieldData($arr_params['field_id']);

					$objResult = new JsonModel(array(
						'error' => 0,
						'objData' => (object) array(
								'objField' => (object) $objField->getArrayCopy()
						)
					));
					return $objResult;
					break;

				case 'create-send-request':
					$arr_request = $this->formatBulkSendRequestData($arr_post_data);

					//set additional values
					$arr_request['journey_id'] = (int) $arr_post_data['journey_id'];

					//submit the data
					$objData = $this->getFrontCommsBulkSendModel()->createBulkSendRequest($arr_request);

					$objResult = new JsonModel(array(
							'error' => 0,
							'objData' => (object) $objData->getArrayCopy(),
					));
					return $objResult;
					break;

				case 'cancel-send-request':
					$objRequest = $this->getFrontCommsBulkSendModel()->fetchBulkSendRequest($arr_post_data['request_id']);

					if (!is_object($objRequest))
					{
						$objResult = new JsonModel(array(
							'error' => 1,
							'response' => 'The requested Request could not be located',
						));
						return $objResult;
					} else {
						if ($objRequest->get('id') != $arr_post_data['request_id'])
						{
							$objResult = new JsonModel(array(
									'error' => 1,
									'response' => 'The requested Request could not be located',
							));
							return $objResult;
						}//end if
					}//end if

					$objResponse = $this->getFrontCommsBulkSendModel()->requestBulkSendApprovalCancellation($objRequest->get('id'));
					$objResult = new JsonModel(array(
						'error' => 0,
						'objData' => $objResult,
					));
					return $objResult;
					break;

				case 'approve-send-request':
					$objRequest = $this->getFrontCommsBulkSendModel()->fetchBulkSendRequest($arr_post_data['request_id']);

					if (!is_object($objRequest))
					{
						$objResult = new JsonModel(array(
								'error' => 1,
								'response' => 'The requested Request could not be located',
						));
						return $objResult;
					} else {
						if ($objRequest->get('id') != $arr_post_data['request_id'])
						{
							$objResult = new JsonModel(array(
									'error' => 1,
									'response' => 'The requested Request could not be located',
							));
							return $objResult;
						}//end if
					}//end if

					$objResponse = $this->getFrontCommsBulkSendModel()->requestBulkSendRequestApproval($objRequest->get('id'));
					$objResult = new JsonModel(array(
							'error' => 0,
							'objData' => $objResult,
					));
					return $objResult;
					break;

				case 'release-send-request':
					$objRequest = $this->getFrontCommsBulkSendModel()->fetchBulkSendRequest($arr_post_data['request_id']);

					if (!is_object($objRequest))
					{
						$objResult = new JsonModel(array(
								'error' => 1,
								'response' => 'The requested Request could not be located',
						));
						return $objResult;
					} else {
						if ($objRequest->get('id') != $arr_post_data['request_id'])
						{
							$objResult = new JsonModel(array(
									'error' => 1,
									'response' => 'The requested Request could not be located',
							));
							return $objResult;
						}//end if
					}//end if

					$objResponse = $this->getFrontCommsBulkSendModel()->authorizeBulkSendRequest($objRequest->get('id'), array('confirmation_code' => time() . '-' . $objRequest->get('id')));
					$objResult = new JsonModel(array(
							'error' => 0,
							'objData' => $objResult,
					));
					return $objResult;
					break;
			}//end switch
		} catch (\Exception $e) {
			$objResult = new JsonModel(array(
					'error' => 1,
					'response' => $e->getMessage(),
			));
			return $objResult;
		}//end catch

		$objResult = new JsonModel(array(
				'error' => 1,
				'response' => 'Request type is not specified',
		));
		return $objResult;
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

	/**
	 * Take data received from views and formulate approriate array for api call
	 * @param stdClass $objData
	 * @return array
	 */
	private function formatBulkSendRequestData($objData)
	{
		$arr_data = array();

		if (isset($objData['objJourney']) && isset($objData['objJourney']['id']))
		{
			$arr_data['journey_id'] = $objData['objJourney']['id'];
		}//end if

		foreach ($objData as $key => $objSection)
		{
			switch ($key)
			{
				case 'id':
					$arr_data['id'] = $objSection;
					break;

				case 'objHasStatuses':
					if (!isset($arr_data['contact_status_equals']))
					{
						$arr_data['contact_status_equals'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['contact_status_equals'][] = $v['id'];
					}//end foreach
					break;

				case 'objNotHaveStatuses':
					if (!isset($arr_data['contact_status_not_equals']))
					{
						$arr_data['contact_status_not_equals'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['contact_status_not_equals'][] = $v['id'];
					}//end foreach
					break;

				case 'objHasWebForm':
					if (!isset($arr_data['webform_completed']))
					{
						$arr_data['webform_completed'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['webform_completed'][] = $v['id'];
					}//end foreach
					break;

				case 'objNotHaveWebForm':
					if (!isset($arr_data['webform_not_completed']))
					{
						$arr_data['webform_not_completed'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['webform_not_completed'][] = $v['id'];
					}//end foreach
					break;

				case 'objHasTracker':
					if (!isset($arr_data['tracker_exists']))
					{
						$arr_data['tracker_exists'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['tracker_exists'][] = $v['id'];
					}//end foreach
					break;

				case 'objNotHaveTracker':
					if (!isset($arr_data['tracker_not_exists']))
					{
						$arr_data['tracker_not_exists'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['tracker_not_exists'][] = $v['id'];
					}//end foreach
					break;

				case 'objHasUser':
					if (!isset($arr_data['contact_equals_user']))
					{
						$arr_data['contact_equals_user'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['contact_equals_user'][] = $v['id'];
					}//end foreach
					break;

				case 'objNotHaveUser':
					if (!isset($arr_data['contact_not_equals_user']))
					{
						$arr_data['contact_not_equals_user'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['contact_not_equals_user'][] = $v['id'];
					}//end foreach
					break;

				case 'objHasSource':
					if (!isset($arr_data['contact_source_equals']))
					{
						$arr_data['contact_source_equals'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['contact_source_equals'][] = $v['id'];
					}//end foreach
					break;

				case 'objNotHaveSource':
					if (!isset($arr_data['contact_source_not_equals']))
					{
						$arr_data['contact_source_not_equals'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['contact_source_not_equals'][] = $v['id'];
					}//end foreach
					break;

				case 'objHasReference':
					if (!isset($arr_data['contact_reference_equals']))
					{
						$arr_data['contact_reference_equals'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['contact_reference_equals'][] = $v['id'];
					}//end foreach
					break;

				case 'objNotHaveReference':
					if (!isset($arr_data['contact_reference_not_equals']))
					{
						$arr_data['contact_reference_not_equals'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						$arr_data['contact_reference_not_equals'][] = $v['id'];
					}//end foreach
					break;

				case 'objStandardFields':
					if (!isset($arr_data['standard_fields']))
					{
						$arr_data['standard_fields'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						foreach($v['values'] as $kk => $vv)
						{
							$arr_data['standard_fields'][] = array(
								'field_name' => $v['data']['field'],
								'field_id' => $v['data']['id'],
								'operator' => $vv['operator'],
								'value' => $vv['value'],
							);
						}//end foreach
					}//end foreach
					break;

				case 'objCustomFields':
					if (!isset($arr_data['custom_fields']))
					{
						$arr_data['custom_fields'] = array();
					}//end if

					foreach ($objSection as $k => $v)
					{
						foreach($v['values'] as $kk => $vv)
						{
							$arr_data['custom_fields'][] = array(
									'field_name' => $v['data']['field'],
									'field_id' => $v['data']['id'],
									'operator' => $vv['operator'],
									'value' => $vv['value'],
							);
						}//end foreach
					}//end foreach
					break;

				case 'objOptions':
					foreach($objSection as $key => $value)
					{
						if (is_numeric($value))
						{
							$value = (int) $value;
						}//end if

						$arr_data[$key] = $value;
					}//end foreach
					break;
			}//end switch
		}//end foreach

		return $arr_data;
	}//end function
}//end class