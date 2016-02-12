<?php
namespace FrontUsers\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class UserRolesAllocateController extends AbstractActionController
{
	/**
	 * Container for Front User Roles Model
	 * @var \FrontUsers\Models\FrontUserRolesAdminModel
	 */
	protected $model_user_roles;

	/**
	 * Container for the Front User Model
	 * @var \FrontUsers\Models\FrontUsersModel
	 */
	protected $model_users;

	public function listUserRolesAction()
	{
		//load the user id from the route
		$user_id = $this->params()->fromRoute("user_id", "");
		if ($user_id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Roles could not be loaded. User Id is not set");

			//redirect back to user index page
			return $this->redirect()->toRoute("frontusers");
		}//end if

		//load user details
		$objUser = $this->getUsersModel()->fetchUser($user_id);

		//load user allocated roles
		$objUserRoles = $this->getUserRolesModel()->fetchUserAllocatedRoles($user_id);

		return array(
				"objUser" => $objUser,
				"objUserRoles" => $objUserRoles,
		);
	}//end function

	public function allocateRoleAction()
	{
		//load the user id from the route
		$user_id = $this->params()->fromRoute("user_id", "");
		if ($user_id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Roles could not be loaded. User Id is not set");

			//redirect back to user index page
			return $this->redirect()->toRoute("frontusers");
		}//end if

		//load user data
		$objUser = $this->getUsersModel()->getUser($user_id);

		//load user roles data
		$objUserRoles = $this->getUserRolesModel()->fetchUserAllocatedRoles($objUser->id);
		$form = $this->getUserRolesModel()->getUserRoleAllocationSystemForm();
		//extract form elements
		$arr_form_elements = $form->getElements();

		//set form data
		if (is_object($objUserRoles))
		{
			foreach ($objUserRoles as $objUserRole)
			{
				if (array_key_exists("role_" . $objUserRole->fk_id_users_roles, $arr_form_elements))
				{
					$form->get("role_" . $objUserRole->fk_id_users_roles)->setAttributes(array(
																								"checked" => "checked",
																								"disabled" => "disabled",
																								));
				}//end if
			}//end foreach
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				try {
					//allocate the roles
					$objResult = $this->getUserRolesModel()->allocateUserRole($form->getData(), $objUser);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Roles have been allocated to " . $objUser->uname);

					//return to users index page
					return $this->redirect()->toRoute("front-users-roles/user", array("action" => "list-user-roles", "user_id" => $user_id));
				} catch (\Exception $e) {
					//set error message
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				}//end catch
			}//end if
		}//end if

		return array(
				"form" => $form,
				"objUser" => $objUser,
		);
	}//end function

	public function removeRoleAction()
	{
		//load the user id from the route
		$user_id = $this->params()->fromRoute("user_id", "");
		$role_id = $this->params()->fromRoute("id", "");
		if ($user_id == "" || $role_id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Role could not be remove. User Id or Role id is not set");

			//redirect back to user index page
			return $this->redirect()->toRoute("frontusers");
		}//end if

		try {
			//remove the role
			$result = $this->getUserRolesModel()->removeUserRole($role_id, $user_id);

			//set success message
			$this->flashMessenger()->addSuccessMessage("Role succesfully removed form User");
		} catch (\Exception $e) {
    		//set error message
    		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
		}//end catch

		//redirect back to the roles page
		return $this->redirect()->toRoute("front-users-roles/user", array("action" => "list-user-roles", "user_id" => $user_id));
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

	/**
	 * Create and instance of the Front Users Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUsersModel
	 */
	private function getUsersModel()
	{
		if (!$this->model_users)
		{
			$this->model_users = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersModel");
		}//end function

		return $this->model_users;
	}//end function
}//end class