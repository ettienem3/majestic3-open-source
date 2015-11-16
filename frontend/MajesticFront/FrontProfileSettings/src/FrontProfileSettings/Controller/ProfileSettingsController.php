<?php
namespace FrontProfileSettings\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ProfileSettingsController extends AbstractActionController
{
	/**
	 * Container for the Profile Settings Model
	 * @var \FrontProfileSettings\Models\FrontProfileSettingsModel
	 */
	private $model_profile_settings;
	
    public function indexAction()
    {
        //load form
        $form = $this->getProfileSettingsModel()->getProfileSettingsAdminForm();
        
        //load profile settings
        $objProfile = $this->getProfileSettingsModel()->fetchProfileSettings();
        $objProfile->remove("submit");
        
        foreach ($form->getElements() as $objElement)
        {
        	$form->get($objElement->getName())->setAttribute("disabled", "disabled");
        }//end foreach
        
        //bind data to form
        $form->bind($objProfile);
        
        return array(
        	"form" => $form,	
        	"objProfile" => $objProfile,
        );
    }//end function
    
    public function updateAction()
    {
    	$this->flashMessenger()->addInfoMessage("Profile settings cannot be changed using this channel");
    	return $this->redirect()->toRoute("front-profile-settings");
    	
    	//load form
    	$form = $this->getProfileSettingsModel()->getProfileSettingsAdminForm();
    	
    	//load profile settings
    	$objProfile = $this->getProfileSettingsModel()->fetchProfileSettings();
    	
    	//bind data to form
    	$form->bind($objProfile);
    	
    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$form->setData($request->getPost());
    		
    		if ($form->isValid($request->getPost()))
    		{
    			try {
    				//extract data from form
    				$objProfile = $form->getData();
    				
    				//update the profile
    				$objProfile = $this->getProfileSettingsModel()->updateProfileSettings($objProfile);
    				
    				//set message
    				$this->flashMessenger()->addSuccessMessage("Profile settings updated");
    				
    				return $this->redirect()->toRoute("front-profile-settings");
    			} catch (\Exception $e) {
    				//set error message
    				$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			}//end catch
    		}//end if
    	}//end if
    	
    	return array(
    			"form" => $form,
    	);
    }//end function
    
    /**
     * Create an instance of the Front Profile Settings Model using the Service Manager
     * @return \FrontProfileSettings\Models\FrontProfileSettingsModel
     */
    private function getProfileSettingsModel()
    {
    	if (!$this->model_profile_settings)
    	{
    		$this->model_profile_settings = $this->getServiceLocator()->get("FrontProfileSettings\Models\FrontProfileSettingsModel");
    	}//end if
    	
    	return $this->model_profile_settings;
    }//end function
}//end class
