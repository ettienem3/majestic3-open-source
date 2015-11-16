<?php
namespace FrontCommsSmsCampaigns\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
	/**
	 * Container for FrontCommsSmsCampaignsModel instance
	 * @var \FrontCommsSmsCampaigns\Models\FrontCommsSmsCampaignsModel
	 */
	private $model_comms_sms_campaigns;

	/**
	 * Container for the Front Behaviours Config Model
	 * @var \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
	 */
	private $model_front_behaviours_config;

	public function indexAction()
	{
		//load data
		$objCommsSmsCampaigns = $this->getCommsSmsCampaignsModel()->fetchSmsCampaigns($this->params()->fromQuery());

		return array("objCommsSmsCampaigns" => $objCommsSmsCampaigns);
	} // end function

	/**
	 * Create a new Sms Campaign
	 * @return multitype:\Zend\Form\Form
	 */
	public function createAction()
	{
		// Instantiate a CommsSmsCampaign form
		$form = $this->getCommsSmsCampaignsModel()->getSmsCampaignSystemForm();

		// HTTP request
		$request = $this->getRequest();

		if ($request->isPost()){
			// Populate data into CommsSmsCampaign form.
			$form->setData($request->getPost());
			if ($form->isValid()) {
				try {
					// Create CommsSmsCampaign object.
					$objCommsSmsCampaign = $this->getCommsSmsCampaignsModel()->createCommsSmsCampaign($form->getData());

					// Set successful message.
					$this->flashMessenger()->addSuccessMessage("SMS Campaign created successfully");

					// Redirect to index page.
					return $this->redirect()->toRoute("front-comms-sms-campaigns");
				} catch (\Exception $e) {

					// Set unssuccessful message.
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				} //end try
			} // end if
		} // end if

		return array("form" => $form);
	} // end function


	/**
	 * Update an existing Sms Campaign
	 * @return multitype:\Zend\Form\Form
	 */
	public function editAction()
	{
		// get ID from route
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			// Set unsuccessful message
			$this->flashMessenger()->addErrorMessage("CommsSmsCampaign could not be loaded. ID is not set.");

			//redirect to sms campaings index page
			return $this->redirect()->toRoute("front-comms-sms-campaigns");
		}//end if

		// load the CommsSmsCampaign details
		$objCommsSmsCampaign = $this->getCommsSmsCampaignsModel()->fetchSmsCampaign($id);

		// Instantiate the form
		$form = $this->getCommsSmsCampaignsModel()->getSmsCampaignSystemForm();

		// Populate data into the form.
		$form->bind($objCommsSmsCampaign);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			// Prepares specific CommsSmsCampaign.ID object.
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				try {
					$objCommsSmsCampaign = $form->getData();

					//set id from route
					$objCommsSmsCampaign->set("id", $id);

					// Update the CommsSmsCampaign
					$objCommsSmsCampaign = $this->getCommsSmsCampaignsModel()->updateCommsSmsCampaign($objCommsSmsCampaign);

					// Set successful message
					$this->flashMessenger()->addSuccessMessage("Sms Campaign updated successfully");

					// Redirect to index page
					return $this->redirect()->toRoute("front-comms-sms-campaigns");
				} catch (\Exception $e) {
					// Set unssuccessful message.
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				} // end try
			} // end if
		} // end if

		// Load CommsSmsCampaign form.
		return array(
				"form" => $form,
				"objCommsSmsCampaign"=> $objCommsSmsCampaign,
		);
	} // end function

	/**
	 * Delete existing Sms Campaign
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	public function deleteAction()
	{
		// get the ID
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			// Set error message
			$this->flashMessenger()->addErrorMessage("Sms Campaign could not be deleted. ID is not set.");

			// Return to index page.
			return $this->redirect()->toRoute("front-comms-sms-campaigns");
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{
				try {
					//delete the sms campaign
					$objCommsSmsCampaign = $this->getCommsSmsCampaignsModel()->deleteCommsSmsCampaign($id);

					// Set successful message
					$this->flashMessenger()->addSuccessMessage("SMS Campaign deleted successfully");
				} catch (\Exception $e) {
					// Set unssuccessful message.
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				} // end try
			}//end if

			//redirect back to the sms campaign index page
			return $this->redirect()->toRoute("front-comms-sms-campaigns");
		}//end if

		//load data
		$objCommsSmsCampaign = $this->getCommsSmsCampaignsModel()->fetchSmsCampaign($id);

		return array(
			"objCommsSmsCampaign" => $objCommsSmsCampaign,
		);
	} // end function


	/**
	 * UPDATE active column Active/Inactive
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	public function statusAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			// Set ID unsuccessful message.
			$this->flashMessenger()->addErrorMessage("Sms Campaign Active status could not be set. ID is not set.");

			// Return to the index page.
			return $this->redirect()->toRoute("front-comms-sms-campaigns");
		}//end if

		try {
			//Load the sms campaign
			$objCommsSmsCampaign = $this->getCommsSmscampaignsModel()->fetchSmsCampaign($id);

			//set data
			$objCommsSmsCampaign->set("active", (1 - $objCommsSmsCampaign->get("active")));

			// Update Sms Campaign
			$objCommsSmsCampaign = $this->getCommsSmsCampaignsModel()->updateCommsSmsCampaign($objCommsSmsCampaign);

			// Set successful message
			$this->flashMessenger()->addSuccessMessage("Sms Campaign Active Status updated successfully");
		} catch (\Exception $e) {
			// Set unsuccessful message
			$this->flashMessenger()->addErrorMessage($e->getMessage());
		} // end try

		// redirect to index page
		return $this->redirect()->toRoute("front-comms-sms-campaigns");
	} // end function

	public function smsCampaignBehavioursAction()
	{
		//set layout
		$this->layout("layout/behaviours-view");

		//load behaviours form
		$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm("sms_campaign");
		$form = $arr_config_form_data["form"];
		$arr_descriptors = $arr_config_form_data["arr_descriptors"];

		//set data array to collect behaviours and pass url data to view
		$arr_behaviour_params = array(
				"sms_campaign_id" => $this->params()->fromRoute("id"),
				"behaviour" => "sms_campaign",
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
				$arr_params["behaviour"] = "sms_campaign";
				$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_params);

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
							$arr_form_data["sms_campaign_id"] = $this->params()->fromRoute("id");

							//create/update the behaviour
							$objBehaviour = $this->getFrontBehavioursModel()->createBehaviourAction($arr_form_data);

							//redirect back to the "index" view
							return $this->redirect()->toUrl($this->url()->fromRoute("front-comms-sms-campaigns", array("action" => "sms-campaign-behaviours", "id" => $this->params()->fromRoute("id"))));
						} else {
							//set additional params
							$objBehaviour = $form->getData();
							$objBehaviour->set("sms_campaign_id", $this->params()->fromRoute("id"));

							//update the behaviour
							$objBehaviour = $this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);

							//redirect back to the "index" view
							return $this->redirect()->toUrl($this->url()->fromRoute("front-comms-sms-campaigns", array("action" => "sms-campaign-behaviours", "id" => $this->params()->fromRoute("id"))));
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
				"behaviours_header" 	=> "Behaviours configured for <span class=\"text-info\">Campaign</span>",
		));
		$viewModel->setTemplate('front-behaviours-config/index/configure-behaviours.phtml');

		return $viewModel;
	}//end function

	/**
	 * Create an instance of the FrontCommsSmsCampaignsModel using the service manager.
	 * @return \FrontCommsSmsCampaigns\Models\FrontCommsSmsCampaignsModel
	 */
	private function getCommsSmsCampaignsModel()
	{
		if (!$this->model_comms_sms_campaigns )
		{
			$this->model_comms_sms_campaigns = $this->getServiceLocator()->get("FrontCommsSmsCampaigns\Models\FrontCommsSmsCampaignsModel");
		}//end function

		return $this->model_comms_sms_campaigns;
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
