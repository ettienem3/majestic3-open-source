<?php
namespace FrontUsers\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontUsers\Entities\FrontUserEntity;

class FrontUsersModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Front User Roles Admin Model
	 * @var \FrontUsers\Models\FrontUserRolesAdminModel
	 */
	private $model_user_roles_admin;

	/**
	 * CREATE user form from Core Systems Forms
	 * @return \Zend\Form\Form
	 */
	public function getUserSystemForm()
	{
		// Instatiate User form from Core Forms.
		$objUser = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")->getSystemForm("Core\Forms\SystemForms\Users\UserForm");
		return $objUser;
	}//end function

	/**
	 * SELECT rows of data from Users query.
	 * @param string $arr_where
	 */
//@TODO delete function
	public function getUsers($arr_where = NULL)
	{
		return self::fetchUsers($arr_where);
	} // end function

	public function fetchUsers($arr_where = array())
	{
		// Create the APIRequest
		$objApiRequest = $this->getApiRequestModel();
		// Setup the object and specify the action
		$objApiRequest->setApiAction("users/admin/user");

		// execute
		$objUsers = $objApiRequest->performGETRequest($arr_where)->getBody();
		return $objUsers->data;
	}//end function

	/**
	 * SELECT specific row of data for User query.
	 * @param string $id
	 * @return \FrontUsers\Entities\FrontUserEntity
	 */
//@TODO delete function
	public function getUser($id)
	{
		return self::fetchUser($id);
	} //end getUser($id = NULL)

	public function fetchUser($id)
	{
		// Create the APIRequest object
		$objApiRequest = $this->getApiRequestModel();

		// Setup the object and specify the action.
		$objApiRequest->setApiAction("users/admin/user/$id");

		// Execute
		$objUser = $objApiRequest->performGETRequest(array("id" => $id))->getBody();

		// Create User entity
		$entity_user = $this->createUserEntity($objUser->data);

		return $entity_user;
	}//end function

	/**
	 * INSERT raw of data into User query.
	 * @param unknown $arr_data
	 * @return \FrontUsers\Entities\FrontUserEntity
	 */
	public function createUser($arr_data)
	{
		// Create object entity;
		$objUser = $this->createUserEntity($arr_data);

		// trigger .pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objUser" => $objUser));

		// Create APIRequest object model
		$objApiRequest = $this->getApiRequestModel();

		// Create APIRequest object and specity the action.
		$objApiRequest->setApiAction("users/admin/user");

		// Execute
		$objUser = $objApiRequest->performPOSTRequest($objUser->getArrayCopy())->getBody();

		// Recreate User entity
		$objUser = $this->createUserEntity($objUser->data);

		// trigger .post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objUser" => $objUser));

		return $objUser;
	} // end createUser($arr_data)


	/**
	 * UPDATE row of data from User query.
	 * @param FrontUserEntity $objUser
	 * @return Ambigous <\FrontCore\Models\Ambigous, \FrontCore\Models\ApiRequestModel, \FrontCore\Models\ApiRequestModel>
	 */
	public function updateUser(FrontUserEntity $objUser)
	{
		// trigger .pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objUser" => $objUser));

		// Create APIRequest object from model
		$objApiRequest = $this->getApiRequestModel();

		// Setup User object and specify action
		$objApiRequest->setApiAction($objUser->getHyperMedia("edit-user")->url);
		$objApiRequest->setApiModule(NULL);

		// Execute
		$objUser = $objApiRequest->performPUTRequest($objUser->getArrayCopy())->getBody();
		// Recreate User entity
		$objUser = $this->createUserEntity($objUser->data);

		// trigger .post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objUser" => $objUser));

		return $objUser;
	} // end updateUser(UserEntity $objUser)

	public function manageUserStatus(FrontUserEntity $objUser)
	{
		// trigger .pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objUser" => $objUser));

		// Create APIRequest object from model
		$objApiRequest = $this->getApiRequestModel();

		// Setup User object and specify action
		$objApiRequest->setApiAction($objUser->getHyperMedia("edit-user-status")->url);
		$objApiRequest->setApiModule(NULL);

		// Execute
		$objUser = $objApiRequest->performPUTRequest($objUser->getArrayCopy())->getBody();
		// Recreate User entity
		$objUser = $this->createUserEntity($objUser->data);

		// trigger .post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objUser" => $objUser));

		return $objUser;
	}//end function

	/**
	 * DELETE row of data from User query.
	 * @param string $id
	 */
	public function deleteUser($id = NULL)
	{
		// Get User object
		$objUser = $this->getUser($id);

		// trigger .pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objUser" => $objUser));

		// Create APIRequestModel
		$objApiRequest = $this->getApiRequestModel();

		// Setup User object and specify thhe action.
		$objApiRequest->setApiAction($objUser->getHyperMedia("delete-user")->url);

		// Clear Module for User.
		$objApiRequest->setApiModule(NULL);

		// Execute
		$objUser = $objApiRequest->performDELETERequest(array())->getBody();

		// trigger .post event
		$this->getEventManager()->trigger(__FUNCTION__ . "post", $this, array("objUser" => $objUser));
	} // end deleteAction()

	/**
	 * Create an instance of the Front User Roles Admin Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUserRolesAdminModel
	 */
	private function getUserRolesAdminModel()
	{
		if (!$this->model_user_roles_admin)
		{
			$this->model_user_roles_admin = $this->getServiceLocator()->get("FrontUsers\Models\FrontUserRolesAdminModel");
		}//end if

		return $this->model_user_roles_admin;
	}//end function

	/**
	 * CREATE entity object for User query.
	 * @param unknown $objData
	 * @return \FrontUsers\Entities\FrontUserEntity
	 */
	private function createUserEntity($objData)
	{
		// Instatiate entity object using SM/L.
		$entity_user = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserEntity");

		// Populate the data
		$entity_user->set($objData);
		return $entity_user;
	}//end function

}//end FrontUsersModel{}
