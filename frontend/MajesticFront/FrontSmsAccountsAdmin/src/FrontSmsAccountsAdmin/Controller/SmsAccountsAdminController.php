<?php
namespace FrontSmsAccountsAdmin\Controller;

use FrontCore\Adapters\AbstractCoreActionController;

class SmsAccountsAdminController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Sms Accounts Admin Model
	 * @var \FrontSmsAccountsAdmin\Models\FrontSmsAccountsAdminModel
	 */
	private $model_front_sms_accounts_admin;

    public function indexAction()
    {
        $objSmsAccounts = $this->getSmsAccountsAdminModel()->fetchSmsAccounts($this->params()->fromQuery());

        return array(
        		"objSmsAccounts" => $objSmsAccounts,
        );
    }//end function

    public function createSmsAccountAction()
    {
    	//load the form
    	$form = $this->getSmsAccountsAdminModel()->getSmsAccountAdminForm();

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		//set form data
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			try {
    				//create the account
    				$objSmsAccount = $this->getSmsAccountsAdminModel()->createSmsAccount($form->getData());

    				//set success message
    				$this->flashMessenger()->addSuccessMessage("Sms Account created successfully");

    				//return to the index page
    				return $this->redirect()->toRoute("front-sms-accounts-admin");
    			} catch (\Exception $e) {
    				//set error message
    				$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			}//end cacth
    		}//end if
    	}//end if

    	return array(
    			"form" => $form,
    	);
    }//end function

    public function editSmsAccountAction()
    {
    	//get sms account id from the route
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Sms Account could not be loaded. Id is not set");

    		//return to the index page
    		return $this->redirect()->toRoute("front-sms-account-admin");
    	}//end if

    	//load the account details
    	$objSmsAccount = $this->getSmsAccountsAdminModel()->fetchSmsAccount($id);

    	//load the form
    	$form = $this->getSmsAccountsAdminModel()->getSmsAccountAdminForm();

    	//amend some form fields
    	$form->remove("account_type");
    	$form->remove("fk_id_users");
    	$form->remove("fk_id_sms_campaign");
    	$form->remove("fk_id_sms_vendor");

    	$form->add(array(
    		"type" => "hidden",
    		"name" => "account_type",
    		"attributes" => array(
    			"id" => "account_type",
    		),
    		"options" => array(
    			"value" => 1,
    		),
    	));

    	$form->add(array(
    			"type" => "hidden",
    			"name" => "fk_id_users",
    			"attributes" => array(
    					"id" => "fk_id_users",
    			),
    			"options" => array(
    					"value" => $objSmsAccount->get("fk_id_users"),
    			),
    	));

    	$form->add(array(
    			"type" => "hidden",
    			"name" => "fk_id_sms_campaign",
    			"attributes" => array(
    					"id" => "fk_id_sms_campaign",
    			),
    			"options" => array(
    					"value" => $objSmsAccount->get("fk_id_sms_campaign"),
    			),
    	));

    	$form->add(array(
    			"type" => "hidden",
    			"name" => "fk_id_sms_vendor",
    			"attributes" => array(
    					"id" => "fk_id_sms_vendor",
    			),
    			"options" => array(
    					"value" => $objSmsAccount->get("fk_id_sms_vendor"),
    			),
    	));

    	$form->get("sms_uname")->setAttribute("readonly", "readonly");
    	$form->get("sms_pword")->setAttribute("readonly", "readonly");

    	//bind data to the form
    	$form->bind($objSmsAccount);

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$form->setData($request->getPost());

    		//check if form is valid
    		if($form->isValid())
    		{
    			$objSmsAccount = $form->getData();
    			$objSmsAccount->set("id", $id);

    			try {
    				$objSmsAccount = $this->getSmsAccountsAdminModel()->updateSmsAccount($objSmsAccount);

    				//set success message
    				$this->flashMessenger()->addSuccessMessage("Sms Account has been updated successfully");

    				//return to the index page
    				return $this->redirect()->toRoute("front-sms-accounts-admin");
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

    public function deleteSmsAccountAction()
    {
    	//get sms account id from the route
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Sms Account could not be loaded. Id is not set");

    		//return to the index page
    		return $this->redirect()->toRoute("front-sms-account-admin");
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		if (strtolower($request->getPost("delete")) == "yes")
    		{
    			try {
    				$objSmsAccount = $this->getSmsAccountsAdminModel()->deleteSmsAccount($id);

    				//set message
    				$this->flashMessenger()->addSuccessMessage("Sms Account has been deleted successfully");
    			} catch (\Exception $e) {
    				//set message
    				$this->flashMessenger()->addErrorMessage($e->getMessage());
    			}//end catch
    		} else {
    			$this->flashMessenger()->addInfoMessage("Delete Operation cancelled");
    		}//end if

    		//redirect back to the home page
    		return $this->redirect()->toRoute("front-sms-accounts-admin");
    	}//end if
    }//end function

    public function statusSmsAccountAction()
    {
    	//get sms account id from the route
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Sms Account could not be loaded. Id is not set");

    		//return to the index page
    		return $this->redirect()->toRoute("front-sms-account-admin");
    	}//end if

    	try {
	    	//load the data
	    	$objSmsAccount = $this->getSmsAccountsAdminModel()->fetchSmsAccount($id);

	    	//set status
	    	$objSmsAccount->set("active", (1 - $objSmsAccount->get("active")));

	    	//update the account
    		$this->getSmsAccountsAdminModel()->updateSmsAccount($objSmsAccount);

    		//set success message
    		$this->flashMessenger()->addSuccessMessage("Sms Account Status updated successfully");
    	} catch (\Exception $e) {
    		//set error message
    		$this->flashMessenger()->addErrorMessage($e->getMessage());
    	}//end catch

    	//redirect back to the index page
    	return $this->redirect()->toRoute("front-sms-accounts-admin");
    }//end function

    /**
     * Create an instance of the Front Sms Accounts Admin Model using the Service Manager
     * @return \FrontSmsAccountsAdmin\Models\FrontSmsAccountsAdminModel
     */
    private function getSmsAccountsAdminModel()
    {
    	if(!$this->model_front_sms_accounts_admin)
    	{
    		$this->model_front_sms_accounts_admin = $this->getServiceLocator()->get("FrontSmsAccountsAdmin\Models\FrontSmsAccountsAdminModel");
    	}//end if

    	return $this->model_front_sms_accounts_admin;
    }//end function
}//end class
