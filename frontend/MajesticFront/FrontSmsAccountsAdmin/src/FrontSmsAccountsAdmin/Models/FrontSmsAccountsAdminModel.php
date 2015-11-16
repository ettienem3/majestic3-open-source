<?php
namespace FrontSmsAccountsAdmin\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontSmsAccountsAdmin\Entities\FrontSmsAccountsAdminSmsAccountEntity;

class FrontSmsAccountsAdminModel extends AbstractCoreAdapter
{
	/**
	 * Load the Sms Accounts Admin Form
	 * @return \Zend\Form\Form
	 */
	public function getSmsAccountAdminForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm("Core\Forms\SystemForms\SmsAccounts\SmsAccountAdminForm");
		
		return $objForm;
	}//end function
	
	/**
	 * Request a collection of Sms Accounts
	 * @param array $arr_where - Optional
	 * @return \FrontSmsAccountsAdmin\Entities\FrontSmsAccountsAdminSmsAccountEntity
	 */
	public function fetchSmsAccounts($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("sms-accounts/admin");
		
		//execute
		$objResult = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		foreach ($objResult->data as $objSmsAccount)
		{
			$arr[] = $this->createSmsAccountEntity($objSmsAccount);
		}//end foreach
		
		return (object) $arr;
	}//end function
	
	/**
	 * Request a specific Sms Account
	 * @param mixed $id
	 * @return \FrontSmsAccountsAdmin\Entities\FrontSmsAccountsAdminSmsAccountEntity
	 */
	public function fetchSmsAccount($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("sms-accounts/admin/$id");
		
		//execute
		$objResult = $objApiRequest->performGETRequest()->getBody();
	
		return $this->createSmsAccountEntity($objResult->data);
	}//end function
	
	/**
	 * Create a new Sms Account
	 * @trigger createSmsAccount.pre, createSmsAccount.post
	 * @param array $arr_data
	 * @return \FrontSmsAccountsAdmin\Entities\FrontSmsAccountsAdminSmsAccountEntity
	 */
	public function createSmsAccount($arr_data)
	{
		//create entity
		$objSmsAccount = $this->createSmsAccountEntity($arr_data);
		
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSmsAccount" => $objSmsAccount));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("sms-accounts/admin");
		
		//execute
		$objResult = $objApiRequest->performPOSTRequest($objSmsAccount->getArrayCopy())->getBody();
	
		//recreate the Sms Account Entity
		$objSmsAccount = $this->createSmsAccountEntity($objResult->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSmsAccount" => $objSmsAccount));
		
		return $objSmsAccount;
	}//end function

	/**
	 * Update an sms account
	 * @trigger updateSmsAccount.pre, updateSmsAccount.post
	 * @param FrontSmsAccountsAdminSmsAccountEntity $objSmsAccount
	 * @return \FrontSmsAccountsAdmin\Entities\FrontSmsAccountsAdminSmsAccountEntity
	 */
	public function updateSmsAccount(FrontSmsAccountsAdminSmsAccountEntity $objSmsAccount)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSmsAccount" => $objSmsAccount));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("sms-accounts/admin/" . $objSmsAccount->get("id"));
		
		//execute
		$objResult = $objApiRequest->performPUTRequest($objSmsAccount->getArrayCopy())->getBody();
		
		//recreate Sms Account Entity
		$objSmsAccount = $this->createSmsAccountEntity($objResult->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSmsAccount" => $objSmsAccount));
		
		return $objSmsAccount;
	}//end function
	
	/**
	 * Delete an existing Sms Account
	 * @trigger deleteSmsAccount.pre, deleteSmsAccount.post
	 * @param mixed $id
	 * @return \FrontSmsAccountsAdmin\Entities\FrontSmsAccountsAdminSmsAccountEntity
	 */
	public function deleteSmsAccount($id)
	{
		//load the account
		$objSmsAccount = $this->fetchSmsAccount($id);
		
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSmsAccount" => $objSmsAccount));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction($objSmsAccount->getHyperMedia("delete-sms-account")->url);
		$objApiRequest->setApiModule(NULL);
		
		//execute
		$objResult = $objApiRequest->performDELETERequest(array())->getBody();
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSmsAccount" => $objSmsAccount, "objResult" => $objResult));
		
		return $objSmsAccount;
	}//end function
	
	/**
	 * Convert data into entities
	 * @param mixed $objData
	 * @return \FrontSmsAccountsAdmin\Entities\FrontSmsAccountsAdminSmsAccountEntity
	 */
	private function createSmsAccountEntity($objData)
	{
		$objSmsAccount = $this->getServiceLocator()->get("FrontSmsAccountsAdmin\Entities\FrontSmsAccountsAdminSmsAccountEntity");
		
		//assign data
		$objSmsAccount->set($objData);
		
		return $objSmsAccount;
	}//end function
}//end class
