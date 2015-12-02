<?php
namespace FrontUsers\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class RolesAclLinksAdminController extends AbstractActionController
{
	/**
	 * Container for Front User Roles Model
	 * @var \FrontUsers\Models\FrontUserRolesAdminModel
	 */
	protected $model_user_roles;

	/**
	 * Container for the Front User Roles Acl Links Model
	 * @var \FrontUsers\Models\FrontUsersRolesAclLinksModel
	 */
	protected $model_role_acl_links;

	/**
	 * Displays a list of resources allocated to a role
	 * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
	 */
	public function indexAction()
	{
		$role_id = $this->params()->fromRoute("role_id");

		//load role data
		$objRole = $this->getUserRolesModel()->fetchUserRole($role_id);

		//load role acl resources data
		$objResources = $this->getRoleAclLinksModel()->fetchRoleAclResourceAllocations($role_id);

		return array(
				"objRole" => $objRole,
				"objResources" => $objResources,
		);
	}//end function

	public function listAclResourcesAction()
	{
		$role_id = $this->params()->fromRoute("role_id");

		//load role data
		$objRole = $this->getUserRolesModel()->fetchUserRole($role_id);
		if (is_numeric($objRole->get("fk_id_common_user_roles")))
		{
			$this->flashMessenger()->addInfoMessage("Standard Role resources cannot be amended");
			
			//redirect to user role index page
			return $this->redirect()->toRoute("front-users-roles/admin");
		}//end if
		
		//load the Core Resources
// 		$objCoreAclResources = $this->getRoleAclLinksModel()->fetchCoreAclResources($role_id);

		//load the API Resources
		$objApiAclResources = $this->getRoleAclLinksModel()->fetchApiAclResources($role_id);

		return array(
				"objRole" => $objRole,
// 				"objCoreAclResources" => $objCoreAclResources,
				"objApiAclResources" => $objApiAclResources,
		);
	}//end function

	public function allocateAclResourceAction()
	{
		$role_id = $this->params()->fromRoute("role_id");

		//load role data
		$objRole = $this->getUserRolesModel()->fetchUserRole($role_id);
		if (is_numeric($objRole->get("fk_id_common_user_roles")))
		{
			$this->flashMessenger()->addInfoMessage("Standard Role resources cannot be amended");
				
			//redirect to user role index page
			return $this->redirect()->toRoute("front-users-roles/admin");
		}//end if
		
		$resource_type = $this->params()->fromRoute("type", "");
		$resource_id = $this->params()->fromRoute("resource_id", "");

		if ($resource_type == "" || $resource_id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Resource could not be loaded. Required Information is missing");

			//redirect back the role acl link index page
			return $this->redirect()->toRoute("front-role-acl-links/admin", array("role_id" => $role_id));
		}//end if

		//load the form
		$form = $this->getRoleAclLinksModel()->getRoleAclResourceAllocateSystemForm();

		switch ($resource_type)
		{
			case "api":
				$objResource = $this->getRoleAclLinksModel()->fetchApiAclResource($resource_id);
				//amend some values to bind to the form
				$objResource->set("fk_id_api_acl_resources", $objResource->id);
				break;

			case "core":
				$objResource = $this->getRoleAclLinksModel()->fetchCoreAclResource($resource_id);
				//amend some values to bind to the form
				$objResource->set("fk_id_core_acl_resources", $objResource->id);
				break;

			default:
				//set error message
				$this->flashMessenger()->addErrorMessage("Resource could not be loaded. Invalid resource type specified");

				//redirect back the role acl link index page
				return $this->redirect()->toRoute("front-role-acl-links/admin", array("role_id" => $role_id));
				break;
		}//end switch

		//add role id to entity
		$objResource->set("fk_id_users_roles", $role_id);

		//bind resource data to the form
		$form->bind($objResource);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				try {
					//extract data from the form
					$objRoleAclResourceLink = $form->getData();

					//create the link
					$this->getRoleAclLinksModel()->createRoleAclResourceLink($objRoleAclResourceLink, $resource_type);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Resource has been allocated to role successfully");

					//redirect back to the role acl links index page
					return $this->redirect()->toRoute("front-role-acl-links/admin", array("role_id" => $role_id));
				} catch (\Exception $e) {
					$form_message = $e->getMessage() . " " . $e->getPrevious();
				}//end catch
			}//end function
		}//end if

		return array(
				"objRole" => $objRole,
				"objResource" => $objResource,
				"form" => $form,
				"form_message" => $form_message,
		);
	}//end function

	public function updateAclResourceAction()
	{
		/**
		 * Role id is substituted for the Role Acl Link record id
		 */
		$resource_id = $this->params()->fromQuery("rid");

		//load the form
		$form = $this->getRoleAclLinksModel()->getRoleAclResourceAllocateSystemForm();

		//load the Role Acl Resouce
		$objResource = $this->getRoleAclLinksModel()->fetchRoleAclResourceRecord($resource_id);

		//load the role from the data received
		$objRole = $this->getUserRolesModel()->fetchUserRole($objResource->get("fk_id_users_roles"));

		$form->bind($objResource);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			try {
				if ($form->isValid())
				{
					$objResource = $form->getData();

					//set resource id from route
					$objResource->set("id", $resource_id);

					//update the resource
					$objResource = $this->getRoleAclLinksModel()->updateRoleAclResourceLink($objResource);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Role Resouce has been updated successfully");

					//redirect back to the role resources index page
					return $this->redirect()->toRoute("front-role-acl-links/admin", array("role_id" => $objResource->get("fk_id_users_roles")));
				}//end if
			} catch (\Exception $e) {
				//set error message
				$this->flashMessenger()->addErrorMessage($e->getMessage());

				//redirect back to the roles index page as we dont know if we wont have the role id
				return $this->redirect()->toRoute("front-users-roles/admin");
			}//end if
		}//end if

		return array(
				"objRole" => $objRole,
				"form" => $form,
		);
	}//end function

	public function deleteAclResourceAction()
	{
		/**
		 * Role id is substituted for the Role Acl Link record id
		 */
		$role_id = $this->params()->fromRoute("role_id");
		$resource_id = $this->params()->fromQuery("rid", "");
		
		if ($resource_id == "")
		{
			$this->flashMessenger()->addErrorMessage("Resource cannot be removed. ID is not set");
			//return to the index page
			return $this->redirect()->toRoute("front-role-acl-links/admin", array("role_id" => $role_id));
		}//end if
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			if ($request->getPost("delete") == "Yes")
			{
				try{
					//load the entity
					$objRoleAclResourceLink = $this->getRoleAclLinksModel()->fetchRoleAclResourceRecord($role_id);

					//delete the record
					$this->getRoleAclLinksModel()->deleteRoleAclResourceLink($objRoleAclResourceLink);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Role Resource has successfully been removed");
				} catch (\Exception $e) {
					$this->flashMessenger()->setErrorMessage($e->getMessage());
				}//end catch
			}//end if
			
			//return to the index page
			return $this->redirect()->toRoute("front-role-acl-links/admin", array("role_id" => $role_id));
		}//end if
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
	 * Create an instance of the Front Users Roles Acl Links Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUsersRolesAclLinksModel
	 */
	private function getRoleAclLinksModel()
	{
		if (!$this->model_role_acl_links)
		{
			$this->model_role_acl_links = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersRolesAclLinksModel");
		}//end if

		return $this->model_role_acl_links;
	}//end function
}//end class