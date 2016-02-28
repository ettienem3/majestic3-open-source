<?php
namespace FrontStatuses\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ContactStatusesController extends AbstractActionController
{
	/**
	 * Container for Contact Statuses Model instance.
	 * @var \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private $model_contact_statuses;

	/**
	 * Container for the Front Behaviours Config Model
	 * @var \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
	 */
	private $model_front_behaviours_config;

	/**
	 * Loads list of Statuses using service manager.
	 * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
	 */
    public function indexAction()
    {
    	$objContactStatuses = $this->getContactStatusesModel()->fetchContactStatuses($this->params()->fromQuery());
        return array("objContactStatuses" => $objContactStatuses);
    }//end function


    /**
     * Create a new Contact Status
     * @return multitype:\Zend\Form\Form
     */
    public function createAction()
    {
    	// Loads Contact Status System Form
    	$form = $this->getContactStatusesModel()->getContactStatusSystemForm();

    	// HTTP Request
    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		// Populate data into Contact Status Form
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			try {
    				$objContactStatus = $this->getContactStatusesModel()->createContactStatus($form->getData());

    				// Set successful message
    				$this->flashMessenger()->addSuccessMessage("Status created successfully");

    				// Redirect to index page.
    				return $this->redirect()->toRoute("front-statuses");
    			} catch (\Exception $e) {
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			} // end try{}
    		} // end if
    	} // end if

    	// Loads Contact Status System Form
    	return array("form" => $form);
    } //end function


    /**
     * Update existing Contact Status
     * @return multitype:\Zend\Form\Form
     */
    public function editAction()
    {
    	$id = $this->params()->fromRoute("id", "");
    	if ($id == "")
    	{
    		// Set unsuccessful message
    		$this->flashMessenger()->addErrorMessage("Contact Status could not be loaded. ID is not set.");

    		// Redirect to the index page
    		return $this->redirect()->toRoute("front-statuses");
    	} // end if

    	// Load Contact Status details.
    	$objContactStatus = $this->getContactStatusesModel()->fetchContactStatus($id);

    	// Loads Contact Status form
    	$form = $this->getContactStatusesModel()->getContactStatusSystemForm();

    	// Populate data for Contact Status
    	$form->bind($objContactStatus);

    	// Get HTTP request
    	$request = $this->getRequest();

    	if ($request->isPost())
    	{
    		// Load Contact Status form
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			try {
    				$objContactStatus = $form->getData();
    				$objContactStatus->set("id", $id);
    				$objContactStatus = $this->getContactStatusesModel()->updateContactStatus($objContactStatus);

    				// Set successful message
    				$this->flashMessenger()->addSuccessMessage("Status updated successfully");

    				// Redirect to index page
    				return $this->redirect()->toRoute("front-statuses");
    			} catch (\Exception $e) {
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			} // end try
    		} // end if
    	} // end if

    	// Loads Contact Status system form
    	return array(
    			"form" => $form,
    			"objContactStatus" => $objContactStatus,
    	);
    } // end function


    /**
     * Delete existing Contact Status
     */
    public function deleteAction()
    {
    	$id = $this->params()->fromRoute("id", "");
    	if ($id == "")
    	{
    		// Set unsuccessful message
    		$this->flashMessenger()->addErrorMessage("Contact Status could not be deleted. ID is not set.");

    		// Redirect to index page.
    		return $this->redirect()->toRoute("front-statuses");
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		if (strtolower($request->getPost("delete")) == "yes")
    		{
    			try {
    				$this->getContactStatusesModel()->deleteContactStatus($id);

    				// Set successful message
    				$this->flashMessenger()->addSuccessMessage("Contact Status delete successfully");
    			} catch (\Exception $e) {
    				//set error message
    				$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
    			} // end try
    		}//end if

    		// Redirect to index page
    		return $this->redirect()->toRoute("front-statuses");
    	}//end if

		return array();
    } // end function

    /**
     * Update Contact Status active column to be Active/Inactive.
     */
    public function statusAction()
    {
    	$id = $this->params()->fromRoute("id", "");
    	if ($id == "")
    	{
    		// Set unsuccessful message
    		$this->flashMessenger()->addErrorMessage("Contact Status could not be activated. ID is not set.");

    		// Return to index page
    		return $this->redirect()->toRoute("front-statuses");
    	}

    	try {
    		// Loads Contact Status details
    		$objContactStatus = $this->getContactStatusesModel()->fetchContactStatus($id);
    		$objContactStatus->set("active", (1 - $objContactStatus->get("active")));

    		$objContactStatus = $this->getContactStatusesModel()->updateContactStatus($objContactStatus);

    		// Set successful message
    		$this->flashMessenger()->addSuccessMessage("Status updated successfully.");
    	} catch (\Exception $e) {
    		//set error message
    		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
    	} // end

    	// Redirect to index page.
    	return $this->redirect()->toRoute("front-statuses");
    } // end function

    public function statusBehavioursAction()
    {
    	//set layout
    	$this->layout("layout/behaviours-view");

    	//load behaviours form
    	$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm("reg_status");
    	$form = $arr_config_form_data["form"];
    	$arr_descriptors = $arr_config_form_data["arr_descriptors"];

    	//set data array to collect behaviours and pass url data to view
    	$arr_behaviour_params = array(
    			"status_id" => $this->params()->fromRoute("id"),
    			"behaviour" => "reg_status",
    	);

    	//load current field behaviours...
    	$objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);

    	//check if behaviour is being reconfigured
    	if (is_numeric($this->params()->fromQuery("behaviour_id", "")))
    	{
    		$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviour($this->params()->fromQuery("behaviour_id"));
    	} else {
    		$objBehaviour = FALSE;
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			//reload the form
    			$arr_params = $form->getData();
    			$arr_params["behaviour"] = "reg_status";
    			$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_params);

    			//check if a local defined form exists for the behaviour, sometime needed since the api wont render the form correctly
    			$class = "\\FrontBehavioursConfig\\Forms\\Statuses\\Behaviour" . str_replace(" ", "", ucwords(str_replace("_", " ", $arr_params['beh_action']))) . "Form";
    			
    			if (class_exists($class))
    			{
    				$form = new $class($form);
    			}//end if
    			
    			//set behaviour action param for view
    			$arr_behaviour_params["beh_action"] = $arr_params["beh_action"];

    			//assign data to form is behaviour is being reconfigured
    			if ($objBehaviour instanceof \FrontBehaviours\Entities\FrontBehavioursBehaviourConfigEntity)
    			{
    				$form->bind($objBehaviour);
    			}//end if

    			//check if submitted form is the complete behaviour config
    			if ($this->params()->fromPost("setup_complete", 0) == 1)
    			{
    				//revalidate the form
    				$form->setData($request->getPost());
    				if ($form->isValid())
    				{
    					if ($objBehaviour === FALSE)
    					{
    						//set additional params
    						$arr_form_data = $form->getData();
    						$arr_form_data["reg_status_id"] = $this->params()->fromRoute("id");

    						//create/update the behaviour
    						$objBehaviour = $this->getFrontBehavioursModel()->createBehaviourAction($arr_form_data);

    						//redirect back to the "index" view
    						return $this->redirect()->toUrl($this->url()->fromRoute("front-statuses", array("action" => "status-behaviours", "id" => $this->params()->fromRoute("id"))));
    					} else {
    						//set additional params
    						$objBehaviour = $form->getData();
    						$objBehaviour->set("reg_status_id", $this->params()->fromRoute("id"));

    						//update the behaviour
    						$objBehaviour = $this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);

    						//redirect back to the "index" view
    						return $this->redirect()->toUrl($this->url()->fromRoute("front-statuses", array("action" => "status-behaviours", "id" => $this->params()->fromRoute("id"))));
    					}//end if
    				}//end if
    			}//end if
    		}//end if
    	}//end if

    	$viewModel = new ViewModel(array(
    			//form to add behavours
    			"form"      			=> $form,
    			//existing behaviours
    			"objBehaviours" 		=> $objBehaviours,
    			//behaviour params
    			"arr_behaviour_params" 	=> $arr_behaviour_params,
    			//action descriptions
    			"arr_descriptors" 		=> $arr_descriptors,
    			//set header
    			"behaviours_header" 	=> "Behaviours configured for <span class=\"text-info\">Status</span>",
    	));
    	$viewModel->setTemplate('front-behaviours-config/index/configure-behaviours.phtml');

    	return $viewModel;
    }//end function

    /**
     * Creates an instance of Contact Statuses Model using the Service Manager\locator (SM\L).
     * @return \FrontStatuses\Models\FrontContactStatusesModel
     */
    public function getContactStatusesModel()
    {
    	if (!$this->model_contact_statuses)
    	{
    		$this->model_contact_statuses = $this->getServiceLocator()->get("FrontStatuses\Models\FrontContactStatusesModel");
    	}
    	return $this->model_contact_statuses;
    } // end function

    /**
     * Create an instance of the Front Behaviours Config Model using the Service Manager
     * @return \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
     */
    private function getFrontBehavioursModel()
    {
    	if (!$this->model_front_behaviours_config)
    	{
    		$this->model_front_behaviours_config = $this->getServiceLocator()->get("FrontBehavioursConfig\Models\FrontBehavioursConfigModel");
    	}//end if

    	return $this->model_front_behaviours_config;
    }//end function
} // end class
