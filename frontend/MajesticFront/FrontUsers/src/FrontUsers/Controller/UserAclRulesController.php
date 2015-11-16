<?php
namespace FrontUsers\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class UserAclRulesController extends AbstractActionController
{
	/**
	 * Container for the Front User Acl Rules Model
	 * @var \FrontUsers\Models\FrontUsersAclRulesModel
	 */
	private $model_user_acl_rules;
	
	/**
	 * Container for the Users Model
	 * @var \FrontUsers\Models\FrontUsersModel
	 */
	private $model_users;
	
	public function indexAction()
	{
		//load users
		$objUsers = $this->getFrontUsersModel()->fetchUsers();
		
		return array(
			"objUsers" => $objUsers,
		);
	}//end function
	
	public function createRuleAction()
	{
		//laod user id
		$user_id = $this->params()->fromRoute("user_id", "");
		if ($user_id == "")
		{
			$this->flashMessenger()->addErrorMessage("Data Access Rules cannot be loaded. User ID is not set");
			return $this->redirect()->toRoute("front-users");	
		}//end if
		
		//load the form
		$form = $this->getFrontUserAclRulesModel()->getUserAccessRulesForm();
		
		//load user data
		$objUserAccessRules = $this->getFrontUserAclRulesModel()->fetchUserDataAccessRules($user_id);
		
		//create entity
		$objUser = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserEntity");
		foreach ($objUserAccessRules as $key => $arr_values)
		{
			$objUser->set($key, $arr_values);	
		}//end foreach
		$form->bind($objUser); 
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_data = $request->getPost();
			foreach ($form->getElements() as $objElement)
			{
				switch ($objElement->getName())
				{
					case "submit":
						continue;
						break;
				}//end switch

				if ($objElement->getAttribute("type") == "multi_checkbox")
				{
					if (!array_key_exists($objElement->getName(), $arr_data))
					{
						$arr_data[$objElement->getName()] = array();
					}//end if
				}//end if
			}//end foreach
			
			$form->setData($request->getPost());

			if ($form->isValid($request->getPost()))
			{
				try {
					$objUserAccessRules = $this->getFrontUserAclRulesModel()->createUserDataAccessRules($user_id, $form->getData()->getArrayCopy());
					
					//set success message
					$this->flashMessenger()->addSuccessMessage("User Data Access Rules have been updated");
					$this->redirect()->toRoute("front-user-data-acl-rules");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if
		
		return array(
			"form" => $form,
			"objUser" => (object) array("id" => $user_id),
		);
	}//end function
	
	public function deleteRuleAction()
	{
		
	}//end function 
	
	/**
	 * Create an instance of the Front User Acl Rules Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUsersAclRulesModel
	 */
	private function getFrontUserAclRulesModel()
	{
		if (!$this->model_user_acl_rules)
		{
			$this->model_user_acl_rules = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersAclRulesModel");
		}//end if
		
		return $this->model_user_acl_rules;
	}//end function
	
	/**
	 * Create an instance of the Front Users Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUsersModel
	 */
	private function getFrontUsersModel()
	{
		if (!$this->model_users)
		{
			$this->model_users = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersModel");
		}//end if
		
		return $this->model_users;
	}//end function
}//end class