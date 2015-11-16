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
    		
    		try {
    			//create request
    			$objBulkSendRequest = $this->getFrontCommsBulkSendModel()->createBulkSendRequest($arr_data);
    			
    			//set success message
    			$this->flashMessenger()->addSuccessMessage("Bulk Send Request created successfully");
    			
    			//return to the index page
    			return $this->redirect()->toRoute("front-comms-bulksend");
    		} catch (\Exception $e) {
    			$this->flashMessenger()->addErrorMessage("Bulk Send Request Failed : " . $e->getMessage());
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
    	$objForms = $this->getFrontCommsBulkSendModel()->fetchWebForms();

    	foreach ($objForms as $objForm)
    	{
    		if ($objForm->id == "")
    		{
    			continue;
    		}//end if
    		
    		$arr[] = array("value" => $objForm->id, "text" => $objForm->form);
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
    	$objForms = $this->getFrontCommsBulkSendModel()->fetchWebForms();
    	
    	foreach ($objForms as $objForm)
    	{
    		if ($objForm->id == "")
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
    		
    		$arr[] = array("value" => $objField->get("id"), "text" => $objField->get("description"));
    	}//end foreach
    	
    	echo json_encode(array("error" => 0, "data" => $arr), JSON_FORCE_OBJECT); exit;
    }//end function
    
    public function ajaxCustomFieldsListAction()
    {
    	//load data
    	$objFields = $this->getFrontCommsBulkSendModel()->fetchCustomFields();
    	 
    	foreach ($objFields as $objField)
    	{
    		if (!is_object($objField) || $objField->get("id") == "")
    		{
    			continue;
    		}//end if
    	
    		$arr[] = array("value" => $objField->get("id"), "text" => $objField->get("description"));
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
