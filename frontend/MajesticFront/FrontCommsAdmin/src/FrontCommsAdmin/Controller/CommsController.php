<?php
namespace FrontCommsAdmin\Controller;

use FrontCore\Adapters\AbstractCoreActionController;
use Zend\View\Model\JsonModel;

class CommsController extends AbstractCoreActionController
{
	/**
	 * Container for the CommsAdmin Model
	 * @var \FrontCommsAdmin\Models\FrontCommsAdminModel
	 */
	private $model_commsadmin;

	/**
	 * Container for the Comm Attachments Model
	 * @var \FrontCommsAdmin\Models\FrontCommsAdminCommAttachmentsModel
	 */
	private $model_comm_attachments;

	/**
	 * Container for the Journeys Model
	 * @var \FrontCommsAdmin\Models\FrontJourneysModel
	 */
	private $model_journeys;


	/**
	 * Container for the journey id, set from route
	 * @var mixed
	 */
	private $journey_id;

    public function indexAction()
    {
    	//load journey id
		$this->setJourneyId();

		try {
			//load journey details
			$objJourney = $this->getJourneysModel()->fetchJourney($this->journey_id);
	
	        //load the comm
	        $objComms = $this->getCommsAdminModel()->fetchCommsAdmin(array("journey_id" => $this->journey_id));
        } catch (\Exception $e) {
        	$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
        	return $this->redirect()->toRoute('home');
        }//end catch
        
        return array(
        		"objJourney" => $objJourney,
        		"objComms" => $objComms,
        		"journey_id" => $this->journey_id
        );
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
    		
    		if (isset($arr_post_data['episode_id']))
    		{
    			$arr_params['episode_id'] = $arr_post_data['episode_id'];
    		}//end if
    	}//end if
    	
    	try {
    		//validate the journey
    		$objJourney = $this->getJourneysModel()->fetchJourney($arr_params['journey_id']);
    		if (!is_object($objJourney) || $objJourney->get('id') != $arr_params['journey_id'])
    		{
    			$objResult = new JsonModel(array(
    				'error' => 1,
    				'response' => 'The requested Journey could not be located',
    			));	
    			return $objResult;
    		}//end if
    		
    		switch ($acrq)
    		{
    			case 'load-journey-episodes':
    				//load the comm
    				$objComms = $this->getCommsAdminModel()->fetchCommsAdmin(array("journey_id" => $objJourney->get('id')));
    				$arr_comms = array();
    				foreach ($objComms as $objComm)
    				{
    					if (isset($objComm->id))
    					{
    						if ($objComm->date_expiry != '' && $objComm->date_expiry != '0000-00-00')
    						{
    							$t = strtotime($objComm->date_expiry);
    							$objComm->date_expiry = date('d M Y', $t);
    						}//end if
    						
    						foreach ($objComm as $k => $v)
    						{
    							if (is_numeric($v))
    							{
    								$objComm->$k = ($v * 1);
    							}//end if
    						}//end foreach
    						
    						$arr_comms[] = $objComm;
    					}//end if
    				}//end foreach
    				
    				$objResult = new JsonModel(array(
    						'error' => 0,
    						'objData' => (object) $arr_comms,
    				));
    				return $objResult;
    				break;
    				
    			case 'load-journey-episode':
    				$objEpisode = $this->getCommsAdminModel()->fecthCommAdmin($arr_params['episode_id']);
    				
    				if (!is_object($objEpisode) || $objEpisode->get('id') != $arr_params['episode_id'])
    				{
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'The requested episode could not be located',
    					));
    					return $objResult;
    				}//end if
    				
    				//set some values before returning the data
    				if ($objEpisode->get('date_expiry') != '' && $objEpisode->get('date_expiry') != '0000-00-00')
    				{
    					$t = strtotime($objEpisode->get('date_expiry'));
    					$objEpisode->set('date_expiry', date('d M Y', $t));
    				}//end if
    				
    				$objResult = new JsonModel(array(
    						'error' => 0,
    						'objData' => (object) $objEpisode->getArrayCopy(),
    				));
    				return $objResult;
    				break;
    				
    			case 'load-episode-admin-form':
    				//load the form
    				$form = $this->getCommsAdminModel()->getCommsAdminForm(array("journey_id" => $arr_params['journey_id'], "comm_id" => $arr_params['episode_id']));
    				
    				//remove some fields from the form for existing episodes
    				if (is_numeric($arr_params['episode_id']))
    				{
    					if ($form->has('comm_type_id'))
    					{
    						$form->remove('comm_type_id');
    					}//end if
    					
    					if ($form->has('comm_via_id'))
    					{
    						$form->remove('comm_via_id');
    					}//end if
    				}//end if
    				
    				$objForm = $this->renderSystemAngularFormHelper($form, NULL);
    				$objResult = new JsonModel(array(
    						'objData' => $objForm,
    				));
    				return $objResult;
    				break;
    				
    			case 'create-episode':
    				$form = $this->getCommsAdminModel()->getCommsAdminForm(array("journey_id" => $arr_params['journey_id']));
    				
    				//add missing fields to data
    				foreach ($form->getElements() as $objElement)
    				{
    					if (!isset($arr_post_data[$objElement->getName()]))
    					{
    						$arr_post_data[$objElement->getName()] = '';
    					}//end if
    				}//end foreach
    				
    				$form->setData($arr_post_data);
    				if ($form->isValid())
    				{
    					$arr_data = (array) $form->getData();
    					$arr_data['not_send_public_holidays'] = 1;
    					
    					$objEpisode = $this->getCommsAdminModel()->createCommsAdmin($arr_data);
    					$objResult = new JsonModel(array(
    						'error' => 0,
    						'objData' => $objEpisode->getArrayCopy(),
    					));
    					return $objResult;
    				} else {
    					$objResult = new JsonModel(array(
    							'error' => 0,
    							'objData' => $objEpisode->getArrayCopy(),
    					));
    					return $objResult;
    				}//end if
    				break;
    				
    			case 'edit-episode':
    				$objEpisode = $this->getCommsAdminModel()->fecthCommAdmin($arr_params['episode_id']);
    				
    				if (!is_object($objEpisode) || $objEpisode->get('id') != $arr_params['episode_id'])
    				{
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'The requested episode could not be located',
    					));
    					return $objResult;
    				}//end if
    				
    				$form = $this->getCommsAdminModel()->getCommsAdminForm(array("journey_id" => $arr_params['journey_id'], 'comm_id' => $arr_params['episode_id']));
    				
    				//add missing fields to data
    				foreach ($form->getElements() as $objElement)
    				{
    					if (!isset($arr_post_data[$objElement->getName()]))
    					{
    						$arr_post_data[$objElement->getName()] = '';
    					}//end if
    				}//end foreach
    				
    				$arr_episode_data = $objEpisode->getArrayCopy();
    				$form->bind($objEpisode);
    				$form->setData($arr_post_data);
    				if ($form->isValid())
    				{
    					$objEpisode = $form->getData();
    					$objEpisode->set('not_send_public_holidays', 1);
    					$objEpisode->set('comm_via_id', $arr_episode_data['comm_via_id']);
    					$objEpisode->set('id', $arr_episode_data['id']);
    					$objEpisode->set('journey_id', $arr_episode_data['journey_id']);
					
    					$this->getCommsAdminModel()->updateCommAdmin($objEpisode);
    					$objResult = new JsonModel(array(
    							'error' => 0,
    							'objData' => $objEpisode->getArrayCopy(),
    					));
    					return $objResult;
    				} else {
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'Form validation failed',
    							'form_messages' => (object) $form->getMessages(),
    					));
    					return $objResult;
    				}//end if
    				
    				$this->getCommsAdminModel()->updateCommAdmin($objEpisode);
    				$objResult = new JsonModel(array(
    					'error' => 0,
    					'objData' => (object) $objEpisode->getArrayCopy(),
    				));
    				return $objResult;
    				break;
    				
    			case 'delete-episode':
    				$objEpisode = $this->getCommsAdminModel()->fecthCommAdmin($arr_params['episode_id']);
    				
    				if (!is_object($objEpisode) || $objEpisode->get('id') != $arr_params['episode_id'])
    				{
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'The requested episode could not be located',
    					));
    					return $objResult;
    				}//end if
    				
    				$this->getCommsAdminModel()->deleteCommsAdmin($objEpisode->get('id'));
    				
    				$objResult = new JsonModel(array(
    					'error' => 0,	
    				));
    				return $objResult;
    				break;
    				
    			case 'toggle-episode-status':
    				$objEpisode = $this->getCommsAdminModel()->fecthCommAdmin($arr_params['episode_id']);
				
    				if (!is_object($objEpisode) || $objEpisode->get('id') != $arr_params['episode_id'])
    				{
    					$objResult = new JsonModel(array(
    						'error' => 1,
    						'response' => 'The requested episode could not be located',
    					));
    					return $objResult;
    				}//end if
    				
    				$objEpisode->set('active', (1 - $objEpisode->get('active')));
    				$this->getCommsAdminModel()->updateCommStatus($objEpisode->get('id'));
    				$objResult = new JsonModel(array(
    					'error' => 0,
    					'objData' => (object) $objEpisode->getArrayCopy(),
    				));
    				return $objResult;
    				break;
    		}//end switch
    	} catch (\Exception $e) {
    		$objResult = new JsonModel(array(
    				'error' => 1,
    				'response' => $this->frontControllerErrorHelper()->formatErrors($e),
    				'raw_response' => $e->getMessage(),
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
     * Create an new Comm Admin
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Zend\Form\Form
     */
    public function createAction()
    {
    	//load the journey id
    	$this->setJourneyId();

    	//load the form
    	$form = $this->getCommsAdminModel()->getCommsAdminForm(array("journey_id" => $this->journey_id));

    	//set form journey id
    	$form->get("journey_id")->setValue($this->journey_id);
    	//default to email type
    	$form->get("comm_via_id")->setValue(1);
    	//remove public holiday field for now
    	if ($form->has("not_send_public_holidays"))
    	{
    		$form->remove("not_send_public_holidays");
    	}//end if
    	//default time of day to anytime
    	if ($form->has("send_after_hours"))
    	{
    		$form->get("send_after_hours")->setValue(0);
    	}//end if

		$send_time_delay_text = $this->setCommDelayString(0);

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		//set the form data
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			try {
    				$arr_data = $form->getData();
    				$arr_data["not_send_public_holidays"] = 1;

    				//create the CommAdmin
    				$objCommAdmin = $this->getCommsAdminModel()->createCommsAdmin($arr_data);

    				//set the success message
    				$this->flashMessenger()->addSuccessMessage("Communication has been created");

    				//redirect to the index page
    				return $this->redirect()->toRoute("front-comms-admin/comms", array("journey_id" => $this->journey_id));
    			} catch (\Exception $e) {
    				//set the error message
    				$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			}//end catch
    		}//end if

    		//reset communication timing delay
    		$send_time_delay_text = $this->setCommDelayString($request->getPost("send_time"));
    	}//end if

    	return array(
    			"send_time_delay_text" => $send_time_delay_text,
    			"form" => $form,
    			"journey_id" => $this->journey_id
    	);
    }//end function

    /**
     * Update an existing comm admin
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Form\Form\Form
     */
    public function editAction()
    {
    	//set the journey id
    	$this->setJourneyId();

    	//get the id
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set the message and return to the index page.
    		$this->flashMessenger()->addErrorMessage("Communication could not be loaded. ID is not set");

    		//redirect to the index page
    		return $this->redirect()->toRoute("front-comms-admin/comms", array("journey_id" => $this->journey_id));
    	}//end if

    	//load the commadmin details
    	$objCommAdmin = $this->getCommsAdminModel()->fecthCommAdmin($id);

    	if (!$objCommAdmin)
    	{
    		//set the message and return to the index page.
    		$this->flashMessenger()->addErrorMessage("Communication could not be loaded. Data could not be located");

    		//redirect to the index page
    		return $this->redirect()->toRoute("front-comms-admin/comms", array("journey_id" => $this->journey_id));
    	}//end if

    	//check if communication is active, if so, prevent updates
    	if ($objCommAdmin->get("active") == 1)
    	{
    		//set the message and return to the index page.
    		$this->flashMessenger()->addInfoMessage("Communications cannot be changed while active");

    		//redirect to the index page
    		return $this->redirect()->toRoute("front-comms-admin/comms", array("journey_id" => $this->journey_id));
    	}//end if

    	/**
    	 * Check some data conditions
    	 */
    	if ($objCommAdmin->get("date_expiry") == "0000-00-00" || $objCommAdmin->get("date_expiry") == "00-00-0000" || $objCommAdmin->get("date_expiry") == "")
    	{
     		$objCommAdmin->set("date_expiry", "");
    	}//end if

    	if ($objCommAdmin->get("date_start") == "0000-00-00" || $objCommAdmin->get("date_start") == "00-00-0000" || $objCommAdmin->get("date_start") == "")
    	{
     		$objCommAdmin->set("date_start", "");
    	}//end if

    	//load the form
    	$form = $this->getCommsAdminModel()->getCommsAdminForm(array("journey_id" => $this->journey_id, "comm_id" => $id));

    	//remove public holiday field for now
    	if ($form->has("not_send_public_holidays"))
    	{
    		$form->remove("not_send_public_holidays");
    	}//end if

    	//bind the data
    	$form->bind($objCommAdmin);

    	//set expiry date
    	if ($form->has("date_expiry") && $objCommAdmin->get("date_expiry") != "")
    	{
    		if ($objCommAdmin->get("date_expiry") != "0000-00-00")
    		{
    			$form->get("date_expiry")->setValue($objCommAdmin->get("date_expiry"));
    		}//end if
    	}//end if

    	if ($form->has("date_start") && $objCommAdmin->get("date_start") != "")
    	{
    		if ($objCommAdmin->get("date_start") != "0000-00-00")
    		{
    			$form->get("date_start")->setValue($objCommAdmin->get("date_start"));
    		}//end if
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		//set the form data
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			try {
    				$objCommAdmin = $form->getData();

    				//set id from route
    				$objCommAdmin->set("id", $id);
    				$objCommAdmin->set("not_send_public_holidays", 0);
    				$objCommAdmin = $this->getCommsAdminModel()->updateCommAdmin($objCommAdmin);

    				//set the success message
    				$this->flashMessenger()->addSuccessMessage("Communication has been updated");

    				//return to index page
    				return $this->redirect()->toRoute("front-comms-admin/comms",  array("journey_id" => $this->journey_id));
    			} catch (\Exception $e) {
					//set form error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			}//end catch
    		}//end if
    	}//end if

    	//set communication delay time string based on set value
    	$send_time_delay_text = $this->setCommDelayString($objCommAdmin->get("send_time"));

    	return array(
    			"send_time_delay_text" => $send_time_delay_text,
    			"form" => $form,
    			"journey_id" => $this->journey_id,
    			"objCommAdmin" => $objCommAdmin,
    	);
    }//end function

    public function attachmentsAction()
    {
    	//set the journey id
    	$this->setJourneyId();

    	//get the id
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set the message and return to the index page.
    		$this->flashMessenger()->addErrorMessage("Communication could not be loaded. ID is not set");

    		//redirect to the index page
    		return $this->redirect()->toRoute("front-comms-admin/comms", array("journey_id" => $this->journey_id));
    	}//end if

    	//load the commadmin details
    	$objCommAdmin = $this->getCommsAdminModel()->fecthCommAdmin($id);

    	//load comm attachments
    	$objCommAttachments = $this->getCommAttachmentsModel()->fetchCommAttachments($objCommAdmin->get("id"));

    	return array(
    		"journey_id" => $this->journey_id,
    		"objCommAdmin" => $objCommAdmin,
    		"objAttachments" => $objCommAttachments,
    	);
    }//end function

    public function ajaxAddCommAttachmentAction()
    {
    	//set the journey id
    	$this->setJourneyId();

    	//get the id
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
			echo json_encode(array(
				"error" => 1,
				"response" => "Communication attachment could not be set. ID is not set",
			),
			JSON_FORCE_OBJECT);
			exit;
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		//load the form
    		$form = $this->getCommAttachmentsModel()->getCommAttachmentForm();
    		$form->setData($request->getPost());

    		if ($form->isValid($request->getPost()))
    		{
    			$arr_data = (array) $form->getData();

    			try {
    				//allocate the attachment
    				$objData = $this->getCommAttachmentsModel()->createCommAttachment($id, $arr_data);

    				echo json_encode(array(
    					"error" => 0,
    					"response" => "Communication attachment saved",
    				),
    				JSON_FORCE_OBJECT);
    				exit;
    			} catch (\Exception $e) {
    				echo json_encode(array(
    					"error" => 1,
    					"response" => $e->getMessage(),
    				),
    				JSON_FORCE_OBJECT);
    				exit;
    			}//end catch
    		}//end if

    		echo json_encode(array(
    			"error" => 1,
    			"response" => "No data has been received",
    		),
    		JSON_FORCE_OBJECT);
    		exit;
    	}//end if
    }//end function

    public function ajaxRemoveCommAttachmentAction()
    {
    	//set the journey id
    	$this->setJourneyId();

    	//get the id
    	$comm_id = $this->params()->fromRoute("id", "");
    	$attachment_id = $this->params()->fromQuery("attachment_id", "");

    	if ($comm_id == "" || $attachment_id == "")
    	{
    		echo json_encode(array(
    				"error" => 1,
    				"response" => "Communication attachment could not be set. ID is not set",
    		),
    				JSON_FORCE_OBJECT);
    		exit;
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		try {
    			$this->getCommAttachmentsModel()->deleteCommAttachment($comm_id, $attachment_id);

    			echo json_encode(array(
    				"error" => 0,
    				"response" => "Attachment has been removed",
    			),
    			JSON_FORCE_OBJECT);
    			exit;
    		} catch (\Exception $e) {
    			echo json_encode(array(
    				"error" => 1,
    				"response" => $e->getMessage(),
    			), JSON_FORCE_OBJECT);
    			exit;
    		}//end catch
    	}//end if

    	echo json_encode(array(
    			"error" => 1,
    			"response" => "No data has been received",
    	),
    			JSON_FORCE_OBJECT);
    	exit;
    }//end function

    /**
     * Delete a Comms Admin
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function deleteAction()
    {
    	//set journey id
    	$this->setJourneyId();

    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Communication could not be deleted. ID is not set");

    		//return to the index page
    		return $this->redirect()->toRoute("front-comms-admin/comms",  array("journey_id" => $this->journey_id));
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		if ($this->params()->fromPost("delete") == "Yes")
    		{
		    	//try delete the comm
		    	try {
		    		$objCommAdmin = $this->getCommsAdminModel()->deleteCommsAdmin($id);

		    		//set the message
		    		$this->flashMessenger()->addSuccessMessage("Communcation has been deleted");
		    	} catch (\Exception $e) {
		    		//set error message
    				$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
		    	}//end catch
    		}//end if

	    	//redirect to index page
	    	return $this->redirect()->toRoute("front-comms-admin/comms",  array("journey_id" => $this->journey_id));
    	}//end if

    }//end function

    /**
     * Activate or deactivate Comm
     *
     */
    public function statusAction()
    {
    	//set journey id
    	$this->setJourneyId();

    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set the error message
    		$this->flashMessenger()->addErrorMessage("Communication could not be activated. ID is not set");

    		//return to index page
    		return $this->redirect()->toRoute("front-comms-admin/comms",  array("journey_id" => $this->journey_id));
    	}//end if

    	try {
			$this->getCommsAdminModel()->updateCommStatus($id);

    		//set the success message
    		$this->flashMessenger()->addSuccessMessage("Communication Status updated");
    	} catch (\Exception $e) {
    		//set error message
    		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
    	}//end catch

    	//redirect to indexpage
    	return $this->redirect()->toRoute("front-comms-admin/comms",  array("journey_id" => $this->journey_id));
    }//end function

    public function setCommDelayAction()
    {
    	//set layout
    	$this->layout("layout/layout-body");

    	$id = $this->params()->fromRoute("id", "");
    	$send_time = $this->params()->fromQuery("send_time", "0");

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$send_days = $request->getPost("send_days");
    		$send_hours = $request->getPost("send_hours");
    		$send_mins = $request->getPost("send_mins");

    		//perform calculation
    		$send_time = (86400 * $send_days); //days
    		$send_time = $send_time + (3600 * $send_hours); //hours
    		$send_time = $send_time + (60 * $send_mins); //mins

    		echo json_encode(array(
    			"error" => 0,
    			"data" => array("send_time" => $send_time, "send_text" => "$send_days Days, $send_hours Hours, $send_mins Minutes"),
    		), JSON_FORCE_OBJECT);
    		exit;
    	}//end if

    	return array(
    		"send_time" => $send_time,
    		"arr_times" => $this->setCommDelayString($send_time, TRUE),
    	);
    }//end function

    private function setCommDelayString($value, $return_array = FALSE)
    {
    	if ($value / 86400 >= 1)
    	{
    		$send_days = (int) ($value / 86400);
    	} else {
    		$send_days = 0;
    	}//end if

    	$send_time = (int) $value - ($send_days * 86400);
    	if ($send_time / 3600 >= 1)
    	{
    		$send_hours = (int) ($send_time / 3600);
    	} else {
    		$send_hours = 0;
    	}//end if

    	$send_time = $send_time - ($send_hours * 3600);
    	if ($send_time / 60 >= 1)
    	{
    		$send_mins = (int) ($send_time / 60);
    	} else {
    		$send_mins = 0;
    	}//end if

    	if ($return_array === TRUE)
    	{
    		return array(
    			"days" => $send_days,
    			"hours" => $send_hours,
    			"minutes" => $send_mins,
    		);
    	}//end if

    	switch ($send_days)
    	{
    		case 1:
				$days = $send_days . " Day";
    			break;

    		case 0:
    			$days = "";
    			break;

    		default:
    			$days = $send_days . " Days";
    			break;
    	}//end switch

    	switch ($send_hours)
    	{
    		case 1:
    			$hours = $send_hours . " Hour";
    			break;

    		case 0:
    			$hours = "0 Hours";
    			break;

    		default:
    			$hours = $send_hours . " Hours";
    			break;
    	}//end switch

    	switch ($send_mins)
    	{
    		case 1:
    			$mins = $send_mins . " Minute";
    			break;

    		case 0:
    			$mins = "0 Minutes";
    			break;

    		default:
    			$mins = $send_mins . " Minutes";
    			break;
    	}//end switch

    	return $days . " " . $hours . " " . $mins;
    }//end function

    /**
     * Create an instance of the commsadmin model using the service manager
     * @return \FrontCommsAdmin\Models\FrontCommsAdminModel
     */
    private function getCommsAdminModel()
    {
    	if (!$this->model_commsadmin)
    	{
    		$this->model_commsadmin = $this->getServiceLocator()->get("FrontCommsAdmin\Models\FrontCommsAdminModel");
    	}//end if

    	return $this->model_commsadmin;
    }//end function

    /**
     * Create an instance of the Comm Attachments Model using the Service Manager
     * @return \FrontCommsAdmin\Models\FrontCommsAdminCommAttachmentsModel
     */
    private function getCommAttachmentsModel()
    {
    	if (!$this->model_comm_attachments)
    	{
			$this->model_comm_attachments = $this->getServiceLocator()->get("FrontCommsAdmin\Models\FrontCommsAdminCommAttachmentsModel");
    	}//end if

    	return $this->model_comm_attachments;
    }//end function

    /**
     * Extract the journey id from the route
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    private function setJourneyId()
    {
    	$this->journey_id = $this->params()->fromRoute("journey_id", "");

    	if ($this->journey_id == "")
    	{
    		//set error message and redirect back to home page
    		$this->flashMessenger()->addErrorMessage("Communications could not be loaded. Journey is not set");
    		return $this->redirect()->toRoute("home");
    	}//end if
    }//end function

    /**
     * Create an instance of the Journeys Model using the Service Manager
     * @return \FrontCommsAdmin\Models\FrontJourneysModel
     */
    private function getJourneysModel()
    {
    	if (!$this->model_journeys)
    	{
    		$this->model_journeys = $this->getServiceLocator()->get("FrontCommsAdmin\Models\FrontJourneysModel");
    	}//end if

    	return $this->model_journeys;
    }//end function
}//end class
