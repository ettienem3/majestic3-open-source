<?php
namespace FrontBehavioursConfig\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontBehavioursConfig\Entities\FrontBehavioursBehaviourConfigEntity;
use Zend\Log\Processor\Backtrace;

class FrontBehavioursConfigModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Front System Forms Model
	 * @var \FrontCore\Models\SystemFormsModel
	 */
	private $model_front_system_forms;

	/**
	 * Get the Behaviour Actions Form.
	 * This form allows to select a specific action for the specified behaviour
	 * @param string $behaviour
	 * @param array $arr_params - Optional. Specify additional params to send to API
	 * @return \Zend\Form\Form
	 */
	public function getBehaviourActionsForm($behaviour, $arr_params = NULL)
	{
		//check behaviours are active
		$this->isBehavioursActive();

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup url
		$url = "behaviour/form?behaviour=$behaviour";
		if (is_array($arr_params))
		{
			$url .= "&";
			foreach ($arr_params as $key => $value)
			{
				$url .= "$key=$value&";
			}//end foreach
			$url = rtrim($url, "&");
		}//end if

		//setup the object and specify the action
		$objApiRequest->setApiAction($url);

		//execute the request
		$objResult = $objApiRequest->performGETRequest()->getBody();

		$objForm = $this->getFrontSystemFormsModel()->constructCustomForm($objResult->data->objForm);

		return array(
			"form" => $objForm,
			"arr_descriptors" => (array) $objResult->data->descriptors,
		);
	}//end function

	/**
	 * Load profile behaviour summaries
	 * @param array $arr_params
	 * @return stadClass
	 */
	public function fetchProfileBehaviourSummary(array $arr_params) 
	{
		//check behaviours are active
		$this->isBehavioursActive();
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("behaviour/config");
		
		//execute the request
		$objResult = $objApiRequest->performGETRequest($arr_params)->getBody();
		
		return $objResult->data;
	}//end function
	
	/**
	 * Load a collection of behaviour actions
	 * @param array $arr_where
	 * @return StdClass
	 */
	public function fetchBehaviourActions(array $arr_where)
	{
		//check behaviours are active
		$this->isBehavioursActive();

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//set params url
		$url = '';
		foreach ($arr_where as $key => $value)
		{
			$url .= "$key=$value&";
		}//end foreach
		$url = rtrim($url, "&");

		//setup the object and specify the action
		$objApiRequest->setApiAction("behaviour/config?$url");

		//execute the request
		$objResult = $objApiRequest->performGETRequest()->getBody();

		return $objResult->data;
	}//end function

	/**
	 * Load details about a specific configure behavioural action
	 * @param mixed $id
	 * @return \FrontBehavioursConfig\Entities\FrontBehavioursBehaviourConfigEntity
	 */
	public function fetchBehaviourAction($id)
	{
		//check behaviours are active
		$this->isBehavioursActive();

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("behaviour/config/$id");

		//execute the request
		$objResult = $objApiRequest->performGETRequest()->getBody();

		//create behaviour entity
		$objBehaviour = $this->createBehaviourEntity($objResult->data);

		return $objBehaviour;
	}//end function

	/**
	 * Create a behviour
	 * @triggers createBehaviourAction.pre, createBehaviourAction.post
	 * @return \FrontBehaviours\Entities\FrontBehavioursBehaviourConfigEntity
	 */
	public function createBehaviourAction($arr_form_data)
	{
		//check behaviours are active
		$this->isBehavioursActive();

		//check if field value is set where required
		if (isset($arr_form_data["field_value"]))
		{
			if (isset($arr_form_data["field_value_label"]) && $arr_form_data["field_value_label"] != "" && $arr_form_data["field_value"] == "")
			{
				$arr_form_data["field_value"] = $arr_form_data["field_value_label"];
			}//end if
		}//end if

		//create entity
		$objBehaviour = $this->createBehaviourEntity($arr_form_data);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objBehaviour" => $objBehaviour));

		//create the behaviour
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("behaviour/config");

		//execute
		$objResult = $objApiRequest->performPOSTRequest($objBehaviour->getArrayCopy())->getBody();

		//recreate the entity object from response
		$objBehaviour = $this->createBehaviourEntity($objResult->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objBehaviour" => $objBehaviour));

		return $objBehaviour;
	}//end function

	/**
	 * Update a behviour
	 * @triggers editBehaviourAction.pre, editBehaviourAction.post
	 * @return \FrontBehaviours\Entities\FrontBehavioursBehaviourConfigEntity
	 */
	public function editBehaviourAction(FrontBehavioursBehaviourConfigEntity $objBehaviour)
	{
		//check behaviours are active
		$this->isBehavioursActive();

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objBehaviour" => $objBehaviour));

		//update the behaviour
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("behaviour/config/" . $objBehaviour->get("id"));

		//execute
		$objResult = $objApiRequest->performPUTRequest($objBehaviour->getArrayCopy())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objBehaviour" => $objBehaviour));

		return $objBehaviour;
	}//end function

	/**
	 * Delete a behviour
	 * @triggers deleteBehaviourAction.pre, deleteBehaviourAction.post
	 * @return \FrontBehaviours\Entities\FrontBehavioursBehaviourConfigEntity
	 */
	public function deleteBehaviourAction(FrontBehavioursBehaviourConfigEntity $objBehaviour)
	{
		//check behaviours are active
		$this->isBehavioursActive();

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objBehaviour" => $objBehaviour));

		//delete the behaviour
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("behaviour/config/" . $objBehaviour->id);

		//execute
		$objResult = $objApiRequest->performDELETERequest(array())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objBehaviour" => $objBehaviour));

		return $objBehaviour;
	}//end function

	/**
	 * Create an entity object for a behaviour
	 * @param mixed $objData
	 * @return \FrontBehavioursConfig\Entities\FrontBehavioursBehaviourConfigEntity
	 */
	private function createBehaviourEntity($objData)
	{
		$entity = $this->getServiceLocator()->get("FrontBehavioursConfig\Entities\FrontBehavioursBehaviourConfigEntity");

		//set the data
		$entity->set($objData);

		return $entity;
	}//end function

	/**
	 * Check if behaviours are active against profile plugin settings
	 * @throws \Exception
	 */
	private function isBehavioursActive()
	{
		//load session for plugins enabled
		$objUserSession = \FrontUserLogin\Models\FrontUserSession::isLoggedIn();

		if (!isset($objUserSession->plugins_enabled))
		{
			//plugins not managed
			return;
		}//end if

		$arr_plugins = (array) $objUserSession->plugins_enabled;

		if (!in_array("behviours_basic", $arr_plugins) && !in_array("behaviours_advanced", $arr_plugins))
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Behaviours are not activated", 500 );
		}//end if
	}//end function

	/**
	 * Get the Behaviour Action configuration form
	 * This allows the user to setup and configure the select action for a given behaviour
	 * @param array $arr_params
	 * @return \Zend\Form\Form
	 */
	public function getBehaviourConfigForm(array $arr_params)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//create url
		$url = '';
		foreach ($arr_params as $key => $value)
		{
			$url .= "$key=$value&";
		}//end foreach
		$url = rtrim($url, "&");

		//setup the object and specify the action
		$objApiRequest->setApiAction("behaviour/form?$url");

		//execute the request
		$objResult = $objApiRequest->performGETRequest()->getBody();

		//create the form
		$objForm = $this->getFrontSystemFormsModel()->constructCustomForm($objResult->data->objForm);

		//add additional fields which will be required later on
		$objForm->add(array(
				"type" => "hidden",
				"name" => "behaviour",
				"attributes" => array(
					"id" => "behaviour",
					"value" => $arr_params["behaviour"],
				),
				"options" => array(
					"value" => $arr_params["behaviour"],
				),
		));

		$objForm->add(array(
				"type" => "hidden",
				"name" => "beh_action",
				"attributes" => array(
						"id" => "beh_action",
						"value" => $arr_params["beh_action"],
				),
				"options" => array(
						"value" => $arr_params["beh_action"],
				),
		));

		$objForm->add(array(
				"type" => "hidden",
				"name" => "setup_complete",
				"attributes" => array(
					"value" => 1,
					"id" => "setup_complete",
				),
				"options" => array(
					"value" => 1,
				),
		));

		return $objForm;
	}//end function

	/**
	 * Create an instance of the Front Core System Forms Model using the Service Manager
	 * @return \FrontCore\Models\SystemFormsModel
	 */
	private function getFrontSystemFormsModel()
	{
		if (!$this->model_front_system_forms)
		{
			$this->model_front_system_forms = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel");
		}//end if

		return $this->model_front_system_forms;
	}
}//end class
