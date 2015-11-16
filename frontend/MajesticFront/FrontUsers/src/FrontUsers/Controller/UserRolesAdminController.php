<?php
namespace FrontUsers\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class UserRolesAdminController extends AbstractActionController
{
	/**
	 * Container for Front User Roles Model
	 * @var \FrontUsers\Models\FrontUserRolesAdminModel
	 */
	protected $model_user_roles;

	public function indexAction()
	{
		//load the roles
		$objRoles = $this->getUserRolesModel()->fetchUserRoles($this->params()->fromQuery());

		return array("objRoles" => $objRoles);
	}//end function

	public function createRoleAction()
	{
		//load System Admin Form
		$form = $this->getUserRolesModel()->getUserRoleAdminSystemForm();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			//populate the form
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				try {
					$objUserRole = $this->getUserRolesModel()->createUserRole($form->getData());
					//set success message
					$this->flashMessenger()->addSuccessMessage("User Role create successfully");
				} catch (\Exception $e) {
					//set error message
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				}//end catch

				//redirect to user role index page
				return $this->redirect()->toRoute("front-users-roles/admin");
			}//end if
		}//end if

		return array("form" => $form);
	}//end function

	public function editRoleAction()
	{
		//fetch role id
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("User Role could not be loaded. Id is invalid");
			return $this->redirect()->toRoute("front-users-roles/admin");
		}//end if

		$form = $this->getUserRolesModel()->getUserRoleAdminSystemForm();
		//load the user role
		$objUserRole = $this->getUserRolesModel()->fetchUserRole($id);
		//populate the form
		$form->bind($objUserRole);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			//populate the form
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				try {
					$objUserRole = $form->getData();
					//add id to entity
					$objUserRole->set("id", $id);

					$objUserRole = $this->getUserRolesModel()->updateUserRole($objUserRole);
					//set success message
					$this->flashMessenger()->addSuccessMessage("User Role create successfully");
				} catch (\Exception $e) {
					//set error message
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				}//end catch

				//redirect to user role index page
				return $this->redirect()->toRoute("front-users-roles/admin");
			}//end if
		}//end if

		return array("form" => $form);
	}//end function

	public function deleteRoleAction()
	{
		//fetch role id
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("User Role could not be loaded. ID is invalid");
			return $this->redirect()->toRoute("front-users-roles/admin");
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($this->params()->fromPost("delete")) == "yes")
			{
				try {
					//delete the user role
					$objUserRole = $this->getUserRolesModel()->deleteUserRole($id);

					//set success message
					$this->flashMessenger()->addSuccessMessage("User Role has been deleted succesfully");
				} catch (\Exception $e) {
					//set delete message
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				}//end catch
			} else {
				$this->flashMessenger()->addMessage("User Role Delete operation cancelled");
			}//end if

			//redirect to user role index page
			return $this->redirect()->toRoute("front-users-roles/admin");
		}//end if

		//load the user role
		$objUserRole = $this->getUserRolesModel()->fetchUserRole($id);

		return array("objUserRole" => $objUserRole);
	}//end function

	public function toggleRoleAction()
	{
		//fetch role id
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("User Role could not be loaded. Id is invalid");
			return $this->redirect()->toRoute("front-users-roles/admin");
		}//end if

		//update the role
		try {
			$objUserRole = $this->getUserRolesModel()->fetchUserRole($id);
			$objUserRole->set("role_active", ( 1 - $objUserRole->get("role_active")));
			$this->getUserRolesModel()->updateUserRole($objUserRole);

			//set success message
			$this->flashMessenger()->addSuccessMessage("User Role status succesfully saved");
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
		}//end catch

		//redirect to user role index page
		return $this->redirect()->toRoute("front-users-roles/admin");
	}//end function

	public function viewRoleAclRulesAction()
	{

	}//end function

	public function getStandardRolesAction()
	{
		$objStandardRoles = $this->getUserRolesModel()->fetchStandardRoles();
		
		return array(
			"objStandardRoles" => $objStandardRoles,
		);
	}//end function
	
	public function createRoleFromStandardRoleAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Requested Standard Role could not be loaded. ID is not set");
			
			//redirect to user role index page
			return $this->redirect()->toRoute("front-users-roles/admin");
		}//end if
		
		//load role
		$objStandardRole = $this->getUserRolesModel()->fetchStandardRole($id);
		
		//load System Admin Form
		$form = $this->getUserRolesModel()->getUserRoleAdminSystemForm();
		$form->bind($objStandardRole);
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());	
			
			if ($form->isValid())
			{
				try {
					$objData = $form->getData();
					$objRole = $this->getUserRolesModel()->createUserRoleFromStandardRole($id, $objData->getArrayCopy());
					
					//set message
					$this->flashMessenger()->addSuccessMessage("User Role has been created");
					
					//redirect to user role index page
					return $this->redirect()->toRoute("front-users-roles/admin");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if
		
		return array(
			"form" => $form,
			"objStandardRole" => $objStandardRole,
		);
	}//end function
	
	/**
	 * Create an instance of the Front User Roles Admin Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUserRolesAdminModel
	 */
	private function getUserRolesModel()
	{
		if (!$this->model_user_roles)
		{
			$this->model_user_roles = $this->getServiceLocator()->get("FrontUsers\Models\FrontUserRolesAdminModel");
		}//end function

		return $this->model_user_roles;
	}//end function
}//end class