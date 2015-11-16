<?php
namespace FrontPowerTools\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ProfileAutomationController extends AbstractActionController
{
	/**
	 * Container for the Front Power Tools Comms Automation Model
	 * @var \FrontPowerTools\Models\FrontPowerToolsCommsAutomationModel
	 */
	private $model_power_tools_comms_automation;
	
	public function indexAction()
	{
		
	}//end function
	
	public function queueAction()
	{
		$objResult = $this->getFrontPowerToolsCommAutomationModel()->queueComms();
		
		return array(
			"objResult" => $objResult,
		);
	}//end function
	
	public function sendAction()
	{
		$objResult = $this->getFrontPowerToolsCommAutomationModel()->sendComms();
		
		return array(
			"objResult" => $objResult,
		);
	}//end function
	
	public function queueSendAction()
	{
		$objResult = $this->getFrontPowerToolsCommAutomationModel()->queueSendComms();
		
		return array(
			"objResult" => $objResult,
		);
	}//end function
	
	public function userActivityAction()
	{
		$objResult = $this->getFrontPowerToolsCommAutomationModel()->processUserActivity();
		
		return array(
				"objResult" => $objResult,
		);
	}//end function
	
	public function profileActivityAction()
	{
		$objResult = $this->getFrontPowerToolsCommAutomationModel()->processProfileActivity();
	
		return array(
				"objResult" => $objResult,
		);
	}//end function
	
	/**
	 * Create an instance of the Front Power Tools Comms Automation Model using the Service Manager
	 * @return \FrontPowerTools\Models\FrontPowerToolsCommsAutomationModel
	 */
	private function getFrontPowerToolsCommAutomationModel()
	{
		if (!$this->model_power_tools_comms_automation)
		{
			$this->model_power_tools_comms_automation = $this->getServiceLocator()->get("FrontPowerTools\Models\FrontPowerToolsCommsAutomationModel");
		}//end if
		
		return $this->model_power_tools_comms_automation;
	}//end function
}//end class