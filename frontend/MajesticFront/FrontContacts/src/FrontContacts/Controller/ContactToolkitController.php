<?php
namespace FrontContacts\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use FrontUserLogin\Models\FrontUserSession;
use FrontCore\Adapters\AbstractCoreActionController;
use Zend\Stdlib\ArrayObject;

class ContactToolkitController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Contacts Model
	 * @var \FrontContacts\Models\FrontContactsModel
	 */
	private $model_contacts;

	/**
	 * Container for the Linked Contacts Model (Viral and Standard Links)
	 * @var \FrontContacts\Models\FrontContactsLinkedContactsModel $model_linked_contacts
	 */
	private $model_linked_contacts;

	/**
	 * Container for the Front Contacts Forms Model
	 * @var \FrontContacts\Models\FrontContactsFormsModel
	 */
	private $model_contact_forms;

	/**
	 * Container for the Front Contacts Statuses Model
	 * @var \FrontContacts\Models\FrontContactsStatusesModel
	 */
	private $model_contact_statuses;

	/**
	 * Container for the Front Statuses Model
	 * @var \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private $model_statuses;

	/**
	 * Container for the Front Contact Journeys Model
	 * @var \FrontContacts\Models\FrontContactsJourneysModel
	 */
	private $model_contact_journeys;

	/**
	 * Container for the Contact Trackers Model
	 * @var \FrontSalesFunnels\Models\FrontSalesFunnelsModel
	 */
	private $model_contact_trackers;

	/**
	 * Container for the Contact User Tasks Model
	 * @var \FrontUsers\Models\FrontUsersTasksModel
	 */
	private $model_user_tasks;

	/**
	 * Container for the Forms Model
	 * @var \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private $model_forms_admin;

	/**
	 * Container for the External Forms Model
	 * @var \MajesticExternalForms\Models\MajesticExternalFormsModel
	 */
	private $model_external_forms;

	/**
	 * Container for the Journeys Amdin Mode
	 * @var \FrontCommsAdmin\Models\FrontJourneysModel
	 */
	private $model_front_journeys_admin;

	/**
	 * Container for Angular Data requests
	 * @var \Zend\Stdlib\ArrayObject
	 */
	private $objAngularRequestData;

	private function renderOutputFormat($layout = "layout/layout-toolkit-body", $arr_view_data = NULL)
	{
		$this->layout($layout);

		$contact_id = $this->params()->fromRoute("id", "");
		return $contact_id;
	}//end function

	private function loadContactData($contact_id)
	{
		return $this->getContactsModel()->fetchContact($contact_id);
	}//end function

	public function appAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['contact-list'] != true || $arr_config['angular-views-enabled']['contact-specific'] != true)
		{
			$this->flashMessenger()->addInfoMessage('The requested view is not available');
			return $this->redirect()->toRoute('front-contacts');
		}//end if

		$this->layout('layout/angular/app');
		$this->layout()->setVariable('angular_app_name', 'frontEndContactToolkitApp');
		$this->layout()->setVariable('angular_disable_main_menu', 1);

		//load contact
		$objContact = $this->loadContactData($this->params()->fromRoute("id", ""));

		//remove some fields for the contacts which might be problems
		$arr_remove_fields = array(
			'del_address', //could contain line breaks
			'postal_address', //could contian line breaks
		);
		
		foreach ($arr_remove_fields as $field)
		{
			if (isset($objContact->$field))
			{
				unset($objContact->$field);
			}//end if
		}//end if
		
		return array(
			'objContact' => $objContact,
		);
	}//end function

	public function ajaxRequestAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['contact-list'] != true || $arr_config['angular-views-enabled']['contact-specific'] != true)
		{
			return new JsonModel(array(
					'error' => 1,
					'response' => 'Requested functionality is not available',
			));
		}//end if

		if (!$this->objAngularRequestData)
		{
			$this->objAngularRequestData = new ArrayObject();
		}//end if

		$arr_params = $this->params()->fromQuery();
		if (isset($arr_params['acrq']))
		{
			$acrq = $arr_params['acrq'];
		}//end if

		$request = $this->getRequest();
		$arr_post_data = false;
		if ($request->isPost())
		{
			$arr_post_data = json_decode(file_get_contents('php://input'), true);
			if (isset($arr_post_data['acrq']))
			{
				$acrq = $arr_post_data['acrq'];
				unset($arr_post_data['acrq']);
			}//end if
			if (isset($arr_post_data['cid']))
			{
				$arr_params['cid'] = $arr_post_data['cid'];
				unset($arr_post_data['cid']);
			}//end if
		}//end if

		$this->objAngularRequestData->acrq = $acrq;
		$this->objAngularRequestData->arr_params = $arr_params;
		$this->objAngularRequestData->arr_post_data = $arr_post_data;

		//load the contact
		$this->objAngularRequestData->objContact = $this->loadContactData($arr_params['cid']);
		if (isset($this->objAngularRequestData->arr_params['cid']))
		{
			$this->objAngularRequestData->cid_encoded = $this->objAngularRequestData->arr_params['cid'];
			unset($this->objAngularRequestData->arr_params['cid']);
		}//end if

		try {
			//map request to the correct function
			switch ($acrq)
			{
				case 'comments':
				case 'list-comments':
				case 'create-comment':
				case 'delete-comment':
					$objResult = $this->ajaxProcessCommentsRequest();
					break;

				case 'journeys':
				case 'list-journeys-available':
				case 'list-contact-journeys':
				case 'contact-journey-history':
				case 'stop-contact-journey':
				case 'start-contact-journey':
				case 'restart-contact-journey':
					$objResult = $this->ajaxProcessJourneysRequest();
					break;

				case 'forms-completed':
				case 'list-forms-completed':
				case 'list-web-forms':
				case 'list-viral-forms':
					$objResult = $this->ajaxProcessFormsCompletedRequest();
					break;

				case 'statuses':
				case 'list-contact-statuses':
				case 'list-available-statuses':
				case 'update-contact-status':
					$objResult = $this->ajaxProcessStatusesRequest();
					break;

				case 'list-contact-tasks':
				case 'load-task-admin-form':
				case 'create-contact-task':
				case 'complete-user-task':
				case 'delete-user-task':
					$objResult = $this->ajaxProcessTasksRequest();
					break;

				case 'trackers':
				case 'list-contact-trackers':
				case 'create-contact-tracker':
				case 'list-tracker-forms':
				case 'load-tracker-form':
					$objResult = $this->ajaxProcessTrackersRequest();
					break;

				case 'unsubscribe-contact':
					//unsubscribe the contact
					$objData = $this->getContactsModel()->unsubscribeContact($this->objAngularRequestData->objContact);

					$objResult = new JsonModel(array(
							'objData' => (object) $objData,
					));
					break;

				case 'list-linked-contacts':
					$objData = $this->getLinkedContactsModel()->fetchLinkedContacts($this->objAngularRequestData->objContact);

					$objResult = new JsonModel(array(
							'objData' => (object) $objData,
					));
					break;

				case 'list-linked-to-contacts':
					$objData = $this->getLinkedContactsModel()->fetchLinkedToContacts($this->objAngularRequestData->objContact);

					$objResult = new JsonModel(array(
							'objData' => (object) $objData,
					));
					break;
			}//end switch

			if (!$objResult instanceof \Zend\View\Model\JsonModel)
			{
				$objResult = new JsonModel(array(
						'error' => 1,
						'response' => 'Data could not be loaded, an unknown problem has occurred',
				));
			}//end if

			return $objResult;
		} catch (\Exception $e) {
			$objResult = new JsonModel(array(
					'error' => 1,
					'response' => 'Requested operation failed with message: ' . $this->frontControllerErrorHelper()->formatErrors($e),
					'raw_response' => $e->getMessage()
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
	 * Helper function for angular
	 */
	private function ajaxProcessCommentsRequest()
	{
		switch ($this->objAngularRequestData->acrq)
		{
			case 'list-comments':
				$objComments = $this->getContactsModel()->fetchContactComments($this->objAngularRequestData->objContact->get('id'), $this->objAngularRequestData->arr_params);
				$arr_comments = array();
				//fix dates
				foreach ($objComments as $k => $objComment)
				{
					if (isset($objComment->tstamp))
					{
						//format tstamp to date
						$date = $this->formatUserDate(array("date" => $objComment->tstamp, "options" => array(
								"output_format" => "d M Y H:i",
						)));
						if (!$date)
						{
							$date = '';
						}//end if

						$objComment->tstamp = $date;
						$objComment->datetime_created = $date;
					}//end if

					$arr_comments[$k] = $objComment;
				}//end if

				$objComments = (object) $arr_comments;
				$objResult = new JsonModel(array(
					'objData' => $objComments,
				));
				break;

			case 'create-comment':
				$objR = $this->getContactsModel()->createContactComment($this->objAngularRequestData->objContact->get('id'), (array) $this->objAngularRequestData->arr_post_data);

				$objResult = new JsonModel(array(
						'objData' => (object) array('result' => 'Comment has been created'),
				));
				break;

			case 'delete-comment':
				$objR = $this->getContactsModel()->deleteContactComment($this->objAngularRequestData->objContact->get('id'), $this->objAngularRequestData->arr_params['id']);

				$objResult = new JsonModel(array(
						'objData' => (object) array('result' => 'Comment has been deleted'),
				));
				break;
		}//end switch

		return $objResult;
	}//end function

	/**
	 * Helper function for angular
	 */
	private function ajaxProcessFormsCompletedRequest()
	{
		switch ($this->objAngularRequestData->acrq)
		{
			case 'list-forms-completed':
				//load forms
		 		$objContactForms = $this->getContactFormsModel()->fetchContactFormsCompleted($this->objAngularRequestData->objContact->get('id'), NULL, $this->objAngularRequestData->arr_params);
		 		$objHypermedia = $objContactForms->hypermedia;
		 		unset($objContactForms->hypermedia);

		 		$arr_forms = array();
				foreach ($objContactForms as $objForm)
				{
					//format tstamp to date
					$date = $this->formatUserDate(array("date" =>  $objForm->get('tstamp'), "options" => array(
							"output_format" => "d M Y H:i",
					)));
					$objForm->set('tstamp', $date);
					$arr_forms[] = $objForm->getArrayCopy();
				}//end foreach
				$arr_forms['hypermedia'] = $objHypermedia;

		 		$objResult = new JsonModel(array(
		 				'objData' => (object) $arr_forms,
		 		));
		 		break;

			case 'list-web-forms':
		 		//load web forms
				$objWebForms = $this->getFrontFormAdminModel()->fetchForms(array(
						'forms_type_id' => 1,
						'forms_active' => 1,
				));

				$objResult = new JsonModel(array(
						'objData' => $objWebForms,
				));
				break;

			case 'list-viral-forms':
				//load viral forms
				$objViralForms = $this->getFrontFormAdminModel()->fetchForms(array(
						'forms_type_id' => 2,
						'forms_active' => 1,
				));

				$objResult = new JsonModel(array(
						'objData' => $objViralForms,
				));
				break;
		}//end switch

		return $objResult;
	}//end function

	/**
	 * Helper function for angular
	 */
	private function ajaxProcessJourneysRequest()
	{
		switch ($this->objAngularRequestData->acrq)
		{
			case 'list-journeys-available':
				$objJourneys = $this->getFrontJourneysModel()->fetchJourneys(array());
				$objResult = new JsonModel(array(
						'objData' => (object) array(
							'objJourneys' => $objJourneys,
						),
				));
				return $objResult;
				break;

			case 'list-contact-journeys':
				//load journeys
				$objContactJourneys = $this->getFrontContactJourneysModel()->fetchContactJourneysStarted($this->objAngularRequestData->objContact->get('id'), $this->objAngularRequestData->arr_params);
				$arr_journeys = array();
				foreach ($objContactJourneys as $objJourney)
				{
					if ($objJourney instanceof \FrontContacts\Entities\FrontContactsJourneyEntity)
					{
						//format tstamp to date
						$date = $this->formatUserDate(array("date" =>  $objJourney->get('tstamp'), "options" => array(
								"output_format" => "d M Y H:i",
						)));
						$objJourney->set('tstamp', $date);

						$date = $this->formatUserDate(array("date" =>  $objJourney->get('datetime_last'), "options" => array(
								"output_format" => "d M Y H:i",
						)));
						if (!$date)
						{
							$date = 'NA';
						}//end if

						$objJourney->set('datetime_last', $date);

						$arr_journeys[] = $objJourney->getArrayCopy();
					}//end if
				}//end if

				$objJourneys = (object) $arr_journeys;
				$objJourneys->hypermedia = $objContactJourneys->hypermedia;

				$objResult = new JsonModel(array(
						'objData' => $objJourneys,
				));
				break;

			case 'contact-journey-history':
				//load journey details
				$objJourney = $this->getFrontJourneysModel()->fetchJourney($this->objAngularRequestData->arr_params['journey_id']);
				$objHistoryData = $this->getContactsModel()->fetchContactCommHistory($this->objAngularRequestData->objContact->get('id'), $this->objAngularRequestData->arr_params);
				unset($objHistoryData->hypermedia);

				$objResult = new JsonModel(array(
						'objData' => (object) array(
									'objJourney' => (object) $objJourney->getArrayCopy(),
									'objHistoryData' => $objHistoryData
								),
				));
				break;

			case 'contact-journey-episode-history':
				$objHistoryData = $this->getContactsModel()->fetchContactCommHistory($this->objAngularRequestData->objContact->get('id'), $this->objAngularRequestData->arr_params);

				$objResult = new JsonModel(array(
						'objData' => $objHistoryData,
				));
				break;

			case 'start-contact-journey':
				$objJourneyStatus = $this->getFrontContactJourneysModel()->startContactJourney($this->objAngularRequestData->objContact->get('id'), $this->objAngularRequestData->arr_params['journey_id']);

				$objResult = new JsonModel(array(
						'objData' => $objJourneyStatus->data,
				));
				break;

			case 'restart-contact-journey':
				try {
					$objResult = $this->getFrontContactJourneysModel()->restartContactJourney($this->objAngularRequestData->objContact->get('id'), $this->objAngularRequestData->arr_params['reg_comm_id']);
					if ($objResult->HTTP_RESPONSE_CODE != 200)
					{
						$result = FALSE;
						$error = $objResult->HTTP_RESPONSE_MESSAGE;
					} else {
						$result = TRUE;
						$error = "";
					}//end if

					$arr_return = array(
							"result" => $result,
							"error" => $error,
					);
					return new JsonModel($arr_return);
				} catch (\Exception $e) {
					//extract error
					$arr_t = explode("||", $e->getMessage());
					$json = array_pop($arr_t);
					$objResult = json_decode($json);
					if (is_object($objResult))
					{
						switch($objResult->HTTP_RESPONSE_CODE)
						{
							case 999:
							default:
								$arr_tt = explode(":", $objResult->HTTP_RESPONSE_MESSAGE);
								$message = array_pop($arr_tt);

								//format data for return
								$arr_return = array(
										"result" => FALSE,
										"error" => $message,
								);
								break;
						}//end switch
					} else {
						//format data for return
						$arr_return = array(
								"result" => FALSE,
								"error" => "An unknown error has occured (" . $e->getMessage() . ")",
						);
					}//end if
				}//end catch

				return new JsonModel($arr_return);
				break;

			case 'stop-contact-journey':
				$objJourneyStatus = $this->getFrontContactJourneysModel()->stopContactJourney($this->objAngularRequestData->objContact->get('id'), $this->objAngularRequestData->arr_params['reg_comm_id']);

				$objResult = new JsonModel(array(
						'objData' => $objJourneyStatus->data,
				));
				break;
		}//end switch

		return $objResult;
	}//end function

	/**
	 * Helper function for angular
	 */
	private function ajaxProcessStatusesRequest()
	{
		switch ($this->objAngularRequestData->acrq)
		{
			case 'list-contact-statuses':
				//load status history
				$objContactStatusData = $this->getContactStatusesModel()->fetchContactStatusHistory($this->objAngularRequestData->objContact->get('id'), $this->objAngularRequestData->arr_params);
				$arr_statuses = array();
				foreach ($objContactStatusData as $objStatus)
				{
					if ($objStatus instanceof \FrontContacts\Entities\FrontContactsContactStatusEntity)
					{
						//format tstamp to date
						$date = $this->formatUserDate(array("date" =>  $objStatus->get('tstamp'), "options" => array(
								"output_format" => "d M Y H:i",
						)));
						$objStatus->set('tstamp', $date);

						//set behaviour label
						$objStatus->set('behaviour_label', ucwords(trim(str_replace('_', ' ', $objStatus->get('behaviour')))));
						$arr_statuses[] = $objStatus->getArrayCopy();
					}//end if
				}//end if

				$objData = (object) $arr_statuses;
				$objData->hypermedia = $objContactStatusData->hypermedia;

				$objResult = new JsonModel(array(
						'objData' => $objData,
				));
				break;

			case 'list-available-statuses':
				//load contact statuses to change current status for contact
				$objStatuses = $this->getFrontStatusesModel()->fetchContactStatuses();

				$objResult = new JsonModel(array(
						'objData' => $objStatuses,
				));
				break;

			case 'update-contact-status':
				$this->objAngularRequestData->arr_post_data['behaviour'] = '__manual';
				$objResult = $this->getContactStatusesModel()->updateContactStatus($this->objAngularRequestData->objContact->get('id'), (array) $this->objAngularRequestData->arr_post_data);

				if ($objResult->HTTP_RESPONSE_CODE != 200)
				{
					$objResult = new JsonModel(array(
							'objData' => (object) array('error' => 1, 'response' => 'Status has not been updated', 'message' => $objResult->HTTP_RESPONSE_MESSAGE),
					));
				} else {
					$objResult = new JsonModel(array(
							'objData' => (object) array('result' => 'Status has been updated'),
					));
				}//end if
				break;
		}//end switch

		return $objResult;
	}//end function

	/**
	 * Helper function for angular
	 */
	private function ajaxProcessTrackersRequest()
	{
		switch ($this->objAngularRequestData->acrq)
		{
			case 'list-contact-trackers':
				$objTrackers = $this->getContactFormsModel()->fetchContactSalesFunnelsCompleted($this->objAngularRequestData->objContact->get('id'), $this->objAngularRequestData->arr_params);
				$arr_trackers = array();
				foreach ($objTrackers as $objTracker)
				{
					if ($objTracker instanceof \FrontContacts\Entities\FrontContactsFormsEntity)
					{
						//set dates
						//format tstamp to date
						$date = $this->formatUserDate(array("date" =>  $objTracker->get('tstamp'), "options" => array(
								"output_format" => "d M Y H:i",
						)));
						$objTracker->set('tstamp', $date);

						$date = $this->formatUserDate(array("date" =>  $objTracker->get('tstamp_updated'), "options" => array(
								"output_format" => "d M Y H:i",
						)));
						if (!$date)
						{
							$date = '';
						}//end if

						$objTracker->set('tstamp_updated', $date);

						$arr_trackers[] = $objTracker->getArrayCopy();
					}//end if
				}//end foreach

				$arr_trackers['hypermedia'] = $objTrackers->hypermedia;
				$objTrackers = (object) $arr_trackers;

				$objResult = new JsonModel(array(
						'objData' => $objTrackers,
				));
				break;

			case 'list-tracker-forms':
				//load tracker forms
				$objForms = $this->getFrontFormAdminModel()->fetchForms(array(
						'forms_type_id' => 3,
						'forms_active' => 1,
				));

				$objResult = new JsonModel(array(
						'objData' => $objForms,
				));
				break;

			case 'load-tracker-form':
				//load tracker form
				$arr_form = $this->getExternalFormsModel()->loadForm($this->objAngularRequestData->arr_params['fid'], NULL, array("behaviour" => "__sales_funnel", "cache_clear" => 1));
				$form = $arr_form["objForm"];

				$arr_tracker = $this->renderSystemAngularFormHelper($form, false);
				$objResult = new JsonModel(array(
						'objData' => $arr_tracker,
						'objTrackerData' => $arr_form['objFormRawData'],
				));
				break;

			case 'create-contact-tracker':
				//load tracker form
				$arr_form = $this->getExternalFormsModel()->loadForm($this->objAngularRequestData->arr_post_data['fid'], NULL, array("behaviour" => "__sales_funnel", "cache_clear" => 1));
				$form = $arr_form["objForm"];

				if (is_object($form))
				{
					//validate the data
					$form->setData($this->objAngularRequestData->arr_post_data);
					if ($form->isValid())
					{
						$arr_tracker_data = (array) $form->getData();
						//set required additional data
						$arr_tracker_data['fk_form_id'] = $this->objAngularRequestData->arr_post_data['fid'];

						//submit the data
						$objTrackerData = $this->getContactTrackersModel()->createSalesFunnel($this->objAngularRequestData->objContact, $arr_tracker_data);

						$objResult = new JsonModel(array(
							'objData' => $objTrackerData,
						));
					} else {
						//form is invalid
						$objResult = new JsonModel(array(
								'error' => 1,
								'response' => 'Data could not be validated',
								'form_errors' => $form->getMessages(),
						));
						return $objResult;
					}//end if
				} else {
					$objResult = new JsonModel(array(
							'error' => 1,
							'response' => 'The requested tracker could not be located, check the Form ID',
					));
				}//end if
				break;
		}//end switch

		return $objResult;
	}//end function

	/**
	 * Helper function for angular
	 */
	private function ajaxProcessTasksRequest()
	{
		try {
			switch ($this->objAngularRequestData->acrq)
			{
				case 'load-task-admin-form':
					$form = $this->getFrontUserTasksModel()->getUserTasksForm();

					$arr_form = $this->renderSystemAngularFormHelper($form, false);
					$objResult = new JsonModel(array(
							'objData' => $arr_form,
					));
					break;

				case 'list-contact-tasks':
					//load tasks belonging to contact
					$arr_params = array("user_tasks_reg_id" => $this->objAngularRequestData->cid_encoded);

					if (isset($this->objAngularRequestData->arr_params['filter_loggedin_user_items']) && $this->objAngularRequestData->arr_params['filter_loggedin_user_items'] == 1)
					{
						$objUser = FrontUserSession::isLoggedIn();
						$arr_params['user_tasks_user_id'] = $objUser->id;
					}//end if

					$objUserTasks = $this->getFrontUserTasksModel()->fetchUserTasks($arr_params);
					$arr_tasks = array();
					foreach($objUserTasks as $objTask)
					{
						if (method_exists($objTask, 'get') && $objTask->get('id') > 0)
						{
							$arr_task = $objTask->getArrayCopy();

							//format dates
							$arr_task['datetime_created_pretty'] = date('d M Y', strtotime($arr_task['datetime_created']));
							$arr_task['datetime_reminder_pretty'] = date('d M Y', strtotime($arr_task['datetime_reminder']));
							if ($arr_task['datetime_complete'] != '0000-00-00 00:00:00')
							{
								$arr_task['datetime_complete_pretty'] = date('d M Y', strtotime($arr_task['datetime_complete']));
							} else {
								$arr_task['datetime_complete_pretty'] = 'NA';
							}//end if

							$arr_tasks[] = $arr_task;
						}//end if
					}//end foreach

					$objResult = new JsonModel(array(
						'objData' => (object) $arr_tasks,
					));
					break;

				case 'create-contact-task':
					try {
						$form = $this->getFrontUserTasksModel()->getUserTasksForm();
						$form->setData($this->objAngularRequestData->arr_post_data);
						$reg_id = $this->objAngularRequestData->objContact->get('id');

						if ($form->isValid())
						{
							$arr_data = $form->getData();

							if ($arr_data['datetime_reminder'] != '')
							{
								$t = strtotime(trim($arr_data['datetime_reminder']));
								$arr_data['datetime_reminder'] = date('d M Y H:i:s', $t);
							}//end if

							if ($arr_data['date_email_reminder'] != '')
							{
								$t = strtotime(trim($arr_data['date_email_reminder']));
								$arr_data['date_email_reminder'] = date('d M Y', $t);
							}//end if

							if ($arr_data['notify_user'] == '')
							{
								$arr_data['notify_user'] = 0;
							}//end if

							$arr_data["reg_id"] = $reg_id;

							//create the user task
							$objUserTask = $this->getFrontUserTasksModel()->createUserTask($arr_data);

							$objResult = new JsonModel(array(
								'objData' => $objUserTask->getArrayCopy(),
							));
						} else {
							$objResult = new JsonModel(array(
								'error' => 1,
								'response' => 'Form could not be validated',
								'form_errors' => (array) $form->getMessages(),
							));
						}//end if
					} catch (\Exception $e) {
						$objResult = new JsonModel(array(
							'error' => 1,
							'response' => $this->frontControllerErrorHelper()->formatErrors($e)
						));
					}//end catch
					break;

				case 'complete-user-task':
					$objTask = $this->getFrontUserTasksModel()->fetchUserTask($this->objAngularRequestData->arr_post_data['id']);
					if (!$objTask)
					{
						$objResult = new JsonModel(array(
							'error' => 1,
							'response' => 'Requested Item could not be located',
						));

						return $objResult;
					}//end if

					$arr_data = $objTask->getArrayCopy();
					if ($arr_data['datetime_reminder'] != '')
					{
						$t = strtotime(trim($arr_data['datetime_reminder']));
						$arr_data['datetime_reminder'] = date('d M Y H:i:s', $t);
					}//end if

					if ($arr_data['date_email_reminder'] != '' && $arr_data['date_email_reminder'] != '0000-00-00')
					{
						$t = strtotime(trim($arr_data['date_email_reminder']));
						$arr_data['date_email_reminder'] = date('d M Y', $t);
					}//end if
					$objTask->set($arr_data);

					$objResponse = $this->getFrontUserTasksModel()->completeUserTask($objTask);
					$objResult = new JsonModel(array(
						'objData' => (object) $objResponse->getArrayCopy(),
					));
					break;

				case 'delete-user-task':
					$objTask = $this->getFrontUserTasksModel()->fetchUserTask($this->objAngularRequestData->arr_post_data['id']);
					if (!$objTask)
					{
						$objResult = new JsonModel(array(
								'error' => 1,
								'response' => 'Requested Item could not be located',
						));

						return $objResult;
					}//end if

					$objResponse = $this->getFrontUserTasksModel()->deleteUserTask($objTask->get('id'));
					$objResult = new JsonModel(array(
							'objData' => (object) $objResponse->getArrayCopy(),
					));
					break;
			}//end switch
		} catch (\Exception $e) {
			$objResult = new JsonModel(array(
				'error' => 111,
				'response' => $this->frontControllerErrorHelper()->formatErrors($e)
			));
		}//end catch

		return $objResult;
	}//end function



	public function contactCommentsAction()
	{
		$contact_id = $this->renderOutputFormat();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			//load comment
			$arr_data = (array) $request->getPost();

			//create the comment
			try {
				$objResult = $this->getContactsModel()->createContactComment($contact_id, $arr_data);
				echo "true";
				exit;
			} catch (\Exception $e) {
				echo "Comment could not be created. " . $this->frontControllerErrorHelper()->formatErrors($e);
			}//end catch
		}//end if

		try {
			$objComments = $this->getContactsModel()->fetchContactComments($contact_id);
		} catch (\Exception $e) {
			echo $this->frontControllerErrorHelper()->formatErrors($e);
		}//end catch

		return array(
			"objComments" => $objComments,
			"contact_id" => $contact_id,
		);
	}//end function

	public function contactFormsCompletedAction()
	{
		$contact_id = $this->renderOutputFormat();

		try {
			//load forms
	 		$objContactForms = $this->getContactFormsModel()->fetchContactFormsCompleted($contact_id);

	 		//load web forms
			$objWebForms = $this->getFrontFormAdminModel()->fetchForms(array(
					'forms_type_id' => 1,
					'forms_active' => 1,
			));
		} catch (\Exception $e) {
			echo $this->frontControllerErrorHelper()->formatErrors($e);
		}//end catch

		return array(
			"contact_id" => $contact_id,
			"objContactForms" => $objContactForms,
			"objWebForms" => $objWebForms,
		);
	}//end function

	public function contactJourneysAction()
	{
		$contact_id = $this->renderOutputFormat();

		try {
			//load journeys
	 		$objContactJourneys = $this->getFrontContactJourneysModel()->fetchContactJourneysStarted($contact_id);
		} catch (\Exception $e) {
			echo $this->frontControllerErrorHelper()->formatErrors($e);
		}//end catch

		return array(
			"contact_id" 			=> $contact_id,
			"objContactJourneys" 	=> $objContactJourneys,
		);
	}//end function

	public function contactStatusHistoryAction()
	{
		$contact_id = $this->renderOutputFormat();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			//load data
			$arr_data = (array) $request->getPost();

			//update the status
			try {
				$objResult = $this->getContactStatusesModel()->updateContactStatus($contact_id, $arr_data);

				if ($objResult->HTTP_RESPONSE_CODE != 200)
				{
					echo "Status could not be updated. " . $objResult->HTTP_RESPONSE_MESSAGE;
					exit;
				}//end if

				echo "true";
			} catch (\Exception $e) {
				echo "Status could not be updated. " . $this->frontControllerErrorHelper()->formatErrors($e);
			}//end catch

			exit;
		}//end if

		try {
			//load contact statuses to change current status for contact
			$objStatuses = $this->getFrontStatusesModel()->fetchContactStatuses();

			//load status history
			$objContactStatusData = $this->getContactStatusesModel()->fetchContactStatusHistory($contact_id);
		} catch (\Exception $e) {
			echo $this->frontControllerErrorHelper()->formatErrors($e);
		}//end catch

		return array(
			"contact_id" 				=> $contact_id,
			"objContactStatusData" 		=> $objContactStatusData,
			"objStatuses" 				=> $objStatuses,
		);
	}//end function

	public function contactUserTasksAction()
	{
		$contact_id = $this->renderOutputFormat();

		$form = $this->getFrontUserTasksModel()->getUserTasksForm();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				$arr_data = $form->getData();
				$arr_data["reg_id"] = $contact_id;

				//create the user task
				$objUserTask = $this->getFrontUserTasksModel()->createUserTask($arr_data);
			} else {
				echo 'Task could not be created, form validation failed';
			}//end if
		}//end if

		try {
			//load tasks belonging to contact
			$objUserTasks = $this->getFrontUserTasksModel()->fetchUserTasks(array("user_tasks_reg_id" => $contact_id));
		} catch (\Exception $e) {
			echo $this->frontControllerErrorHelper()->formatErrors($e);
		}//end catch

		return array(
			"objUserTasks" => $objUserTasks,
			"contact_id" => $contact_id,
			"form" => $form,
		);
	}//end function

	public function contactSalesFunnelsAction()
	{
		$contact_id = $this->renderOutputFormat();

		//load contact sales funnels
		try {
			$objSalesFunnels = $this->getContactFormsModel()->fetchContactSalesFunnelsCompleted($contact_id);
		} catch (\Exception $e) {
			echo $this->frontControllerErrorHelper()->formatErrors($e);
		}//end catch

		return array(
			"objSalesFunnels" => $objSalesFunnels,
			"contact_id" => $contact_id,
		);
	}//end function

	/**
	 * Create an instance of the Contacts Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsModel
	 */
	private function getContactsModel()
	{
		if (!$this->model_contacts)
		{
			$this->model_contacts = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsModel");
		}//end if

		return $this->model_contacts;
	}//end function

	/**
	 * Create an instance of the Front Contact Forms Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsFormsModel
	 */
	private function getContactFormsModel()
	{
		if (!$this->model_contact_forms)
		{
			$this->model_contact_forms = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsFormsModel");
		}//end if

		return $this->model_contact_forms;
	}//end function

	/**
	 * Create an instance of the Front Contact Statuses Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsStatusesModel
	 */
	private function getContactStatusesModel()
	{
		if (!$this->model_contact_statuses)
		{
			$this->model_contact_statuses = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsStatusesModel");
		}//end if

		return $this->model_contact_statuses;
	}//end function

	/**
	 * Create an instance of the Front Statuses Model using the Service Manager
	 * @return \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private function getFrontStatusesModel()
	{
		if (!$this->model_statuses)
		{
			$this->model_statuses = $this->getServiceLocator()->get("FrontStatuses\Models\FrontContactStatusesModel");
		}//end if

		return $this->model_statuses;
	}//end function

	/**
	 * Create an instance of the Front Contact Journeys Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsJourneysModel
	 */
	private function getFrontContactJourneysModel()
	{
		if (!$this->model_contact_journeys)
		{
			$this->model_contact_journeys = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsJourneysModel");
		}//end if

		return $this->model_contact_journeys;
	}//end function

	/**
	 * Create an instance of the Front User Tasks Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUsersTasksModel
	 */
	private function getFrontUserTasksModel()
	{
		if (!$this->model_user_tasks)
		{
			$this->model_user_tasks = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersTasksModel");
		}//end if

		return $this->model_user_tasks;
	}//end function

	/**
	 * Create an instance of the Front Form Admin Model using the Service Manager
	 * @return \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private function getFrontFormAdminModel()
	{
		if (!$this->model_forms_admin)
		{
			$this->model_forms_admin = $this->getServiceLocator()->get('FrontFormAdmin\Models\FrontFormAdminModel');
		}//end if

		return $this->model_forms_admin;
	}//end function

	/**
	 * Create an instance of the External Forms Model using the Service Manager
	 * @return \MajesticExternalForms\Models\MajesticExternalFormsModel
	 */
	private function getExternalFormsModel()
	{
		if (!$this->model_external_forms)
		{
			$this->model_external_forms = $this->getServiceLocator()->get("MajesticExternalForms\Models\MajesticExternalFormsModel");
		}//end if

		return $this->model_external_forms;
	}//end function

	/**
	 * Create an instance of the Journeys Admin Model using the Service Manager
	 * @return \FrontCommsAdmin\Models\FrontJourneysModel
	 */
	private function getFrontJourneysModel()
	{
		if (!$this->model_front_journeys_admin)
		{
			$this->model_front_journeys_admin = $this->getServiceLocator()->get('FrontCommsAdmin\Models\FrontJourneysModel');
		}//end if

		return $this->model_front_journeys_admin;
	}//end function

	/**
	 * Create an instance of the Linked Contacts Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsLinkedContactsModel
	 */
	private function getLinkedContactsModel()
	{
		if (!$this->model_linked_contacts)
		{
			$this->model_linked_contacts = $this->getServiceLocator()->get('FrontContacts\Models\FrontContactsLinkedContactsModel');
		}//end if

		return $this->model_linked_contacts;
	}//end function

	/**
	 * Create an instance of the Contact Trackers Model using the Service Manager
	 * @return \FrontSalesFunnels\Models\FrontSalesFunnelsModel
	 */
	private function getContactTrackersModel()
	{
		if (!$this->model_contact_trackers)
		{
			$this->model_contact_trackers = $this->getServiceLocator()->get('FrontSalesFunnels\Models\FrontSalesFunnelsModel');
		}//end if

		return $this->model_contact_trackers;
	}//end function
}//end class