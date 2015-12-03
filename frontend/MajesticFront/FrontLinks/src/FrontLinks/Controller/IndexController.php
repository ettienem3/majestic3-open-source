<?php
namespace FrontLinks\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
	/**
	 * Container for Links Model instance
	 * @var \FrontLinks\Models\FrontLinksModel
	 */
	private $model_links;

	/**
	 * Container for the Front Behaviours Config Model
	 * @var \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
	 */
	private $model_front_behaviours_config;

    public function indexAction()
    {
     	//load links
     	$objLinks = $this->getLinksModel()->fetchLinks($this->params()->fromQuery());
     	return array("objLinks" => $objLinks);
    }//end function

    /**
     * Create a new link
     * @return multitype:\Zend\Form\Form
     */
    public function createAction()
    {
		$form = $this->getLinksModel()->getLinksForm();
		$request = $this->getRequest();
		if ($request->isPost())
		{
			//set form data
			$form->setData($request->getPost());

			if ($form->isValid())
			{
				try {
					//create the link
					$objLink = $this->getLinksModel()->createLink($form->getData());

					//set success message
					$this->flashMessenger()->addSuccessMessage("Link Created");

					//redirect to index page
					return $this->redirect()->toRoute("front-links");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end function
		}//end if

		return array("form" => $form);
    }//end function

    /**
     * Update an existing link
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Zend\Form\Form
     */
    public function editAction()
    {
    	//get id from route
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set message
    		$this->flashMessenger()->addErrorMessage("Link could not be loaded. Id is not set");
    		return $this->redirect()->toRoute("front-links");
    	}//end if

    	//load the link details
    	$objLink = $this->getLinksModel()->fetchLink($id);

    	//load the form
    	$form = $this->getLinksModel()->getLinksForm();
		//bind data
		$form->bind($objLink);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			//set form data
			$form->setData($request->getPost());

			if ($form->isValid())
			{
				try {
					$objLink = $form->getData();
					$objLink->set("id", $id);
// $this->getLinksModel()->setDelayedProcessingFlag(TRUE);
					$objLink = $this->getLinksModel()->updateLink($objLink);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Link Updated");
					return $this->redirect()->toRoute("front-links");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end if
			}//end if
		}//end if

    	return array(
    			"form" => $form,
    			"objLink" => $objLink,
    	);
    }//end function

    /**
     * Delete and existing link
     */
    public function deleteAction()
    {
		$id = $this->params()->fromRoute("id", "");

		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Link could not be deleted. Id is not set");
			//return to index page
			return $this->redirect()->toRoute("front-links");
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{
				//delete the link
				try {
					$objLink = $this->getLinksModel()->deleteLink($id);

					//set message
					$this->flashMessenger()->addSuccessMessage("Link deleted");
				} catch (\Exception $e) {
					//set error message
    				$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
				}//end catch
			}//end if

			//redirect to index page
			return $this->redirect()->toRoute("front-links");
		}//end if

		//load data
		$objLink = $this->getLinksModel()->fetchLink($id);

		return array(
			"objLink" => $objLink,
		);
    }//end function

    /**
     * Activate or deactivate a link
     */
    public function statusAction()
    {
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Link could not be updated. Id is not set");
    		//return to index page
    		return $this->redirect()->toRoute("front-links");
    	}//end if

    	try {
    		//load the link details
    		$objLink = $this->getLinksModel()->fetchLink($id);
    		$objLink->set("active", (1 - $objLink->get("active")));

    		//update the link
    		$objLink = $this->getLinksModel()->updateLink($objLink);

    		//set success message
    		$this->flashMessenger()->addSuccessMessage("Link Status Updated");
    	} catch ( \Exception $e) {
    		//set message
    		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
    	}//end if

    	//redirect to index page
    	return $this->redirect()->toRoute("front-links");
    }//end function

    public function linksBehavioursAction()
    {
    	//set layout
    	$this->layout("layout/behaviours-view");

    	//set data array to collect behaviours and pass url data to view
    	$arr_behaviour_params = array(
    			"link_id" => $this->params()->fromRoute("id"),
    			"behaviour" => $this->params()->fromQuery("behaviour", "links"),
    	);

    	//load behaviours form
    	$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm($this->params()->fromQuery("behaviour", "links"), $arr_behaviour_params);
    	$form = $arr_config_form_data["form"];
    	$arr_descriptors = $arr_config_form_data["arr_descriptors"];

    	//load current field behaviours...
    	$objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);

    	//load the link details
    	$objLink = $this->getLinksModel()->fetchLink($this->params()->fromRoute("id"));

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
    			$arr_params = $form->getData();
    			$arr_params["behaviour"] = $this->params()->fromQuery("behaviour", "links");
    			$arr_behaviour_params["beh_action"] = $arr_params["beh_action"];

    			$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_params);

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
    						$arr_form_data["link_id"] = $objLink->get("id");

    						//create/update the behaviour
    						$objBehaviour = $this->getFrontBehavioursModel()->createBehaviourAction($arr_form_data);

    						//redirect back to the "index" view
    						return $this->redirect()->toUrl($this->url()->fromRoute("front-links", array("action" => "links-behaviours", "id" => $objLink->get("id"))));
    					} else {
    						//set additional params
    						$objBehaviour = $form->getData();
    						$objBehaviour->set("form_id", $this->params()->fromRoute("id"));

    						//update the behaviour
    						$objBehaviour = $this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);

    						//redirect back to the "index" view
    						return $this->redirect()->toUrl($this->url()->fromRoute("front-links", array("action" => "links-behaviours", "id" => $objLink->get("id"))));
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
    			//load form data
    			"objLink"				=> $objLink,
    			//set header
    			"behaviours_header" 	=> "Behaviours configured for <span class=\"text-info\">" . $objLink->get("link") . "</span>",
    	));
    	$viewModel->setTemplate('front-behaviours-config/index/configure-behaviours.phtml');

    	return $viewModel;
    }//end function

    /**
     * Create an instance of the links model using the service manager
     * @return \FrontLinks\Models\FrontLinksModel
     */
    private function getLinksModel()
    {
    	if (!$this->model_links)
    	{
    		$this->model_links = $this->getServiceLocator()->get("FrontLinks\Models\FrontLinksModel");
    	}//end if

    	return $this->model_links;
    }//end function

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
}//end class
