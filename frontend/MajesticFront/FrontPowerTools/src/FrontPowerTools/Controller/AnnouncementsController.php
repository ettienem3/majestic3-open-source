<?php
namespace FrontPowerTools\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class AnnouncementsController extends AbstractActionController
{
	/**
	 * Container for the PowerTools instance
	 * @var \FrontPowerTools\Models\FrontAnnouncementsModel
	 */
	private $model_forms_power_tools;

    public function indexAction()
    {
        //load Announcements
        $objAnnouncements = $this->getAnnouncementsModel()->fetchAnnouncements();
        return array("objAnnouncements" => $objAnnouncements);
    }//end function

    /**
     * Create a new Announcement
     * @return multitype: \Zend\Form\Form
     */
    public function createAction()
    {
    	$form = $this->getAnnouncementsModel()->getAnnouncementForm();
    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		//set form data
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			try {
    				//create the Announcement
    				$objPowerTool = $this->getAnnouncementsModel()->createAnnouncement($form->getData());
    				
    				//set success message
    				$this->flashMessenger()->addSuccessMessage("Announcement created");
    				
    				//redirect to index page
    				return $this->redirect()->toRoute("front-power-tools/announcements");
    			} catch (\Exception $e) {
    				//set error message
    				$form->get("front-power-tools/announcement")->setMessages(array($e->getMessage()));
    			}//end catch
    		}//end if
    	}//end if
    	
    	return array("form" => $form);
    }//end function

    /**
     * Update an Existing announcement
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Zend\Form\Form
     */
    public function editAction()
    {
    	//get id from route
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Announcement could not be loaded. Id not set");
    		return $this->redirect()->toRoute("front-power-tools/announcements");
    	}//end if

    	//load the announcement details
    	$objAnnouncement = $this->getAnnouncementsModel()->fetchAnnouncement($id);

    	//load the form
    	$form = $this->getAnnouncementsModel()->getAnnouncementForm();

    	//bind the data
    	$form->bind($objAnnouncement);

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		// set form data
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			try {
    				$objAnnouncement = $form->getData();
    				//set annoucement id from route
    				$objAnnouncement->set("id", $id);
    				$objAnnouncement = $this->getAnnouncementsModel()->updateAnnouncement($objAnnouncement);

    				//set the success message
    				$this->flashMessenger()->addSuccessMessage("Announcement Update");
    			}//end try
    			catch (\Exception $e) {
    				//set message
    				$this->flashMessenger()->addErrorMessage($e->getMessage());
    			}//end if
    		}//end if

			//redirect to the index page
    		return $this->redirect()->toRoute("front-power-tools/announcements");
    	}//end if

    	return array(
    			"form" => $form,
    			"objAnnouncement" => $objAnnouncement,
    	);
    }//end function

    /**
     * Delete an Existing Announcement
     */
    public function deleteAction()
    {
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set the rror message
    		$this->flashMessenger()->addErrorMessage("Announcement could not be deleted. Id not set");

    		//return to index page
    		return $this->redirect()->toRoute("front-power-tools/announcements");
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		if (strtolower($request->getPost("delete")) == "yes")
    		{
    			//delete the announcement
    			try {
    				$objPowerTool = $this->getAnnouncementsModel()->deleteAnnouncement($id);
    			
    				//set message
    				$this->flashMessenger()->addSuccessMessage("Announcement Deleted");
    			} catch (\Exception $e) {
    				//set error message
    				$this->flashMessenger()->addErrorMessage($e->getMessage());
    			}//end catch
    		}//end if
    		
    		//redirect to indexpage
    		return $this->redirect()->toRoute("front-power-tools/announcements");
    	}//end if

    	return array();
    }//end function

    /**
     * Activate or deactivate a Announcement
     */
    public function statusAction()
    {
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		// set the error message
    		$this->flashMessenger()->addErrorMessage("The Announcement could not be deleted. Id not set");

    		//return to index page
    		return $this->redirect()->toRoute("front-power-tools/announcements");
    	}//end if

    	try {
    		//load the announcement details
    		$objAnnouncement = $this->getAnnouncementsModel()->fetchAnnouncement($id);
    		$objAnnouncement->set("active", (1 - $objAnnouncement->get("active")));

    		//update the announcement
    		$objAnnouncement = $this->getAnnouncementsModel()->updateAnnouncement($objAnnouncement);

    		//set the success message
    		$this->flashMessenger()->addSuccessMessage("Announcement Status Updated");
    	} catch (\Exception $e) {
    		//set erroe message
    		$this->flashMessenger()->addErrorMessage($e->getMessage());
    	}//end if

    	//redirect to index page
    	return $this->redirect()->toRoute("front-power-tools/announcements");
    }//end function

    /**
     * Create an instance of the PowerToolsModel using the service manager
     * @return \FrontPowerTools\Models\FrontAnnouncementsModel
     */
    private function getAnnouncementsModel()
    {
    	if (!$this->model_power_tools)
    	{
    		$this->model_power_tools = $this->getServiceLocator()->get("FrontPowerTools\Models\FrontAnnouncementsModel");
    	}//end if

    	return $this->model_power_tools;
    }//end function
}//end class
