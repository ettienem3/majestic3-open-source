<?php
namespace FrontUsers\Models;

use FrontCore\Adapters\AbstractCoreAdapter;	
use FrontUsers\Entities\FrontUserRoleAdminEntity;
use FrontUsers\Entities\FrontUserEntity;

class FrontUserRolesAdminModel extends AbstractCoreAdapter
{
	/**
	 * Load the User Role Admin System Form
	 * @return \Zend\Form\Form
	 */
	public function getUserRoleAdminSystemForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")->getSystemForm("Core\Forms\SystemForms\Users\UserRolesAdminForm");
		return $objForm;
	}//end function
	
	/**
	 * Load the User Role Allocation System Form
	 * @return \Zend\Form\Form
	 */
	public function getUserRoleAllocationSystemForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")->getSystemForm("Core\Forms\SystemForms\Users\UserAllocateRolesForm");
		return $objForm;
	}//end function
	
	/**
	 * Load a list of user roles available for allocation
	 * @param array $arr_where - Optional
	 * @return StdClass
	 */
	public function fetchUserRoles($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("users/admin/roles/manage");
		
		//execute
		$objUserRoles = $objApiRequest->performGETRequest($arr_where)->getBody();

		return $objUserRoles;
	}//end function
	
	/**
	 * Request details about a specific user role
	 * @param mixed $id
	 * @return \FrontUsers\Entities\FrontUserRoleAdminEntity
	 */
	public function fetchUserRole($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("users/admin/roles/manage/$id");
		
		//execute
		$objUserRole = $objApiRequest->performGETRequest(array("id" => $id))->getBody();
		
		//create link entity
		$objUserRole = $this->createUserRoleAdminEntity($objUserRole->data);
		return $objUserRole;
	}//end function
	
	/**
	 * Create an User Role
	 * @trigger createUserRole.pre, createUserRole.post
	 * @param array $arr_data
	 * @return \FrontUsers\Entities\FrontUserRoleAdminEntity
	 */
	public function createUserRole($arr_data)
	{
		//create User Role Admin Entity
		$objUserRole = $this->createUserRoleAdminEntity($arr_data);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objUserRole" => $objUserRole));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("users/admin/roles/manage");
		
		//execute
		$objUserRole = $objApiRequest->performPOSTRequest($objUserRole->getArrayCopy())->getBody();
		
		//recreate the User Role Entity
		$objUserRole = $this->createUserRoleAdminEntity($objUserRole->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objUserRole" => $objUserRole));
		
		return $objUserRole;
	}//end function
	
	/**
	 * Update an existing User Role
	 * @param FrontUserRoleAdminEntity $objUserRole
	 * @return \FrontUsers\Entities\FrontUserRoleAdminEntity
	 */
	public function updateUserRole(FrontUserRoleAdminEntity $objUserRole)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objUserRole" => $objUserRole));
	
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction($objUserRole->getHyperMedia("edit-user-role")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objUserRole = $objApiRequest->performPUTRequest($objUserRole->getArrayCopy())->getBody();

		//recreate the User Role Entity
		$objUserRole = $this->createUserRoleAdminEntity($objUserRole->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objUserRole" => $objUserRole));
		
		return $objUserRole;
	}//end function
	
	/**
	 * Delete an existing User Role
	 * @param mixed $id
	 * @return \FrontUsers\Entities\FrontUserRoleAdminEntity
	 */
	public function deleteUserRole($id)
	{
		//create the User Role Entity
		$objUserRole = $this->fetchUserRole($id);
		
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objUserRole" => $objUserRole));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction($objUserRole->getHyperMedia("delete-user-role")->url);
		$objApiRequest->setApiModule(NULL);
		
		//execute
		$objUserRole = $objApiRequest->performDELETERequest(array())->getBody();
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objUserRole" => $objUserRole));
		
		return $objUserRole;
	}//end function
	
	/**
	 * Request roles allocated to an user
	 * @trigger : fetchUserAllocatedRoles.pre, fetchUserAllocatedRoles.post
	 * @param mixed $user_id
	 * @return \FrontUsers\Entities\FrontUserRoleAdminEntity
	 */
	public function fetchUserAllocatedRoles($user_id)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("user_id" => $user_id));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
	
		//setup the object and specify the action
		$objApiRequest->setApiAction("users/admin/user/roles/$user_id");
		
		//execute
		$objUserRoles = $objApiRequest->performGETRequest(array("id" => $user_id))->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("user_id" => $user_id, "objUserRoles" => $objUserRoles));
		
		return $objUserRoles->data;
	}//end function
	
	/**
	 * Allocate roles to an user
	 * @trigger : allocateUserRole.pre, allocateUserRole.post
	 * @param array $arr_data - Form data
	 * @param FrontUserEntity $objUser
	 * @return \FrontUsers\Entities\FrontUserEntity
	 */
	public function allocateUserRole(array $arr_data, FrontUserEntity $objUser)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".user.pre", $this, array("objUser"));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("users/admin/user/roles/" . $objUser->id);
		
		//execute
		$objUserRole = $objApiRequest->performPOSTRequest($arr_data)->getBody();			
		
		return $objUser;
	}//end function
	
	/**
	 * Remove roles from an user
	 * @param $id - User Role Record id
	 * @param $user_id - User Id
	 */
	public function removeUserRole($id, $user_id)
	{	
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("id" => $id));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("users/admin/user/roles/$id?uid=$user_id");
		
		//execute
		$objUserRole = $objApiRequest->performDELETERequest(array())->getBody();
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("id" => $id));
	}//end function
	
	public function fetchStandardRole($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		$objApiRequest->setApiAction("admin/access-control/fetch-standard-roles/$id");
		$objStandardRole = $objApiRequest->performGETRequest(array())->getBody();
	
		$objStandardRoleEntity = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserStandardRoleEntity");
		$objStandardRoleEntity->set($objStandardRole->data);
		return $objStandardRoleEntity;
	}//end function
	
	public function fetchStandardRoles()
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		$objApiRequest->setApiAction("admin/access-control/fetch-standard-roles");
		$objStandardRoles = $objApiRequest->performGETRequest(array())->getBody();
	
		$arr = array();
		foreach ($objStandardRoles->data as $objRole)
		{
			if (!is_numeric($objRole->id))
			{
				continue;	
			}//end if
			
			$objStandardRoleEntity = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserStandardRoleEntity");
			$objStandardRoleEntity->set($objRole);
			$arr[] = $objStandardRoleEntity;
		}//end foreach
	
		return (object) $arr;
	}//end function
	
	
	public function createUserRoleFromStandardRole($standard_role_id, $arr_data)
	{
		//create standard role
//@TODO amended to only use standard role as user defined role
// 		$objUserRole = $this->createUserRole($arr_data);
// 		$role_id = $objUserRole->get("id");

		/**
		 * Create user role backed by standard role
		 */
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		$objApiRequest->setApiModule("api");
		$objApiRequest->setApiAction("admin/access-control/fetch-standard-roles/$standard_role_id?role_id=$role_id");
// 		$objStandardRoles = $objApiRequest->performPUTRequest(array("role_id" => $role_id))->getBody();
		$objStandardRoles = $objApiRequest->performPOSTRequest($arr_data)->getBody();
		
	}//end function
	
	/**
	 * Create a User Role Admin Entity
	 * @param mixed $objData
	 * @return \FrontUsers\Entities\FrontUserRoleAdminEntity
	 */
	private function createUserRoleAdminEntity($objData)
	{
		$entity_user_role = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserRoleAdminEntity");
		
		//populate the data
		$entity_user_role->set($objData);
		
		return $entity_user_role;
	}//end function
}//end class