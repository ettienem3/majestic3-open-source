<?php
namespace FrontCommsBulkSend\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
	/**
	 * Container for the Front Comms Bulk Send Model
	 * @var \FrontCommsBulkSend\Models\FrontCommsBulkSendModel
	 */
	private $model_front_comms_bulk_send;
	
    public function indexAction()
    {
       	//load journeys
       	$objJourneys = $this->getFrontCommsBulkSendModel()->fetchJourneys($this->params()->fromQuery());
       	
       	return array(
       		"objJourneys" => $objJourneys,
       	);
    }//end function
    
    public function setCriteriaAction()
    {
    	//set journey id
    	$id = $this->params()->fromRoute("id", "");
    	if ($id == "")
    	{
    		$this->flashMessenger()->addErrorMessage("Bulk Send Criteria could not be loaded. Journey is not specified");
    		return $this->redirect()->toRoute("front-comms-bulksend");
    	}//end if
    	
    	//load the form
		$form = $this->getFrontCommsBulkSendModel()->getBulkCommSendForm();
    	
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				$arr_data = (array) $form->getData();
				$arr_data['journey_id'] = $id;
				
				//amend dates
				if (isset($arr_data['contact_created_start']))
				{
					if ($arr_data['contact_created_start'] == '')
					{
						unset($arr_data['contact_created_start']);
					} else {
						$arr_data['contact_created_start'] = date('c', strtotime($arr_data['contact_created_start']));
					}//end if
				}//end if
				
				if (isset($arr_data['contact_created_end']))
				{
					if ($arr_data['contact_created_end'] == '')
					{
						unset($arr_data['contact_created_end']);
					} else {
						$arr_data['contact_created_end'] = date('c', strtotime($arr_data['contact_created_end']));
					}//end if
				}//end if
				
    			//create request
    			$objBulkSendRequest = $this->getFrontCommsBulkSendModel()->createBulkSendRequest($arr_data);
    			
    			//set success message
    			$this->flashMessenger()->addSuccessMessage("Bulk Send Request created successfully");
    			$this->flashMessenger()->addInfoMessage("The request must be reviewed and approved");
    			
    			//return to the index page
    			return $this->redirect()->toRoute("front-comms-bulksend");			
			}//end if
		}//end if
		
    	//load journey data
    	$objJourney = $this->getFrontCommsBulkSendModel()->fetchJourney($id);
    	 
    	return array(
    			"objJourney" 			=> $objJourney,
    			"objBulkSendRequest" 	=> $objBulkSendRequest,
    			'form' 					=> $form,
    	);
    }//end function
    
    public function setCriteriawwAction()
    {
    	//set journey id
    	$id = $this->params()->fromRoute("id", "");
    	if ($id == "")
    	{
    		$this->flashMessenger()->addErrorMessage("Bulk Send Criteria could not be loaded. Journey is not specified");
    		return $this->redirect()->toRoute("front-comms-bulksend");
    	}//end if
    	
    	//check if send request is specified
    	$bulk_send_id = $this->params()->fromRoute("bulk_send_id", "");
    	if ($bulk_send_id != "")
    	{
    		$objBulkSendRequest = $this->getFrontCommsBulkSendModel()->fetchBulkSendRequest($bulk_send_id);
    	}//end if
    	
    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$arr_data = (array) $request->getPost();
    		$arr_data["journey_id"] = $id;
  		
    		//manipulate dates where set
     		if (isset($arr_data['date_created_start']))
     		{
				if ($arr_data['date_created_start'] == '')
				{
					unset($arr_data['date_created_start']);
				} else {
					//convert to utc format
					$arr_data['date_created_start'] = date('c', strtotime($arr_data['date_created_start']));
				}//end if
     		}//end if
     		
     		if (isset($arr_data['date_created_end']))
     		{
     			if ($arr_data['date_created_end'] == '')
     			{
     				unset($arr_data['date_created_end']);
     			} else {
     				//convert to utc format
     				$arr_data['date_created_end'] = date('c', strtotime($arr_data['date_created_end']));     					
     			}//end if
     		}//end if
    		try {
    			//create request
    			$objBulkSendRequest = $this->getFrontCommsBulkSendModel()->createBulkSendRequest($arr_data);
    			
    			//set success message
    			$this->flashMessenger()->addSuccessMessage("Bulk Send Request created successfully");
    			$this->flashMessenger()->addInfoMessage("The request must be reviewed and approved");
    			
    			//return to the index page
    			return $this->redirect()->toRoute("front-comms-bulksend");
    		} catch (\Exception $e) {
    			//set error message
    			$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
    		}//end catch
    	}//end if
    	
    	//load journey data
    	$objJourney = $this->getFrontCommsBulkSendModel()->fetchJourney($id);
    	
    	return array(
    		"objJourney" 			=> $objJourney,
    		"objBulkSendRequest" 	=> $objBulkSendRequest,
    	);
    }//end function
    
    public function ajaxWebFormsListAction()
    {
    	//load web forms list
    	$objForms = $this->getFrontCommsBulkSendModel()->fetchWebForms(array(
				'qp_export_fields' 				=> 'id,form,form_types_behaviour', //only load specific fields
				'qp_limit' 						=> 'all', //load all forms
				'qp_disable_hypermedia' 		=> 1, //hypermedia must be disabled for qp_limit => all to be accepted
		));

    	foreach ($objForms as $objForm)
    	{
    		if ($objForm->id == "" || str_replace('_', '', $objForm->form_types_behaviour) != 'web')
    		{
    			continue;
    		}//end if

    		$arr[] = array("value" => trim($objForm->id), "text" => $objForm->form);
    	}//end foreach
    	
    	echo json_encode(array("error" => 0, "data" => $arr), JSON_FORCE_OBJECT); exit;
    }//end function
    
    public function ajaxWebFormFieldsListAction()
    {
    	$id = $this->params()->fromQuery("f_id", "");
    	if ($id == "")
    	{
    		$arr = array(
    				"error" => 1,
    				"response" => "Form Fields could not be loaded. ID is not set"
    		);
    		echo json_encode($arr, JSON_FORCE_OBJECT);
    		exit;
    	}//end if
    	
    	$arr_fields = $this->getFrontCommsBulkSendModel()->fetchWebFormFields($id);
    	
    	echo json_encode(array("error" => 0, "data" => $arr_fields), JSON_FORCE_OBJECT); exit;
    }//end function
    
    public function ajaxSalesFunnelsListAction()
    {
    	//load web forms list
    	$objForms = $this->getFrontCommsBulkSendModel()->fetchWebForms(array(
				'qp_export_fields' 				=> 'id,form,form_types_behaviour', //only load specific fields
				'qp_limit' 						=> 'all', //load all forms
				'qp_disable_hypermedia' 		=> 1, //hypermedia must be disabled for qp_limit => all to be accepted
		));
    	
    	foreach ($objForms as $objForm)
    	{
    		if ($objForm->id == "" || str_replace('_', '', $objForm->form_types_behaviour) != 'salesfunnel')
    		{
    			continue;
    		}//end if
    	
    		$arr[] = array("value" => $objForm->id, "text" => $objForm->form);
    	}//end foreach
    	 
    	echo json_encode(array("error" => 0, "data" => $arr), JSON_FORCE_OBJECT); exit;
    }//end function
    
    public function ajaxStandardFieldsListAction()
    {
    	//load data
    	$objFields = $this->getFrontCommsBulkSendModel()->fetchStandardFields();
    	
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
    				$arr[] = array("value" => $objField->get("id"), "text" => $objField->get("description"));
    				break;
    		}//end switch
    		
    		//@TODO set options for additional fields such as fname, sname and email...
    	}//end foreach
    	
    	echo json_encode(array("error" => 0, "data" => $arr), JSON_FORCE_OBJECT); exit;
    }//end function
    
    public function ajaxCustomFieldsListAction()
    {
    	//load data
    	$objFields = $this->getFrontCommsBulkSendModel()->fetchCustomFields(array(
    			'qp_export_fields' 				=> 'id,field,description,fields_types_input_type,fields_types_field_type', //only load specific fields
    			'qp_limit' 						=> 'all', //load all forms
    			'qp_disable_hypermedia' 		=> 1, //hypermedia must be disabled for qp_limit => all to be accepted
    	));
    	 
    	$arr = array();
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
					$arr[] = array("value" => $objField->get("id"), "text" => $objField->get("description"));
					break;
			}//end switch
    	}//end foreach
    	 
    	echo json_encode(array("error" => 0, "data" => $arr), JSON_FORCE_OBJECT); exit;
    }//end function
    
    public function ajaxStandardFieldCriteriaAction()
    {
    	$id = $this->params()->fromQuery("sf_id", "");
    	if ($id == "")
    	{
    		$arr = array(
    					"error" => 1, 
    					"response" => "Standard Field could not be loaded. ID is not set"
    				);
    		echo json_encode($arr, JSON_FORCE_OBJECT);
    		exit;
    	}//end if
  	
    	//load field html
    	$html = $this->getFrontCommsBulkSendModel()->fetchStandardField($id);
 	
    	if (!$html)
    	{
    		$html = "";
    	}//end if
    	
    	$arr = array(
    		"error" => 0,
    		"html" => $html,
    	);
    	
    	echo json_encode($arr, JSON_FORCE_OBJECT);
    	exit;
    }//end function
    
    public function ajaxCustomFieldCriteriaAction()
    {
    	$id = $this->params()->fromQuery("cf_id", "");
    	if ($id == "")
    	{
    		$arr = array(
    				"error" => 1,
    				"response" => "Custom Field could not be loaded. ID is not set"
    		);
    		echo json_encode($arr, JSON_FORCE_OBJECT);
    		exit;
    	}//end if
    	
    	//load field html
    	$html = $this->getFrontCommsBulkSendModel()->fetchCustomField($id);
    	 
    	if (!$html)
    	{
    		$html = "";
    	}//end if
    	 
    	$arr = array(
    			"error" => 0,
    			"html" => $html,
    	);
    	 
    	echo json_encode($arr, JSON_FORCE_OBJECT);
    	exit;
    }//end function
    
    public function ajaxContactStatusListAction()
    {
    	$arr_statuses = $this->getFrontCommsBulkSendModel()->fetchContactStatuses();
    	
    	echo json_encode(array("error" => 0, "data" => $arr_statuses), JSON_FORCE_OBJECT);
    	exit;
    }//end function
    
    /**
     * Create an instance for the Front Comms Bulk Send Model using the Service Manager
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
