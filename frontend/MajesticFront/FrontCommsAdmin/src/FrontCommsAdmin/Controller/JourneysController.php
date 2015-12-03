<?php
namespace FrontCommsAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * @description this is test description
 * @author lodi
 *
 */
class JourneysController extends AbstractActionController
{
	/**
	 * Container for the Journeys Model instance
	 * @var \FrontCommsAdmin\Models\FrontJourneysModel
	 */
	private $model_journeys;

	/**
	 * Container for the Front Behaviours Config Model
	 * @var \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
	 */
	private $model_front_behaviours_config;

	/**
	 * List the Journeys
	 * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
	 */
	public function indexAction()
	{
		$arr_params = (array) $this->params()->fromQuery();
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_params = array_merge($arr_params, (array) $request->getPost());
		}//end foreach

		//laod the journeys
		$objJourneys = $this->getJourneysModel()->fetchJourneys($arr_params, TRUE);

		return array(
				"objJourneys" => $objJourneys,
				"arr_params" => $arr_params,
		);
	}//end function

	/**
	 * Create a new Journey
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\RsponseInteface>|multitype:\Zend\Form\Form
	 */
	public function createAction()
	{
		$form = $this->getJourneysModel()->getJourneysForm();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			//set the form data
			$form->setData($request->getPost());

			if ($form->isValid())
			{
				try {
					//create the journey
					$objJourney = $this->getJourneysModel()->createJourney($form->getData());

					//set the success message
					$this->flashMessenger()->addSuccessMessage("Journey created successfully");

					//redirect to the index page
					return $this->redirect()->toRoute("front-comms-admin/journeys");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		} else {
			//set some default values
			$form->get("user_journey")->setValue(0);
		}//end if

		return array(
				"form" => $form
		);
	}//end function

	/**
	 * Update a Journey
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	public function editAction()
	{
		//get the ID
		$id = $this->params()->fromRoute("id", "");

		if ($id == "")
		{
			//set error message and return to the index page.
			$this->flashMessenger()->addErrorMessage("Journey could not be loaded. Id not set.");

			//redirect to the index page
			return $this->redirect()->toRoute("front-comm-admin/journeys");
		}//end if

		//load the journey details
		$objJourney = $this->getJourneysModel()->fetchJourney($id);

		//load the form
		$form = $this->getJourneysModel()->getJourneysForm();

		//bind the data
		$form->bind($objJourney);

		/**
		 * Perform some data checks
		 */
		if ($form->has("date_expiry"))
		{
			if ($objJourney->get("date_expiry") == "0000-00-00" || $objJourney->get("date_expiry") == "00-00-0000" || $objJourney->get("date_expiry") == "")
			{
				$date = "";
			} else {
				$date = $objJourney->get("date_expiry");
			}//end if

			$form->get("date_expiry")->setValue($date);
		}//end if

		$request = $this->getRequest();
		if ($request->isPost()) {
			//set the form data
			$form->setData($request->getPost());

			if ($form->isValid())
			{
				try {
					$objJourney = $form->getData();
					//set id from route
					$objJourney->set("id", $id);
					$objJourney = $this->getJourneysModel()->updateJourney($objJourney);

					//set the success message
					$this->flashMessenger()->addSuccessMessage("Journey updated successfully");

					//redirect to the index page
					return $this->redirect()->toRoute("front-comms-admin/journeys");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if

		return array(
				"form" => $form,
				"objJourney" => $objJourney,
		);
	}//end function

	/**
	 * Delete a Journey
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	public function deleteAction()
	{
		$id = $this->params()->fromRoute("id", "");

		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Journey could not be deleted. ID not set.");
			//return to the index page
			return $this->redirect()->toRoute("front-comms-admin/journeys");
		}//end if

		//load data
		try {
			$objJourney = $this->getJourneysModel()->fetchJourney($id);
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());

			//redirect to index page
			return  $this->redirect()->toRoute("front-comms-admin/journeys");
		}//end catch

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{
				//delete the Journey
				try {
					$objJourney = $this->getJourneysModel()->deleteJourney($id);

					//set the message
					$this->flashMessenger()->addSuccessMessage("Journey deleted successfully");
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				}//end catch
			}//end if

			//redirect to index page
			return  $this->redirect()->toRoute("front-comms-admin/journeys");
		}//end if

		return array(
			"objJourney" => $objJourney,
		);
	}//end function

	/**
	 * Activate or deactivate a Journey
	 * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
	 */
	public function statusAction()
	{
		$id = $this->params()->fromRoute("id", "");

		if ($id == "")
		{
			//set the error message
			$this->flashMessenger()->addErrorMessage("Journey active status could not be set. Id not set.");

			//return to index page
			return $this->redirect()->toRoute("front-comms-admin/journeys");
		}//end if

		try {
			//load the Jouney details
			$objJourney = $this->getJourneysModel()->fetchJourney($id);
			$objJourney->set("active", (1 - $objJourney->get("active")));

			//update the Journey
			$objJourney = $this->getJourneysModel()->updateJourney($objJourney);

			//set the success message
			$this->flashMessenger()->addSuccessMessage("Journey active status updated");
		} catch (\Exception $e) {
			//set Message
			$this->flashMessenger()->addErrorMessage($e->getMessage());
		}//end if

		//redirect to the index page
		return $this->redirect()->toRoute("front-comms-admin/journeys");
	}//end function

	public function journeyBehavioursAction()
	{
		//set layout
    	$this->layout("layout/behaviours-view");

		//set data array to collect behaviours and pass url data to view
		$arr_behaviour_params = array(
				"journey_id" => $this->params()->fromRoute("id"),
				"behaviour" => "journey",
		);

		//load behaviours form
		$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm("journey", $arr_behaviour_params);

		$form = $arr_config_form_data["form"];

		$arr_descriptors = $arr_config_form_data["arr_descriptors"];

		//load current journey behaviours...
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
				$arr_params["behaviour"] = "journey";
				$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_params);

				//set journey id
				if ($form->has("fk_journey_id"))
				{
					$form->get("fk_journey_id")->setValue($this->params()->fromRoute("id"));
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
							$arr_form_data["journey_id"] = $this->params()->fromRoute("id");

							//create/update the behaviour
							$objBehaviour = $this->getFrontBehavioursModel()->createBehaviourAction($arr_form_data);

							//redirect back to the "index" view
							return $this->redirect()->toUrl($this->url()->fromRoute("front-comms-admin/journeys", array("action" => "journey-behaviours", "id" => $this->params()->fromRoute("id"))));
						} else {
							//set additional params
							$objBehaviour = $form->getData();
							$objBehaviour->set("journey_id", $this->params()->fromRoute("id"));

							//update the behaviour
							$objBehaviour = $this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);

							//redirect back to the "index" view
							return $this->redirect()->toUrl($this->url()->fromRoute("front-comms-admin/journeys", array("action" => "journey-behaviours", "id" => $this->params()->fromRoute("id"))));
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
				"behaviours_header" 	=> "Behaviours configured for <span class=\"text-info\">Journey</span>",
		));
		$viewModel->setTemplate('front-behaviours-config/index/configure-behaviours.phtml');

		return $viewModel;
	}//end function

	public function journeyFlowAction()
	{
		$this->layout("layout/dashboard");

		//get the ID
		$id = $this->params()->fromRoute("id", "");

		if ($id == "")
		{
			//set error message and return to the index page.
			$this->flashMessenger()->addErrorMessage("Journey could not be loaded. Id not set.");

			//redirect to the index page
			return $this->redirect()->toRoute("front-comm-admin/journeys");
		}//end if

		$objData = $this->getJourneysModel()->createJourneyFlowDiagram($id);

		return array(
			"objData" => $objData,
			"journey_id" => $id,
		);
	}//end function

	/**
	 * Create an instance of the Journeys model using the service manager.
	 * @return \FrontCommsAdmin\Models\FrontJourneysModel
	 */
	private function getJourneysModel()
	{
		if (!$this->model_journeys)
		{
			$this->model_journeys = $this->getServiceLocator()->get("FrontCommsAdmin\Models\FrontJourneysModel");
		}//end if

		return $this->model_journeys;
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