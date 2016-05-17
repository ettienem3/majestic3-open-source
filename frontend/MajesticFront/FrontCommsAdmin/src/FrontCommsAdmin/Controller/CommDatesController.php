<?php
namespace FrontCommsAdmin\Controller;

use FrontCore\Adapters\AbstractCoreActionController;

class CommDatesController extends AbstractCoreActionController
{
	/**
	 * Container for the Commdates Model instance
	 * @var \FrontCommsAdmin\Models\FrontCommDatesModel
	 */
	private $model_commdates;

    public function indexAction()
    {
    	//load the commdates
    	$objCommDates = $this->getCommDatesModel()->fetchCommDates($this->params()->fromQuery());
    	return array("objCommDates" => $objCommDates);
    }//end function

    /**
     * Create a new Commdate
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Zend\Form\Form
     */
    public function createAction()
    {
    	$form = $this->getCommDatesModel()->getCommDatesForm();

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		//set the form data
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			try {
    				//create the Commdate
    				$objCommDate = $this->getCommDatesModel()->createCommDate($form->getData());

    				//set success message
    				$this->flashMessenger()->addSuccessMessage("Comm Date Created");

    				//redirect to index page
    				return $this->redirect()->toRoute("front-comms-admin/dates");
    			} catch (\Exeption $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			}//end catch
    		}//end if
    	}//end if

    	return array("form" => $form);
    }//end function

    /**
     * Update an Existing commdate
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Zend\Form\Form
     */
    public function editAction()
    {
    	//get the id
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set the message return to index page
    		$this->flashMessenger()->addErrorMessage("Comm Date could not be loaded. Id is not set");
    		//redirect to index page
    		return $this->redirect()->toRoute("front-comms-admin/dates");
    	}//end if

    	//load the commdates details
    	$objCommDate = $this->getCommDatesModel()->fetchCommDate($id);

    	//load the form
    	$form = $this->getCommDatesModel()->getCommDatesForm();
    	//bind the data
    	$form->bind($objCommDate);

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		//set the form data
 			$form->setData($request->getPost());

 			if ($form->isValid())
 			{
 				try {
 					$objCommDate = $form->getData();
 					//set id from route
 					$objCommDate->set("id", $id);
 					$objCommDate = $this->getCommDatesModel()->updateCommDate($objCommDate);

 					//set success message
 					$this->flashMessenger()->addSuccessMessage("Comm Date Updated");

 					//redirect to index page
 					return $this->redirect()->toRoute("front-comms-admin/dates");
 				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
 				}//end catch
 			}//end if
    	}//end if

    	return array(
    			"form" => $form,
    			"objCommDate" => $objCommDate,
    	);
    }//end function

    /**
     * Delete an Existing Commdate
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function deleteAction()
    {
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Comm Date could not be deleted. Id not set");
    		//return to index page
    		return $this->redirect()->toRoute("front-comms-admin/dates");
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		if (strtolower($request->getPost("delete")) == "yes")
    		{
    			//delete the commdate
    			try {
    				$objCommDate = $this->getCommdatesModel()->deleteCommDate($id);

    				//set the message
    				$this->flashMessenger()->addSuccessMessage("Comm Date deleted");
    			} catch (\Exeption $e) {
    				$this->flashMessenger()->addErrorMessage($e->getMessage());
    			}//end catch
    		}//end if

    		//redirect to index page
    		return $this->redirect()->toRoute("front-comms-admin/dates");
    	}//end if

		//load data
		$objCommDate = $this->getCommDatesModel()->fetchCommDate($id);
		return array(
			"objCommDate" => $objCommDate,
		);
    }//end function

    /**
     * Activate or Deactivate a Comm date
     */
    public function statusAction()
    {
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Comm Date could not be Activated. Id not set");

    		//return to index page
    		return $this->redirect()->toRoute("front-comms-admin/dates");
    	}//end if

    	try {
    		//load the Comm date details
    		$objCommDate = $this->getCommDatesModel()->fetchCommDate($id);
    		$objCommDate->set("active", (1 - $objCommDate->get("active")));

    		//update the Commdate
    		$objCommDate = $this->getCommDatesModel()->updateCommDate($objCommDate);

    		//set the success message
    		$this->flashMessenger()->addSuccessMessage("Comm Date Status update");
    	} catch (\Exeption $e) {
    		//set Message
    		$this->flashMessenger()->addErrorMessage($e->getMessage());
    	}//end if

    	//redirect to the index page
    	return $this->redirect()->toRoute("front-comms-admin/dates");
    }//end function

    /**
     * Create an instance of the commdates model using the service manager
     * @return \FrontCommsAdmin\Models\FrontCommDatesModel
     */
	private function getCommDatesModel()
	{
		if (!$this->model_commdates)
		{
			$this->model_commdates = $this->getServiceLocator()->get("FrontCommsAdmin\Models\FrontCommDatesModel");
		}//end if

		return $this->model_commdates;
	}//end function

}//end class
