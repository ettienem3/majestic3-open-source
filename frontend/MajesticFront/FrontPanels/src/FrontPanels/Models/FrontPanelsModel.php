<?php
namespace FrontPanels\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontPanels\Entities\FrontPanelsPanelEntity;

class FrontPanelsModel extends AbstractCoreAdapter
{
	/**
	 * Load the admin form for Profile Panels from Core System Forms
	 * @return \Zend\Form\Form
	 */
	public function getProfilePanelAdminForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm("Core\Forms\SystemForms\Profiles\ProfilePanelsForm");

		return $objForm;
	}//end function

	/**
	 * Load a sepcific Profile panel available
	 * @return \FrontPanels\Entities\FrontPanelsPanelEntity
	 */
	public function fetchProfilePanel($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("panels/profile/setup/$id");

		//execute
		$objResult = $objApiRequest->performGETRequest(array())->getBody();

		$objPanel = $this->getServiceLocator()->get("FrontPanels\Entities\FrontPanelsPanelEntity");
		$objPanel->set($objResult->data);
		return $objPanel;
	}//end function

	/**
	 * Load Profile panels available
	 * @return \FrontPanels\Entities\FrontPanelsPanelEntity
	 */
	public function fetchProfilePanels()
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("panels/profile/setup");

		//execute
		$objPanels = $objApiRequest->performGETRequest(array())->getBody();

		$arr = array();
		foreach ($objPanels->data as $k => $objTPanel)
		{
			$objPanel = $this->getServiceLocator()->get("FrontPanels\Entities\FrontPanelsPanelEntity");
			$objPanel->set($objTPanel);

			$arr[] = $objPanel;
		}//end foreach

		return (object) $arr;
	}//end function

	/**
	 * Allocate panel to profile
	 * @param array $arr_data
	 * @return \FrontPanels\Entities\FrontPanelsPanelEntity
	 */
	public function createProfilePanel($arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("panels/profile/setup");

		//execute
		$objResult = $objApiRequest->performPOSTRequest($arr_data)->getBody();

		$objPanel = $this->getServiceLocator()->get("FrontPanels\Entities\FrontPanelsPanelEntity");
		$objPanel->set($objResult->data);
		return $objPanel;
	}//end function

	/**
	 * Update a profile panel
	 * @param FrontPanelsPanelEntity $objPanel
	 * @return \FrontPanels\Entities\FrontPanelsPanelEntity
	 */
	public function editProfilePanel(FrontPanelsPanelEntity $objPanel)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("panels/profile/setup/" . $objPanel->get("id"));

		//execute
		$objResult = $objApiRequest->performPUTRequest($objPanel->getArrayCopy())->getBody();

		$objPanel = $this->getServiceLocator()->get("FrontPanels\Entities\FrontPanelsPanelEntity");
		$objPanel->set($objResult->data);
		return $objPanel;
	}//end function

	/**
	 * Delete a profile panel
	 * @param FrontPanelsPanelEntity $objPanel
	 * @return \FrontPanels\Entities\FrontPanelsPanelEntity
	 */
	public function deleteProfilePanel(FrontPanelsPanelEntity $objPanel)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("panels/profile/setup/" . $objPanel->get("id"));

		//execute
		$objResult = $objApiRequest->performDELETERequest()->getBody();

		$objPanel = $this->getServiceLocator()->get("FrontPanels\Entities\FrontPanelsPanelEntity");
		$objPanel->set($objResult->data);
		return $objPanel;
	}//end function

	/**
	 * Read user panels to session
	 */
	public function fetchUserPanelsCache()
	{
		$objUserSession = $this->getUserSession();
		
		if (isset($objUserSession->cache_user_panels))
		{
			$arr = array();
			foreach ($objUserSession->cache_user_panels as $k => $objTPanel)
			{
				$objPanel = $this->getServiceLocator()->get("FrontPanels\Entities\FrontPanelsPanelEntity");
				$objPanel->set($objTPanel);
			
				$arr[] = $objPanel;
			}//end foreach
			
			return (object) $arr;
		} else {
			return FALSE;
		}//end if
	}//end function
	
	/**
	 * Store user panels to session
	 */
	public function setUserPanelCache($objPanels)
	{
		$objUserSession = $this->getUserSession();
		$arr = array();
		foreach ($objPanels as $k => $objTPanel)
		{				
			$arr[] = $objTPanel->getArrayCopy();
		}//end foreach
		
		$objUserSession->cache_user_panels = $arr;
	}//end function
	
	/**
	 * Load panels allocated to the current logged in user
	 * @return \FrontPanels\Entities\FrontPanelsPanelEntity
	 */
	public function fetchUserPanels()
	{
		//check user cache
		$objPanels = $this->fetchUserPanelsCache();	
		if (!$objPanels)
		{
			//create the request object
			$objApiRequest = $this->getApiRequestModel();
	
			//setup the object and specify the action
			$objApiRequest->setApiAction("panels/user/setup");
	
			//execute
			$objResult = $objApiRequest->performGETRequest()->getBody();
	
			$arr = array();
			foreach ($objResult->data as $k => $objTPanel)
			{
				$objPanel = $this->getServiceLocator()->get("FrontPanels\Entities\FrontPanelsPanelEntity");
				$objPanel->set($objTPanel);
	
				$arr[] = $objPanel;
			}//end foreach
	
			$objPanels = (object) $arr;
			$this->setUserPanelCache($objPanels);
		}//end if
		
		return $objPanels;
	}//end function

	public function allocatePanelToUser($id)
	{
		//clear user panel cache
		$objUserSession = $this->getUserSession();
		unset($objUserSession->user_panels);
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("panels/user/setup");

		$objResult = $objApiRequest->performPOSTRequest(array("id" => $id))->getBody();
		return $objResult->data;
	}//end function

	/**
	 * Update settings for a user panel
	 * @param mixed $id
	 * @param array $arr_data
	 */
	public function editUserPanelSettings($id, array $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("panels/user/setup/$id");

		$objResult = $objApiRequest->performPUTRequest($arr_data)->getBody();
		return $objResult->data;
	}//end function

	public function removePanelFromUser($id)
	{
		//clear user panel cache
		$objUserSession = $this->getUserSession();
		unset($objUserSession->user_panels);
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("panels/user/setup/$id");

		$objResult = $objApiRequest->performDELETERequest(array())->getBody();
		return $objResult->data;
	}//end function

	public function processUserPanel($id, $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("panels/user/read/$id");

		$objPanel = $this->getServiceLocator()->get("FrontPanels\Entities\FrontPanelsPanelEntity");

		try {
			$objResult = $objApiRequest->performGETRequest(array())->getBody();
			$objPanel->set($objResult->data);

			//check for local panel modifyers
			$model = "Panel" . preg_replace( '/-(.?)/e',"strtoupper('$1')", ucfirst(strtolower($objPanel->get("panels_unique_identifier")))) . "ProcessorModel";
			$objPanel = $this->processLocalPanel($objPanel, $model);
		} catch (\Exception $e) {
			$objPanel->set($arr_data);
			$objPanel->set("html", "<div>" . $e->getMessage() . "</div>");
		}//end catch

 		return $objPanel;
	}//end function

	/**
	 * Transform panel based on local override class
	 * @param FrontPanelsPanelEntity $objPanel
	 * @param string $model
	 * @return \FrontPanels\Entities\FrontPanelsPanelEntity
	 */
	private function processLocalPanel(FrontPanelsPanelEntity $objPanel, $model)
	{
		$class = "FrontPanels\Panels\\$model";
		if (!class_exists($class))
		{
			return $objPanel;
		}//end if

		//load model
		$objModel = $this->getServiceLocator()->get($class);

		//check if model has the correct interface applied
		if (!$objModel instanceof \FrontPanels\Interfaces\InterfacePanelsProcessor)
		{
			return $objPanel;
		}//end if

		//process the panel
		$objPanel = $objModel->processPanel($objPanel);
		return $objPanel;
	}//end function
	
	private function getUserSession()
	{
		return \FrontUserLogin\Models\FrontUserSession::isLoggedIn();
	}//end function
}//end class
