<?php
namespace FrontContacts\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use FrontUserLogin\Models\FrontUserSession;
use FrontCore\Adapters\AbstractCoreActionController;
use Zend\Stdlib\ArrayObject;

class IndexController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Contacts Model
	 * @var \FrontContacts\Models\FrontContactsModel
	 */
	private $model_contacts;

	/**
	 * Container for the Forms admin model
	 * @var \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private $model_forms_admin;

	/**
	 * Container for the Front Users Model
	 * @var \FrontUsers\Models\FrontUsersModel
	 */
	private $model_users;

	/**
	 * Container for the Contact Status Model
	 * @var \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private $model_contact_statuses;

	/**
	 * Container for the Front Contact System Fields Model
	 * @var \FrontContacts\Models\FrontContactsSystemFieldsModel
	 */
	private $model_front_contact_system_fields;

	/**
	 * Container for Angular Data requests
	 * @var \Zend\Stdlib\ArrayObject
	 */
	private $objAngularRequestData;

    public function indexAction()
    {
    	//extract form id from url
    	$form_id = $this->params()->fromQuery("fid", "");

    	//select contact profile form to use
    	if ($form_id == "")
    	{
    		//check if form id is set in user session
    		$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    		if (isset($objUserStorage->readUserNativePreferences()->cpp_layout_id) && is_numeric($objUserStorage->readUserNativePreferences()->cpp_layout_id))
    		{
    			//load the specified form's fields
    			$form_id = $objUserStorage->readUserNativePreferences()->cpp_layout_id;
    			$objForm = $this->getFormAdminModel()->getForm($form_id);
    		} else {
    			$form_id = "none";
    		}//end if
    	} else {
    		if ($form_id != "none" && is_numeric($form_id))
    		{
    			$objForm = $this->getFormAdminModel()->getForm($form_id);
    		}//end if
    	}//end if

    	//extract search params from query
    	$arr_params = $this->params()->fromQuery();

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$arr_params = array_merge($arr_params, (array) $request->getPost());
    	}//end foreach

    	//load contacts
		$objContacts = $this->getContactsModel()->fetchContacts($arr_params);

		if (!isset($objForm))
		{
			$objForm = FALSE;
		}//end if

		return array(
					"objForm" => $objForm,
					"objContacts" => $objContacts,
					"arr_params" => $arr_params,
				);
    }//end function

    public function indexStreamAction()
    {
    	//extract search params from query
		if ($this->params()->fromQuery('load-data') == 1)
		{
			$arr_data = $this->getContactsModel()->fetchContactsStream();
			if (!is_file($arr_data['source_data_path']))
			{
				echo json_encode(array('error' => 1, 'response' => 'Data does not exist'), JSON_FORCE_OBJECT);
				exit;
			}//end if

			//read data from file
			$start = $this->params()->fromQuery('pos');
			$file = fopen($arr_data['source_data_path'], 'r');
			$arr_headers = array();
			$data_size = 500;

			$total = 0;
			while (($arr = fgetcsv($file)) !== FALSE)
			{
				$total++;
			}//end while
			fclose($file);

			if ($total == 1)
			{
				$arr_data = $this->getContactsModel()->fetchContactsStream('delete');
				echo json_encode(array(
						'data' => array(),
						'start' => $start,
				));
				exit;
			}//end if

			if ($start >= $total && $start > 0)
			//if ($start > 0)
			{
				echo json_encode(array('error' => 1, 'response' => 'No more data available'), JSON_FORCE_OBJECT);
				exit;
			}//end if

			$file = fopen($arr_data['source_data_path'], 'r');
			$counter = 0;
			$arr_sdata = array();
			while (($arr_csv_data = fgetcsv($file)) !== FALSE)
			{
				if ($counter == 0)
				{
					foreach ($arr_csv_data as $header)
					{
						$arr_headers[] = $header;
					}//end foreach
					$counter++;
					continue;
				}//end if

				if ($counter > $start)
				{
					$arr_t = array();
					foreach ($arr_headers as $k => $field)
					{
						$arr_t[$field] = utf8_encode($arr_csv_data[$k]);
					}//end  foreach
					//amend some values
					$view_url = "<a href=\"" . $this->url()->fromRoute("front-contacts", array("action" => "view-contact", "id" => $arr_t['reg_id'])) . "\" title=\"View Contact\" data-toggle=\"tooltip\" target=\"_blank\">" . ICON_SMALL_PROFILE_HTML . "</a>";
					$edit_url = "<a href=\"" . $this->url()->fromRoute("front-contacts", array("action" => "edit-contact", "id" => $arr_t['reg_id'])) . "\" title=\"Edit Contact\" data-toggle=\"tooltip\" target=\"_blank\">" . ICON_SMALL_MODIFY_HTML . "</a>";
					$comms_url = "<a href=\"" . $this->url()->fromRoute("front-contacts", array("action" => "view-contact", "id" => $arr_t['reg_id'])) . "\" class=\"contact_comms\" data-contact-id=\"" . $arr_t['reg_id'] . "\" target=\"_blank\" title=\"Send a Journey\" data-toggle=\"tooltip\">" . ICON_SMALL_COMMS_HTML . "</a>";

					$arr_t['reg_id'] = "<a href=\"" . $this->url()->fromRoute("front-contacts", array("action" => "edit-contact", "id" => $arr_t['reg_id'])) . "\" title=\"Edit Contact\" target=\"_blank\"><span style=\"background-color: " . $arr_t['registration_status_colour'] . "\" class=\"label label-info\" data-toggle=\"tooltip\" title=\"" . $arr_t['registration_status_status'] . "\">" . $arr_t['reg_id'] . "</span></a>";
					$arr_t['urls'] = $view_url . '&nbsp;' . $edit_url . '&nbsp;' . $comms_url;
					$arr_sdata[] = $arr_t;
				}//end if

				$counter++;
				if ($counter == $start + $data_size)
				{
					//stop, time to return data
					break;
				}//end if
			}//end while
			fclose($file);

			echo json_encode(array(
					'data' => $arr_sdata,
					'start' => $start + $data_size,
					'total' => $total,
 			));
			exit;
		}//end if

    	//load contacts
    	$arr_data = $this->getContactsModel()->fetchContactsStream();
    	$objContacts = (object) array();

    	if (!isset($objForm))
    	{
    		$objForm = FALSE;
    	}//end if

    	return array(
    			"objForm" => $objForm,
    			"objContacts" => $objContacts,
    			"arr_params" => $arr_params,
    	);
    }//end function

    public function appAction()
    {
    	$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
    	if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['contact-list'] != true)
    	{
    		$this->flashMessenger()->addInfoMessage('The requested view is not available');
    		return $this->redirect()->toRoute('front-contacts');
    	}//end if

    	$this->layout('layout/angular/app');

    	//load contacts
		$objContacts = $this->getContactsModel()->fetchContacts(array('qp_limit' => 20));

    	return array(
    			'objContacts' => $objContacts,
    	);
    }//end function

    public function ajaxRequestAction()
    {
    	$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
    	if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['contact-list'] != true)
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

    	if (isset($this->objAngularRequestData->arr_params['cid']))
    	{
    		$this->objAngularRequestData->cid = $this->objAngularRequestData->arr_params['cid'];
    		unset($this->objAngularRequestData->arr_params['cid']);
    	}//end if

    	try {
    		//map request to the correct function
    		switch ($acrq)
    		{
    			case 'list-contacts':
					$arr_where = array();
					foreach ($arr_params as $k => $v)
					{
						if (substr($k, 0, strlen('regtbl_')) == 'regtbl_')
						{
							$arr_where[$k] = $v;
						}//end if
					}//end foreach

					if (isset($arr_params['keyword']) && $arr_params['keyword'] != '')
					{
						$arr_where['keyword'] = $arr_params['keyword'];
					}//end if

					if (isset($arr_params['qp_limit']) && is_numeric($arr_params['qp_limit']))
					{
						$arr_where['qp_limit'] = (int) $arr_params['qp_limit'];
					}//end if

					if (isset($arr_params['qp_start']) && is_numeric($arr_params['qp_start']))
					{
						$arr_where['qp_start'] = (int) $arr_params['qp_start'];
					}//end if

    				$objContacts = $this->getContactsModel()->fetchContacts($arr_where);
    				$arr_contacts = array();
    				foreach ($objContacts as $objContact)
    				{
    					if (isset($objContact->id))
    					{
    						$arr_contacts[] = $objContact;
    					}//end if
    				}//end foreach

    				$objData = (object) $arr_contacts;
    				$objData->hypermedia = $objContacts->hypermedia;

    				$objResult = new JsonModel(array(
						'objData' => $objData,
    				));
    				break;

    			case 'load-contact':
    				$objContact = $this->getContactsModel()->fetchContact($this->objAngularRequestData->cid);

    				//fix some dates
    				$date = $this->formatUserDate(array("date" => $objContact->get('datetime_created'), "options" => array(
    						"output_format" => "d M Y H:i",
    				)));
    				if (!$date)
    				{
    					$date = '';
    				}//end if
    				$objContact->set('datetime_created', $date);

    				$date = $this->formatUserDate(array("date" => $objContact->get('datetime_updated'), "options" => array(
    						"output_format" => "d M Y H:i",
    				)));
    				if (!$date)
    				{
    					$date = '';
    				}//end if
    				$objContact->set('datetime_updated', $date);

    				$date = $this->formatUserDate(array("date" => $objContact->get('datetime_form'), "options" => array(
    						"output_format" => "d M Y H:i",
    				)));
    				if (!$date)
    				{
    					$date = '';
    				}//end if
    				$objContact->set('datetime_form', $date);


    				$date = $this->formatUserDate(array("date" => $objContact->get('tstamp'), "options" => array(
    						"output_format" => "d M Y H:i",
    				)));
    				if (!$date)
    				{
    					$date = '';
    				}//end if
    				$objContact->set('tstamp', $date);

    				$objData = (object) $objContact->getArrayCopy();
    				$objResult = new JsonModel(array(
    						'objData' => $objData,
    				));
    				break;

    			case 'create-contact':
    				//check if form id has been set
    				if (is_numeric($this->objAngularRequestData->arr_post_data['cpp_form_id']))
    				{
    					$cpp_form_id = (int) $this->objAngularRequestData->arr_post_data['cpp_form_id'];
    				} else {
    					//use the first available form
    					$arr_forms = $this->getContactsModel()->getContactProfileForm();
    					end($arr_forms);
    					$cpp_form_id = key($arr_forms);
    				}//end if

    				//load the form
    				$form = $this->getContactsModel()->getContactProfileForm($cpp_form_id);
    				$form->setData($this->objAngularRequestData->arr_post_data);
    				if ($form->isValid())
    				{
    					try {
	    					$arr_data = (array) $form->getData();
	    					$objContact = $this->getContactsModel()->createContact($arr_data, $cpp_form_id);

	    					$objResult = new JsonModel(array(
	    							'objData' => array(
	    									'objContact' => (object) $objContact->getArrayCopy(),
	    							),
	    					));
	    					return $objResult;
    					} catch (\Exception $e) {
							//set error message
							$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
							$objResult = new JsonModel(array(
									'error' => 1,
									'response' => $e->getMessage(),
									'form_messages' => (object) $form->getMessages(),
							));
							return $objResult;
    					}//end catch
    				} else {
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'Form is not valid',
    							'form_errors' => (array) $form->getMessages(),
    					));
    					return $objResult;
    				}//end if

    				$objResult = new JsonModel(array(
    						'objData' => $this->objAngularRequestData->arr_post_data,
    				));
    				return $objResult;
    				break;

    			case 'update-contact':
    				//load contact
    				$objContact = $this->getContactsModel()->fetchContact($this->objAngularRequestData->cid);

					//check if form id has been set
					if (is_numeric($this->objAngularRequestData->arr_post_data['cpp_form_id']))
					{
						$cpp_form_id = (int) $this->objAngularRequestData->arr_post_data['cpp_form_id'];
					} else {
						//use the first available form
						$arr_forms = $this->getContactsModel()->getContactProfileForm();
						end($arr_forms);
						$cpp_form_id = key($arr_forms);
					}//end if

					//load the form
					$form = $this->getContactsModel()->getContactProfileForm($cpp_form_id);
					$form->setData($this->objAngularRequestData->arr_post_data);
					if ($form->isValid())
					{
						try {
							$arr_data = (array) $form->getData();
							$objContact->set($arr_data);
							$objContact = $this->getContactsModel()->updateContact($objContact, $cpp_form_id);

							$objResult = new JsonModel(array(
									'objData' => array(
											'objContact' => (object) $objContact->getArrayCopy(),
									),
							));
							return $objResult;
						} catch (\Exception $e) {
							//set error message
							$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
							$objResult = new JsonModel(array(
									'error' => 1,
									'response' => $e->getMessage(),
									'form_messages' => (object) $form->getMessages(),
							));
							return $objResult;
						}//end catch
					} else {
						$objResult = new JsonModel(array(
								'error' => 1,
								'response' => 'Form validation failed',
								'form_messages' => (object) $form->getMessages(),
						));
						return $objResult;
					}//end if

    				$objResult = new JsonModel(array(
    					'objData' => $this->objAngularRequestData->arr_post_data,
    				));
    				return $objResult;
    				break;

    			case 'load-sources':
    				//load form
    				$form = $this->getContactsModel()->getContactSystemFieldsForm();
    				$arr_source_data = $form->get('source_dropdown')->getValueOptions();

    				$objResult = new JsonModel(array(
    						'objData' => (object) $arr_source_data,
    				));
    				break;

    			case 'load-references':
    				//load form
    				$form = $this->getContactsModel()->getContactSystemFieldsForm();
    				$arr_reference_data = $form->get('reference_dropdown')->getValueOptions();

    				$objResult = new JsonModel(array(
    						'objData' => (object) $arr_reference_data,
    				));
    				break;

    			case 'load-users':
    				//load form
    				$form = $this->getContactsModel()->getContactSystemFieldsForm();
    				$arr_users_data = $form->get('user_id')->getValueOptions();

    				$objResult = new JsonModel(array(
    						'objData' => (object) $arr_users_data,
    				));
    				break;

    			case 'load-statuses':
	    			$objStatuses = $this->getContactStatusModel()->fetchContactStatuses();
	    			$arr_data = array();
	    			foreach ($objStatuses as $objStatus)
	    			{
	    				if (!is_numeric($objStatus->id))
	    				{
	    					continue;
	    				}//end if

	    				$arr_data[] = array("id" => $objStatus->id, "status" => $objStatus->status);
	    			}//end foreach

    				$objResult = new JsonModel(array(
    						'objData' => (object) $arr_data,
    				));
    				break;

    			case 'update-user-meta-data':
    				//load the contact
    				$objContact = $this->getContactsModel()->fetchContact($this->objAngularRequestData->cid);

    				//extract fields from data and validate some values
					$objUser = $this->getUsersModel()->fetchUser($this->objAngularRequestData->arr_post_data['user_id']);
    				$objStatus = $this->getContactStatusModel()->fetchContactStatus($this->objAngularRequestData->arr_post_data['reg_status_id']);

    				$objContact->set('source', 				$this->objAngularRequestData->arr_post_data['source']);
    				$objContact->set('reference', 			$this->objAngularRequestData->arr_post_data['reference']);
    				$objContact->set('user_id',  			(int) $this->objAngularRequestData->arr_post_data['user_id']);
    				$objContact->set('reg_status_id', 		(int) $this->objAngularRequestData->arr_post_data['reg_status_id']);

    				$this->getContactsModel()->updateContact($objContact, "systemfields");

    				$objResult = new JsonModel(array(
    						'objData' => (object) array('response' => 'Contact has been updated'),
    				));
    				break;

    			case 'load-cpp-form':
    				//extract form id from url
    				if (isset($this->objAngularRequestData->arr_params['cpp_fid']) && is_numeric($this->objAngularRequestData->arr_params['cpp_fid']))
    				{
    					$form_id = $this->objAngularRequestData->arr_params['cpp_fid'];
    					$form = $this->getContactsModel()->getContactProfileForm($form_id);
    				}//end if

    				//select contact profile form to use
    				if (is_array($form) && $form_id == "")
    				{
    					//check if form id is set in user session
    					$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    					if (isset($objUserStorage->readUserNativePreferences()->cpp_form_id) && is_string($objUserStorage->readUserNativePreferences()->cpp_form_id))
    					{
    						$form_id = $objUserStorage->readUserNativePreferences()->cpp_form_id;
    						if (!is_numeric($form_id))
    						{
    							//use the first available form
    							$arr_forms = $this->getContactsModel()->getContactProfileForm();
    							$form_id = key($arr_forms);
    						}//end if

    						//load the form
    						$form = $this->getContactsModel()->getContactProfileForm($form_id);
    					}//end if
    				}//end if

    				if (is_null($form))
    				{
    					//use the first available form
    					$arr_forms = $this->getContactsModel()->getContactProfileForm();
    					end($arr_forms);
    					$form_id = key($arr_forms);
    					//load the form
    					$form = $this->getContactsModel()->getContactProfileForm($form_id);
    				}//end if

    				$objForm = $this->renderSystemAngularFormHelper($form, NULL);
    				$objResult = new JsonModel(array(
    						'objData' => $objForm,
    				));
    				break;

    			case 'load-cpp-form-list':
    				//use the first available form
    				$arr_forms = $this->getContactsModel()->getContactProfileForm();

    				$objResult = new JsonModel(array(
    						'objData' => (object) $arr_forms,
    				));
    				break;

    			case 'load-contact-filter-form-fields':
					$form = $this->getContactsModel()->getContactFilterForm();

					$objForm = $this->renderSystemAngularFormHelper($form, NULL);
					$objResult = new JsonModel(array(
							'objData' => $objForm,
					));
    				break;

    			case 'load-contact-statistics':
    				$objContact = $this->getContactsModel()->fetchContact($this->objAngularRequestData->cid);

    				$objData = $this->getContactsModel()->fetchContactStatistics($objContact->get('id'), $this->objAngularRequestData->arr_params);
    				$objResult = new JsonModel(array(
    					'objData' => $objData,
    				));
    				return $objResult;
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

    public function ajaxSearchValuesAction()
    {
    	try {
	    	switch($this->params()->fromQuery("param"))
	    	{
	    		case "regtbl_source":
					$objSources = $this->getFrontContactsSystemFieldsModel()->fetchDistinctContactSources();
					foreach ($objSources as $objSource)
					{
						if ($objSource->source == "")
						{
							continue;
						}//end if

						$arr_data[] = array("id" => $objSource->source, "val" => $objSource->source);
					}//end foreach
	    			break;

	    		case "regtbl_ref":
	    			$objReferences = $this->getFrontContactsSystemFieldsModel()->fetchDistinctContactReferences();
	    			foreach ($objReferences as $objReference)
	    			{
	    				if ($objReference->reference == "")
	    				{
	    					continue;
	    				}//end if

	    				$arr_data[] = array("id" => $objReference->reference, "val" => $objReference->reference);
	    			}//end foreach
	    			break;

	    		case "regtbl_user":
	    			$objUsers = $this->getUsersModel()->fetchUsers();
	    			foreach ($objUsers as $objUser)
	    			{
	    				if (!is_numeric($objUser->id))
	    				{
	    					continue;
	    				}//end if
	    				$arr_data[] = array("id" => $objUser->id, "val" => $objUser->uname);
	    			}//foreach
	    			break;

	    		case "regtbl_status":
	    			$objStatuses = $this->getContactStatusModel()->fetchContactStatuses();
	    			foreach ($objStatuses as $objStatus)
	    			{
	    				if (!is_numeric($objStatus->id))
	    				{
	    					continue;
	    				}//end if
	    				$arr_data[] = array("id" => $objStatus->id, "val" => $objStatus->status);
	    			}//end foreach
	    			break;
	    	}//end switch
    	} catch (\Exception $e) {
    		echo json_encode(array(
    			"error" => 1,
    			"response" => $e->getMessage(),
    		));
    		exit;
    	}//end catch

    	echo json_encode(array(
    		"error" => 0,
    		"response" => $arr_data,
    	), JSON_FORCE_OBJECT);
    	exit;
    }//end function

    public function viewContactAction()
    {
    	//set contact id
    	$reg_id = $this->params()->fromRoute("id", "");

    	if ($reg_id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Contact could not be loaded. ID is not set");

    		//return to contacts index page
    		return $this->redirect()->toRoute("front-contacts");
    	}//end if

    	//extract form id from url
    	$form_id = $this->params()->fromQuery("fid", "");
    	$form = $this->getContactsModel()->getContactProfileForm($form_id);

    	//select contact profile form to use
    	if (is_array($form) && $form_id == "")
    	{
    		//check if form id is set in user session
    		$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    		if (isset($objUserStorage->readUserNativePreferences()->cpp_form_id) && is_string($objUserStorage->readUserNativePreferences()->cpp_form_id))
    		{
    			$form_id = $objUserStorage->readUserNativePreferences()->cpp_form_id;
    			if (!is_numeric($form_id))
    			{
    				//redirect to select profile form action
    				return $this->redirect()->toUrl($this->url()->fromRoute("front-contacts", array("action" => "select-profile-form")) . "?redirect=" . $this->url()->fromRoute("front-contacts", array("action" => "view-contact", "id" => $reg_id)));
    			}//end if

    			$form = $this->getContactsModel()->getContactProfileForm($form_id);
    		} else {
    			//redirect to select profile form action
    			return $this->redirect()->toUrl($this->url()->fromRoute("front-contacts", array("action" => "select-profile-form")) . "?redirect=" . $this->url()->fromRoute("front-contacts", array("action" => "view-contact", "id" => $reg_id)));
    		}//end if
    	}//end if

    	try {
    		//load the contact
    		$objContact = $this->getContactsModel()->fetchContact($reg_id);

    		if ($objContact->get("unsub") > 0)
    		{
    			$this->flashMessenger()->addInfoMessage('Contact is unsubscribed');
    		}//end if
    	} catch (\Exception $e) {
    		//set error message
    		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
    		return $this->redirect()->toRoute("front-contacts");
    	}//end catch

    	if (!$form instanceof \Zend\Form\Form)
    	{
    		$this->flashMessenger()->addErrorMessage("Contact data cannot be displayed. Profile form could not be located");
    		return $this->redirect()->toRoute("front-contacts");
    	}//end if

    	//bind contact data to form
    	$form->bind($objContact);

    	//remove some form elements
    	$form->remove("submit");

    	//disable form elements
    	foreach ($form as $element)
    	{
			switch ($element->getAttribute("type"))
			{
				case "checkbox":
				case "radio":
				case "select":
					$form->get($element->getAttribute("name"))->setAttribute("disabled", "disabled");
					break;

				default:
					$form->get($element->getAttribute("name"))->setAttribute("readonly", "readonly");
					break;
			}//end switch
    	}//end foreach

    	return array(
    		"form" => $form,
    		"objContact" => $objContact,
    	);
    }//end function

    public function ajaxViewContactAction()
    {
    	//set contact id
    	$reg_id = $this->params()->fromRoute("id", "");

    	if ($reg_id == "")
    	{
    		//set error message
    		echo json_encode(array('error' => 1, 'response' => "Contact could not be loaded. ID is not set"), JSON_FORCE_OBJECT);
    		exit;
    	}//end if

    	try {
    		//load the contact
    		$objContact = $this->getContactsModel()->fetchContact($reg_id);
    		echo json_encode(array('error' => 0, 'data' => $objContact->getArrayCopy()), JSON_FORCE_OBJECT);
    		exit;
    	} catch (\Exception $e) {
    		//set error message
    		echo json_encode(array('error' => 1, 'response' => $this->frontControllerErrorHelper()->formatErrors($e)), JSON_FORCE_OBJECT);
    		exit;
    	}//end catch
    }//end function

    public function createContactAction()
    {
    	//extract form id from url
    	$form_id = $this->params()->fromQuery("fid", "");
		$form = $this->getContactsModel()->getContactProfileForm($form_id);

		//select contact profile form to use
		if (is_array($form) && $form_id == "")
		{
			//check if form id is set in user session
			$objUserStorage = FrontUserSession::getUserLocalStorageObject();
			if (is_numeric($objUserStorage->readUserNativePreferences()->cpp_form_id))
			{
				$form_id = $objUserStorage->readUserNativePreferences()->cpp_form_id;
			} else {
				//redirect to select profile form action
				return $this->redirect()->toRoute("front-contacts", array("action" => "select-profile-form"));
			}//end if
		}//end if

		//check if cpp form is available
		if (!is_numeric($form_id))
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Contact Profile Page could not be loaded. Form is not set");

			//redirect to index page
			return $this->redirect()->toRoute("front-contacts");
		}//end if

		//load the form
		if (!$form instanceof \Zend\Form\Form)
		{
			$form = $this->getContactsModel()->getContactProfileForm($form_id);
		}//end if

		//load the request
		$request = $this->getRequest();
		if ($request->isPost())
		{
			//populate form
			$form->setData($request->getPost());

			//validate the form
			if ($form->isValid($request->getPost()))
			{
				try {
					//create the contact
					$objContact = $this->getContactsModel()->createContact($form->getData(), $form_id);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Contact created successfully");

					//redirect to contact profile page
					return $this->redirect()->toRoute("front-contacts", array("action" => "view-contact", "id" => $objContact->get("reg_id")));
				} catch (\Exception $e) {
					//extract errors from the request return by the API
					$arr_response = explode("||", $e->getMessage());
					$objResponse = json_decode($arr_response[1]);

					if (is_object($objResponse))
					{
						switch ($objResponse->HTTP_RESPONSE_CODE)
						{
							case 409: //duplicates found
								//extract message
								$arr_t = explode(":", $objResponse->HTTP_RESPONSE_MESSAGE);
								$id_string = array_pop($arr_t);
								$this->flashMessenger()->addErrorMessage(trim(str_replace(array("{", "}"), "", $id_string)));

								//extract ids and create links to each
								preg_match('~{(.*?)}~', $id_string, $output);
								$arr_contact_ids = explode(",", $output[1]);
								if (is_array($arr_contact_ids) && count($arr_contact_ids) > 0)
								{
									foreach ($arr_contact_ids as $k => $id)
									{
										$this->flashMessenger()->addInfoMessage("<a href=\"" . $this->url()->fromRoute("front-contacts", array("action" => "view-contact", "id" => $id)) . "\" target=\"_blank\" title=\"View Contact\">Click to view duplicate $id</a>");
										if ($k > 19)
										{
											break;
										}//end if
									}//end foreach
								}//end if
								break;

							default:
								//set form errors
								$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
								break;
						}//end switch
					} else {
						//set form errors
						$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
					}//end if
				}//end catch
			}//end if
		}//end if

		return array("form" => $form);
    }//end function


    public function editContactAction()
    {
    	//set contact id
    	$reg_id = $this->params()->fromRoute("id", "");

    	if ($reg_id == "")
    	{
    		//contact id is not, set error message and return to index page
    		$this->flashMessenger()->addErrorMessage("Contact could not be loaded. Id is not set");

    		return $this->redirect()->toRoute("front-contacts");
    	}//end if

    	//extract form id from url
    	$form_id = $this->params()->fromQuery("fid", "");
    	$form = $this->getContactsModel()->getContactProfileForm($form_id);

    	//select contact profile form to use
    	if (is_array($form) && $form_id == "")
    	{
    		//check if form id is set in user session
    		$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    		if (isset($objUserStorage->readUserNativePreferences()->cpp_form_id) && $objUserStorage->readUserNativePreferences()->cpp_form_id != "")
    		{
    			$form_id = $objUserStorage->readUserNativePreferences()->cpp_form_id;
    			if (!is_numeric($form_id))
    			{
    				return $this->redirect()->toUrl($this->url()->fromRoute("front-contacts", array("action" => "select-profile-form")) . "?redirect=" . $this->url()->fromRoute("front-contacts", array("action" => "edit-contact", "id" => $reg_id)));
    			}//end if

    			$form = $this->getContactsModel()->getContactProfileForm($form_id);
    		} else {
    			//redirect to select profile form action
    			return $this->redirect()->toUrl($this->url()->fromRoute("front-contacts", array("action" => "select-profile-form")) . "?redirect=" . $this->url()->fromRoute("front-contacts", array("action" => "edit-contact", "id" => $reg_id)));
    		}//end if
    	}//end if

    	//check if cpp form is available
    	if (!is_string($form_id))
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Contact Profile Page could not be loaded. Form is not defined");

    		//redirect to index page
    		return $this->redirect()->toRoute("front-contacts");
    	}//end if

    	//load the form
    	if (!$form instanceof \Zend\Form\Form)
    	{
    		$form = $this->getContactsModel()->getContactProfileForm($form_id);
    	}//end if

    	//load the contact
    	$objContact = $this->getContactsModel()->fetchContact($reg_id);

    	if ($objContact->get("unsub") > 0)
    	{
    		$this->flashMessenger()->addInfoMessage('Contact is unsubscribed');
    	}//end if

    	//bind data to form
    	$form->bind($objContact);

    	//load the request
    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		//populate form
    		$form->setData($request->getPost());

    		//validate the form
    		if ($form->isValid($request->getPost()))
    		{
    			try {
    				$objContact = $form->getData();
    				$objContact->set("id", $reg_id);

    				//update the contact
    				$objContact = $this->getContactsModel()->updateContact($objContact, $form_id);

    				//set success message
    				$this->flashMessenger()->addSuccessMessage("Contact update successfully");

    				//redirect to contact profile page
    				return $this->redirect()->toRoute("front-contacts", array("action" => "view-contact", "id" => $objContact->get("reg_id")));
    			} catch (\Exception $e) {
					//extract errors from the request return by the API
					$arr_response = explode("||", $e->getMessage());
					$objResponse = json_decode($arr_response[1]);

					if (is_object($objResponse))
					{
						switch ($objResponse->HTTP_RESPONSE_CODE)
						{
							case 409: //duplicates found
								//extract message
								$arr_t = explode(":", $objResponse->HTTP_RESPONSE_MESSAGE);
								$id_string = array_pop($arr_t);
								$this->flashMessenger()->addErrorMessage(trim(str_replace(array("{", "}"), "", $id_string)));

								//extract ids and create links to each
								preg_match('~{(.*?)}~', $id_string, $output);
								$arr_contact_ids = explode(",", $output[1]);
								if (is_array($arr_contact_ids) && count($arr_contact_ids) > 0)
								{
									foreach ($arr_contact_ids as $k => $id)
									{
										$this->flashMessenger()->addInfoMessage("<a href=\"" . $this->url()->fromRoute("front-contacts", array("action" => "view-contact", "id" => $id)) . "\" target=\"_blank\" title=\"View Contact\">Click to view duplicate $id</a>");
										if ($k > 19)
										{
											break;
										}//end if
									}//end foreach
								}//end if
								break;

							default:
								//set form errors
								$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
								break;
						}//end switch
					} else {
						//set form errors
						$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
					}//end if
    			}//end catch
    		}//end if
    	}//end if

    	return array(
    			"form" => $form,
    			"reg_id" => $reg_id,
    			"objContact" => $objContact,
    	);
    }//end function

    public function deleteContactAction()
    {

    }//end function

    public function selectProfileLayoutAction()
    {
    	$request = $this->getRequest();
    	$form = new \Zend\Form\Form;

    	//load contact profile forms
    	$arr_forms = $this->getContactsModel()->getContactProfileForm();

    	//set no option
    	$arr_element_value_options["none"] = "No Layout";

    	//create radio button options
    	foreach ($arr_forms as $key => $form_name)
    	{
    		if (is_numeric($key))
    		{
    			$arr_element_value_options[$key] = $form_name;
    		}//end if
    	}//end foreach

    	//add radio group to form
    	$form->add(array(
    			"type" => "radio",
    			"name" => "cpp_form_id",
    			"options" => array(
    					"label" => "Please select the form you would like to use:",
    					"value_options" => $arr_element_value_options,
    			),
    	));

    	//add remember option radio button
    	$form->add(array(
    			'type' => 'checkbox',
    			'name' => 'remember_layout',
    			'attributes' => array(
    					'id' => 'remember_layout',
    			),
    			'options' => array(
    					'label' => 'Remember my option',
    					'use_hidden_element' => true,
    					'checked_value' => '1',
    					'unchecked_value' => '0'
    			)
    	));

    	$form->add(array(
    			"name" => "submit",
    			"attributes" => array(
    					"value" => "Submit",
    			),
    			"options" => array(
    					"ignore" => TRUE,
    			),
    	));

    	//check if local storage has been enabled
    	$arr_config = $this->getServiceLocator()->get("config");
    	if (!isset($arr_config["logged_in_user_settings"]))
    	{
    		$storage_disabled = TRUE;
    	} elseif (isset($arr_config["logged_in_user_settings"]) && $arr_config["logged_in_user_settings"]["storage_enabled"] !== TRUE) {
    		$storage_disabled = TRUE;
    	}//end if

    	if (isset($storage_disabled))
    	{
			$form->remove("remember_layout");
    	}//end if

    	//load user session data
    	$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    	if (isset($objUserStorage->readUserNativePreferences()->cpp_layout_id) && $objUserStorage->readUserNativePreferences()->cpp_layout_id != "")
    	{
    		$form->get("cpp_form_id")->setValue($objUserStorage->readUserNativePreferences()->cpp_layout_id);
    	}//end if

    	if ($request->isPost()) {

            //validate form submitted
    		$form->setData($request->getPost());
    		if ($form->isValid()) {

                $arr_form_data = $form->getData();
    			$form_id = $arr_form_data["cpp_form_id"];

    			if (isset($arr_form_data["remember_layout"]) && $arr_form_data["remember_layout"] == 1)
    			{
    				//persist user preference
    				$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    				if (is_object($objUserStorage))
    				{
    					$objUserStorage->setUserNativePreferences('cpp_layout_id', $form_id);
    				}//end if
    			}//end if

    			//redirect back to the index page if no layout is selected
    			if ($arr_form_data["cpp_form_id"] == "none")
    			{
    				return $this->redirect()->toRoute("front-contacts");
    			}//end if

    			//redirect back to the contact edit screen with form id specified
    			$url = $this->url()->fromRoute("front-contacts");

    			//execute redirect
    			$response = $this->getResponse();
    			$response->getHeaders()->addHeaderLine('Location', $url . "?fid=$form_id");
    			$response->setStatusCode(302);
    			return $response;
    		}//end if
    	}//end if

    	return array("form" => $form);
    }//end function

    public function selectProfileFormAction()
    {
    	$request = $this->getRequest();
    	$form = new \Zend\Form\Form;
    	$redirect = $this->params()->fromQuery("redirect", "");

    	//load contact profile forms
    	$arr_forms = $this->getContactsModel()->getContactProfileForm();

    	//create radio button options
    	foreach ($arr_forms as $key => $form_name) {
   			$arr_element_value_options[$key] = $form_name;
    	} //end foreach

    	//add radio group to form
    	$form->add(array(
    			"type" => "radio",
    			"name" => "cpp_form_id",
    			"options" => array(
    				"label" => "Please select the form you would like to use:",
    				"value_options" => $arr_element_value_options,
    			),
    	));

    	//add remember option radio button
    	$form->add(array(
             'type' => 'checkbox',
             'name' => 'remember_form',
             'options' => array(
                     'label' => 'Remember my option',
                     'use_hidden_element' => true,
                     'checked_value' => '1',
                     'unchecked_value' => '0'
             )
    	));

    	$form->add(array(
    			"name" => "submit",
    			"attributes" => array(
    					"value" => "Submit",
    			),
    			"options" => array(
    					"ignore" => TRUE,
    			),
    	));

    	//check if local storage has been enabled
    	$arr_config = $this->getServiceLocator()->get("config");
    	if (!isset($arr_config["logged_in_user_settings"]))
    	{
    		$storage_disabled = TRUE;
    	} elseif (isset($arr_config["logged_in_user_settings"]) && $arr_config["logged_in_user_settings"]["storage_enabled"] !== TRUE) {
    		$storage_disabled = TRUE;
    	}//end if

    	if (isset($storage_disabled))
    	{
    		$form->remove("remember_form");
    	}//end if

    	//load user session data
    	$objUserStorage = FrontUserSession::getUserLocalStorageObject();
    	if (is_numeric($objUserStorage->readUserNativePreferences()->cpp_form_id))
    	{
    		$form->get("cpp_form_id")->setValue($objUserStorage->readUserNativePreferences()->cpp_form_id);
    	}//end if

    	if ($request->isPost())
    	{
    		//validate form submitted
    		$form->setData($request->getPost());
    		if ($form->isValid())
    		{
    			$arr_form_data = $form->getData();
    			$form_id = $arr_form_data["cpp_form_id"];

    			if (isset($arr_form_data["remember_form"]) && $arr_form_data["remember_form"] == 1)
    			{
    				//persist user preference
    				if (is_object($objUserStorage))
    				{
    					$objUserStorage->setUserNativePreferences('cpp_form_id', $form_id);
    					$objUserData->cookie_data->cpp_form_id = $form_id;
    				}//end if
    			}//end if

    			//check if redirect has been specified
    			if ($redirect != "")
    			{
    				//redirect received
    				return $this->redirect()->toUrl($redirect . "?fid=$form_id");
    			}//end if

    			//redirect back to the contact edit screen with form id specified
    			$url = $this->url()->fromRoute("front-contacts", array("action" => "create-contact"));

    			//execute redirect
    			$response = $this->getResponse();
    			$response->getHeaders()->addHeaderLine('Location', $url . "?fid=$form_id");
    			$response->setStatusCode(302);
    			return $response;
    		}//end if
    	}//end if

    	return array("form" => $form, "redirect" => $redirect);
    }//end function

    /**
     * Load toolkit sections available
     * @return \Zend\View\Model\JsonModel
     */
    public function iframeContactToolkitSectionsAction()
    {
    	//set layout to toolkit
    	$this->layout('layout/toolkit-parent');

    	$contact_id = $this->params()->fromRoute("id", "");
    	//load contact
    	$objContact = $this->getContactsModel()->fetchContact($contact_id);

    	$arr = array(
	    			"comments" 			=> array("title" => "Comments", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-comments", "id" => $contact_id))),
	    			"forms" 			=> array("title" => "Forms Completed", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-forms-completed", "id" => $contact_id))),
	    			"journeys"			=> array("title" => "Journeys", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-journeys", "id" => $contact_id))),
	    			"status-history" 	=> array("title" => "Contact Status", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-status-history", "id" => $contact_id))),
    				"user-tasks"		=> array("title" => "To-do", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-user-tasks", "id" => $contact_id))),
    				"sales-funnels"		=> array("title" => "Trackers", "url" => $this->url()->fromRoute("front-contact-toolkit", array("action" => "contact-sales-funnels", "id" => $contact_id))),
    	);

    	//check plugins enabled
    	$objUser = \FrontUserLogin\Models\FrontUserSession::isLoggedIn();
    	if (!in_array("to_do_list", $objUser->profile->plugins_enabled))
    	{
    		unset($arr["user-tasks"]);
    	}//end if

    	if (!in_array("sales_funnels", $objUser->profile->plugins_enabled))
    	{
    		unset($arr["sales-funnels"]);
    	}//end if

    	return array(
    		"arr_sections" => $arr,
    		"objContact" => $objContact,
    	);
    }//end function

    public function updateContactSystemFieldsAction()
    {
    	//set layout for dialog
    	$this->layout("layout/layout-body");

    	$contact_id = $this->params()->fromRoute("id", "");

    	//load the contact
    	$objContact = $this->getContactsModel()->fetchContact($contact_id);

		//load form
		$form = $this->getContactsModel()->getContactSystemFieldsForm();

    	//manually set form data
    	$form->get("source")->setValue($objContact->get("source"));
    	$form->get("reference")->setValue($objContact->get("reference"));
    	$form->get("user_id")->setValue($objContact->get("user_id"));

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$form->remove("source_dropdown");
    		$form->remove("reference_dropdown");

    		$form->setData($request->getPost());
    		if ($form->isValid($request->getPost()))
    		{
    			//allocate the data
    			$objContact->set($form->getData());

    			//request update
    			try {
    				$this->getContactsModel()->updateContact($objContact, "systemfields");
    				echo json_encode(array("error" => 0, "response" => "Contact details updated"), JSON_FORCE_OBJECT); exit;
    			} catch (\Exception $e) {
    				echo json_encode(array("error" => 1, "response" => $e->getMessage()), JSON_FORCE_OBJECT); exit;
    			}//end try
    		}//end if
    	}//end if

    	return array(
    		"contact_id" => $contact_id,
    		"form" => $form,
    	);
    }//end function

    public function ajaxLoadSourceListAction()
    {
    	//load form
    	$form = $this->getContactsModel()->getContactSystemFieldsForm();
    	$arr_source_data = $form->get('source_dropdown')->getValueOptions();
    	return new JsonModel($arr_source_data);
    }//end function
    
    public function ajaxLoadReferenceListAction()
    {
    	//load form
    	$form = $this->getContactsModel()->getContactSystemFieldsForm();
    	$arr_reference_data = $form->get('reference_dropdown')->getValueOptions();
    	return new JsonModel($arr_reference_data);
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
	 * Create an instance of the Forms Admin model using the Service Manager
	 * @return \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private function getFormAdminModel()
	{
		if (!$this->model_forms_admin)
		{
			$this->model_forms_admin = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontFormAdminModel");
		}//end function

		return $this->model_forms_admin;
	}//end function

	private function getUsersModel()
	{
		if (!$this->model_users)
		{
			$this->model_users = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersModel");
		}//end if

		return $this->model_users;
	}//end function

	/**
	 * Create an instance of the Front Contacts System Fields Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsSystemFieldsModel
	 */
	private function getFrontContactsSystemFieldsModel()
	{
		if (!$this->model_front_contact_system_fields)
		{
			$this->model_front_contact_system_fields = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsSystemFieldsModel");
		}//end if

		return $this->model_front_contact_system_fields;
	}//end function

	/**
	 * Create an instance of the Front Contact Status Model using the Service Manager
	 * @return \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private function getContactStatusModel()
	{
		if (!$this->model_contact_statuses)
		{
			$this->model_contact_statuses = $this->getServiceLocator()->get("FrontStatuses\Models\FrontContactStatusesModel");
		}//end if

		return $this->model_contact_statuses;
	}//end function
}//end class
