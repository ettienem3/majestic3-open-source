<?php
namespace FrontCommsTemplates\Controller;

use FrontCore\Adapters\AbstractCoreActionController;

class IndexController extends AbstractCoreActionController
{
	/**
	 * Container for Comms Templates Model instance
	 * @var \FrontCommsTemplates\Models\FrontCommsTemplatesModel
	 */
	private $model_commstemplates;

	/**
	 * Load a list of existing Comm Templates using the api
	 * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
	 */
    public function indexAction()
    {
     	// get indexAction ViewModel
     	$objCommsTemplates = $this->getCommsTemplatesModel()->getCommsTemplates($this->params()->fromQuery());
     	return array("objCommsTemplates" => $objCommsTemplates);
    } // end function

    /**
     * Create a new Comm Template
     * @return multitype:\Zend\Form\Form
     */
    public function createAction()
    {
    	$form = $this->getCommsTemplatesModel()->getCommsTemplatesForm();
    	$request = $this->getRequest();

    	//set default content
    	$form->get("content")->setValue("#content");

    	if ($request->isPost())
    	{
    		// populate data into form
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			try {
    				// Insert row of data to specific table (commtemplate)
    				$objCommTemplate = $this->getCommsTemplatesModel()->createCommTemplate($form->getData());

    				// set success message
    				$this->flashMessenger()->addSuccessMessage("Comm Template Created");

    				//set success message
    				return $this->redirect()->toRoute("front-comms-templates");
    			} catch (\Exception $e) {
    				//set error message
    				$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			}// end catch
    		}// end function
    	}// end if

    	return array(
    			"form" => $form,
    		);
    } // end createAction()


    /**
     * Update an existing Comm Template
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Zend\Form\Form
     */
    public function editAction()
    {
    	// Get ID from route to return specific row of data.
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		// Set ID unsuccessful message
    		$this->flashMessenger()->addErrorMessage("Comm Template could not be loaded. Id is not set");
    		return $this->redirect()->toRoute("front-comms-templates");
    	} // end if

    	// Get Comm Template specific row of data details
    	$objCommTemplate = $this->getCommsTemplatesModel()->getCommTemplate($id);

    	// Instantiate form
    	$form = $this->getCommsTemplatesModel()->getCommsTemplatesForm();

    	// Populate data into the form
    	$form->bind($objCommTemplate);

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		// Prepares ID specific raw of data to be captured into the DB.
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			try {
    				$objCommTemplate = $form->getData();
    				$objCommTemplate->set("id", $id);
    				$objCommTemplate = $this->getCommsTemplatesModel()->updateCommTemplate($objCommTemplate);

    				// Set successful message
    				$this->flashMessenger()->addSuccessMessage("Comm Template Updated");

    				//redirect to index
    				return $this->redirect()->toRoute("front-comms-templates");
    			} catch ( \Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			} // end if
    		} // end if
    	} // end if

    	return array(
    			"form" => $form,
    			"objCommTemplate" => $objCommTemplate,
    	);
    } // end editAction($id)


    /**
     * Delete existing Comm Template
     */
    public function deleteAction()
    {
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		// Set ID unsuccessful message
    		$this->flashMessenger()->addErrorMessage("Comm Template could not be deleted. Id is not set");
    		// Return to index page
    		return $this->redirect()->toRoute("front-comms-templates");
    	}// end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		if (strtolower($request->getPost("delete")) == "yes")
    		{
    			// Delete Comm Template specific row of data
    			try {
    				$objCommTemplate = $this->getCommsTemplatesModel()->deleteCommTemplate($id);

    				// Set successful message
    				$this->flashMessenger()->addSuccessMessage("Comm Template deleted");
    			} catch (\Exception $e) {
    				// Set unsuccessful message
    				$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
    			}// end catch
    		}//end if

    		// Redirect to index page
      		return $this->redirect()->toRoute("front-comms-templates");
    	}//end if

		//load data
		$objCommTemplate = $this->getCommsTemplatesModel()->getCommTemplate($id);
		return array(
			"objCommTemplate" => $objCommTemplate,
		);
    } // end deleteAction()


    /**
     * Activate or deactivate a Comm Template
     */
    public function statusAction()
    {
    	$id = $this->params()->fromRoute("id", "");
    	if ($id == "")
    	{
    		// Set ID unsuccessful message
    		$this->flashMessenger()->addErrorMessage("Comm Template could not be de-actived. Id is not set");
    		// Return to index page
    		return $this->redirect()->toRoute("front-comms-templates");
    	} // end if

    	try {
    		// Instatiate specific Comm Template specific row of data details
    		$objCommTemplate = $this->getCommsTemplatesModel()->getCommTemplate($id);
    		$objCommTemplate->set("active", (1 - $objCommTemplate->get("active")));

    		// Update Comm Template specific row of data details
    		$objCommTemplate = $this->getCommsTemplatesModel()->updateCommTemplate($objCommTemplate);

    		// Set Successful message
    		$this->flashMessenger()->addSuccessMessage("Comm Template Status updated");
    	} catch ( \Exception $e) {
    		// Set Unsuccessfull message
    		$this->flashMessenger()->addErrorMessage($e->getMessage());
    	}// end if

    	// Redirect to index page
    	return $this->redirect()->toRoute("front-comms-templates");
    } // end statusAction()


    /**
     * Create an instance of the commstemplates model using the service manager
     * @return \FrontCommsTemplates\Models\FrontCommsTemplatesModel
     */
    private function getCommsTemplatesModel()
    {
    	if (!$this->model_commstemplates)
    	{
    		$this->model_commstemplates = $this->getServiceLocator()
    				->get("FrontCommsTemplates\Models\FrontCommsTemplatesModel");
    	} // end if

    	return $this->model_commstemplates;
    } // end getCommsTemplatesModel()
} // end IndexController{}

