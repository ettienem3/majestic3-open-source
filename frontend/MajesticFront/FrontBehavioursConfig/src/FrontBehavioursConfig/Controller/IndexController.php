<?php
namespace FrontBehavioursConfig\Controller;

use FrontCore\Adapters\AbstractCoreActionController;

class IndexController extends AbstractCoreActionController
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

		//set form parameters
		$arr_form_params = $this->setBehaviourRequestFormParams($objBehaviour);

		//load the behaviour config form
		$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_form_params);

		//check if a local defined form exists for the behaviour, sometime needed since the api wont render the form correctly
		switch ($objBehaviour->get('behaviour'))
		{
			case "__journey":
				$class = "\\FrontBehavioursConfig\\Forms\\Journeys\\Behaviour" . str_replace(" ", "", ucwords(str_replace("_", " ", $objBehaviour->get('action')))) . "Form";
				break;

			case "__form":
				$class = "\\FrontBehavioursConfig\\Forms\\Forms\\Behaviour" . str_replace(" ", "", ucwords(str_replace("_", " ", $objBehaviour->get('action')))) . "Form";
				break;

			case "__form_fields":
				$class = "\\FrontBehavioursConfig\\Forms\\FormFields\\Behaviour" . str_replace(" ", "", ucwords(str_replace("_", " ", $objBehaviour->get('action')))) . "Form";
				break;

			case "__reg_status":
				$class = "\\FrontBehavioursConfig\\Forms\\Statuses\\Behaviour" . str_replace(" ", "", ucwords(str_replace("_", " ", $objBehaviour->get('action')))) . "Form";
				break;

			case "__links":
				$class = "\\FrontBehavioursConfig\\Forms\\Links\\Behaviour" . str_replace(" ", "", ucwords(str_replace("_", " ", $objBehaviour->get('action')))) . "Form";
				break;
		}//end switch

		if (isset($class) && class_exists($class))
		{
			$form = new $class($form);
		}//end if

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

					//submit changes
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
			"objBehaviour" => $objBehaviour,
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
	 * Set behaviour parameters where required based on the type if request received
	 * @param unknown $objBehaviour
	 * @return array
	 */
	private function setBehaviourRequestFormParams($objBehaviour)
	{
		$arr_params = array(
				'behaviour' => $objBehaviour->get('behaviour'),
				'beh_action' => $objBehaviour->get('action'),
		);

		//set additional params based on behaviour and its action where applicable
		switch(strtolower(str_replace("_", "", $arr_params['behaviour'])))
		{
			case "formfields":
				$arr_params['form_id'] = $objBehaviour->get('fk_form_id');
				$arr_params['field_id'] = $objBehaviour->get('fk_fields_all_id');
				break;
		}//end switch

		return $arr_params;
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
