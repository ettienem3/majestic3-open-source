<?php
namespace FrontProfileSettings\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontProfileSettings\Entities\FrontProfileSettingsProfileEntity;

class FrontProfileSettingsModel extends AbstractCoreAdapter
{
	/**
	 * Load Profile Settings Form
	 * @return \Zend\Form\Form
	 */
	public function getProfileSettingsAdminForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
							->getSystemForm("Core\Forms\SystemForms\Profiles\ProfileUserAccessibleSettingsForm");
		
		return $objForm;
	}//end function
	
	/**
	 * Request profile settings
	 * @return \FrontProfileSettings\Entities\FrontProfileSettingsProfileEntity
	 */
	public function fetchProfileSettings()
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("profiles/admin");
		
		//execute
		$objResult = $objApiRequest->performGETRequest(array())->getBody();
		
		//create entity
		$objProfile = $this->getServiceLocator()->get("FrontProfileSettings\Entities\FrontProfileSettingsProfileEntity");
		$objProfile->set($objResult->data);
		
		return $objProfile;
	}//end function
	
	/**
	 * Update profile settings
	 * @param FrontProfileSettingsProfileEntity $objProfile
	 * @return \FrontProfileSettings\Entities\FrontProfileSettingsProfileEntity
	 */
	public function updateProfileSettings(FrontProfileSettingsProfileEntity $objProfile)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("profiles/admin");
		
		//execute
		$objResult = $objApiRequest->performPOSTRequest($objProfile->getArrayCopy())->getBody();
		
		//create entity
		$objProfile = $this->getServiceLocator()->get("FrontProfileSettings\Entities\FrontProfileSettingsProfileEntity");
		$objProfile->set($objResult->data);
		
		return $objProfile;
	}//end function
}//end class
