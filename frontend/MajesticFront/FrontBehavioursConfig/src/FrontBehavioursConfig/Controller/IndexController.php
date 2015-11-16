<?php
namespace FrontBehavioursConfig\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
	/**
	 * Container for the Front Behaviours Config Model
	 * @var \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
	 */
	private $model_front_behaviours_config;

	public function editBehaviourAction()
	{
		//set layout
		$this->layout("layout/behaviours-view");

		//set behaviour id
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			echo "Behaviour could not be loaded. Required parameters are not available";
			exit;
		}//end if

		try {
			//load behaviour details
			$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviourAction($id);
		} catch (\Exception $e) {
			echo $e->getMessage() . " : " . $e->getPrevious();
			exit;
		}//end catch

		//load the behaviour config form
		$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm(array(
			"behaviour" => $objBehaviour->get("behaviour"),
			"beh_action" => $objBehaviour->get("action"),
		));

		//bind data to the form
		$form->bind($objBehaviour);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				//update the behaviour action
				try {
					//extract form data
					$objBehaviour = $form->getData();
					//set id
					$objBehaviour->set("id", $id);

					$this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);

					//redirect back to the calling url
					return $this->redirect()->toUrl($this->params()->fromQuery("redirect_url"));
				} catch (\Exception $e) {
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end exception
			}//end if
		}//end if

		//set redirect url for post form submit redirect
		if ($this->params()->fromQuery("redirect_url", "") == "")
		{
			$redirect_url = $this->getRequest()->getServer('HTTP_REFERER');
		} else {
			$redirect_url = $this->params()->fromQuery("redirect_url");
		}//end if

		return array(
			"form" => $form,
			"id" => $id,
			"redirect_url" => $redirect_url,
		);
	}//end function

	public function deleteBehaviourAction()
	{
		//set layout
		$this->layout("layout/behaviours-view");

		//set behaviour id
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			echo "Behaviour could not be loaded. Required parameters are not available";
			exit;
		}//end if

		try {
			//load behaviour details
			$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviourAction($id);
		} catch (\Exception $e) {
			echo $e->getMessage() . " : " . $e->getPrevious();
			exit;
		}//end catch

		$notifications = FALSE;

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if ($request->getPost("delete") == "Yes")
			{
				//delete the behaviour
				try {
					$this->getFrontBehavioursModel()->deleteBehaviourAction($objBehaviour);

					//redirect back to specified route
					return $this->redirect()->toUrl($this->params()->fromQuery("redirect_url"));
				} catch (\Exception $e) {
					$notifications = $e->getMessage() . " : " . $e->getPrevious();
				}//end catch
			} else {
				//redirect back to specified route
				return $this->redirect()->toUrl($this->params()->fromQuery("redirect_url"));
			}//end if
		}//end if

		//set redirect url for post form submit redirect
		if ($this->params()->fromQuery("redirect_url", "") == "")
		{
			$redirect_url = $this->getRequest()->getServer('HTTP_REFERER');
		} else {
			$redirect_url = $this->params()->fromQuery("redirect_url");
		}//end if


		return array(
			"notifications" => $notifications,
			"arr_behaviour_params" => $this->params()->fromQuery(),
			"redirect_url" => $redirect_url,
			"id" => $id,
		);
	}//end function

	public function setBehaviourStatusAction()
	{
		//set behaviour id
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			echo "Behaviour could not be loaded. Required parameters are not available";
			exit;
		}//end if

		try {
			//load behaviour details
			$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviourAction($id);
		} catch (\Exception $e) {
			echo $e->getMessage() . " : " . $e->getPrevious();
			exit;
		}//end catch

		//update the behaviour
		$objBehaviour->set("active", (1 - $objBehaviour->get("active")));
		$objBehaviour->set("beh_action", $objBehaviour->get("action")); //add values to the object to make form validation work
		$this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);

		return $this->redirect()->toUrl($this->getRequest()->getServer('HTTP_REFERER'));
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
