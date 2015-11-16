<?php
namespace FrontUsers\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontUsersAclRulesModel extends AbstractCoreAdapter
{
	/**
	 * Load the admin form for User Data Access Rules
	 * @return \Zend\Form\Form
	 */
	public function getUserAccessRulesForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm("Core\Forms\SystemForms\AccessControl\UserAccessRuleForm");
		
		return $objForm;
	}//end function
	
	public function fetchUserDataAccessRules($user_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("users/admin/user/data/acl/$user_id");
		
		//request data
		$objData = $objApiRequest->performGETRequest()->getBody();
		
		return $objData->data;
	}//end function
	
	public function fetchUserDataAccessRule($user_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("users/admin/user/data/acl/$user_id");
	}//end function
	
	public function createUserDataAccessRules($user_id, $arr_data)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("user_id" => $user_id, "arr_data" => $arr_data));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("users/admin/user/data/acl/$user_id");
		
		$objData = $objApiRequest->performPOSTRequest($arr_data)->getBody();
		$objUserDataAccessRules = $objData->data;
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("user_id" => $user_id, "arr_data" => $arr_data, "objUserDataAccessRules" => $objUserDataAccessRules));
		
		return $objUserDataAccessRules;
	}//end function
}//end class