<?php
namespace FrontUsers\Controller;

use FrontUserLogin\Models\FrontUserSession;
use Zend\View\Model\JsonModel;
use FrontCore\Adapters\AbstractCoreActionController;

class IndexController extends AbstractCoreActionController
{
	/**
	 * Container for Users Model instance
	 * @var \FrontUsers\Models\FrontUsersModel
	 */
	private $model_users;

	/**
	 * Loads list of Users using APIUsers.
	 * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
	 */
	public function indexAction()
	{
		$objUsers = $this->getUsersModel()->fetchUsers($this->params()->fromQuery());
		return array("objUsers" => $objUsers);
	}//end function

	public function ajaxLoadUsersAction()
	{
		$objUsers = $this->getUsersModel()->fetchUsers($this->params()->fromQuery());
		$arr_data = array();

		foreach ($objUsers as $objUser)
		{
			if ($objUser->active != 1)
			{
				continue;
			}//end if

			$arr_data[] = (object) array(
				'id' => $objUser->id,
				'uname' => $objUser->uname,
			);
		}//end foreach

		return new JsonModel($arr_data);
	}//end function

	/**
	 * Create a new User
	 * @return multitype:\Zend\Form\Form
 	 */
	public function createAction()
	{
		// Load User form
		$form = $this->getUsersModel()->getUserSystemForm();

		//set default options
		$arr_form_field_set = array(
			"country_id" => 0,
			"locale_timezone" => "Africa/Johannesburg",
			"user_details" => '#user_fname #user_sname<br />#user_designation<br />#user_company_name<br />E-mail: <a href="mailto:#user_email">#user_email</a><br />Telephone: #user_work_num<br />Cellphone: #user_cell_num<br />Fax: #user_fax_num',
			"registrations_limit" => 20,
			"allocate" => 0,
			"available_on_forms" => 1,
			"add_leads" => 1,
			"registrations_bin" => 0,
			"export_reports" => 0,
			"notify_form" => 0,
			"encoded" => 0,
			"view_others_tasks" => 0,
			"tasks_set_all_users" => 0,
			"appointments" => 0,
			"disable_login" => 0,
			"bulk_approve" => 0,
		);

		foreach ($arr_form_field_set as $field => $value)
		{
			if ($form->has($field))
			{
				$form->get($field)->setValue($value);
			}//end if
		}//end foreach

		// HTTP request
		$request = $this->getRequest();

		if ($request->isPost())
		{
			// Populate data into User form
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				try {
					$objUser = $this->getUsersModel()->createUser($form->getData());

					// Set successful message
					$this->flashMessenger()->addSuccessMessage("User created successfully");

					//set info message and redirect to allocating roles for the user
					$this->flashMessenger()->addInfoMessage("Roles need to be allocated to the user, you can do so now with the details below");

					// Redirect to index page.
					return $this->redirect()->toRoute("front-users-roles/user", array("action" => "allocate-role", "user_id" => $objUser->get("id")));
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				} // end try{}
			} // end if
		} // end if
		// Load form of User
		return array("form" => $form);
	} // end function

	/**
	 * Update an existing User
	 * @return multitype:\Zend\Form\Form
	 */
	public function editAction()
	{
		// Get ID from route
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			// Set unsuccessful message
			$this->flashMessenger()->addErrorMessage("User could not be loaded. ID is not set.");

			// Redirect to index page
			return $this->redirect()->toRoute("front-users");
		}//end if

		// Load existing User details
		$objUser = $this->getUsersModel()->fetchUser($id);

		// Load form of user
		$form = $this->getUsersModel()->getUserSystemForm();

		//remove required attribute from password field
		$form->get("pword")->setAttribute("required", FALSE);
		$objUser->set("pword", "");

		// Populate specific User.ID
		$form->bind($objUser);

		// Loads HTTP request.
		$request = $this->getRequest();
		if ($request->isPost())
		{
			// Load data into form of User
			$form->setData($request->getPost());
			if ($request->getPost("pword") == "")
			{
				$form->remove("pword");
			}//end if
			if ($form->isValid())
			{
				try {
					$objUser = $form->getData();
					$objUser->set("id", $id);

					if ($request->getPost("pword") == "")
					{
						$objUser->set("pword", "");
					}//end if

					$objUser = $this->getUsersModel()->updateUser($objUser);

					// Set successful message
					$this->flashMessenger()->addSuccessMessage("User details have been saved");

					//check if logged in user updated its own details
					$objUserSession = FrontUserSession::isLoggedIn();
					if ($objUserSession->id == $objUser->get("id") && $request->getPost("pword") != "")
					{
						//log user out
						$this->flashMessenger()->addInfoMessage("Password change has been detected. Please login to continue");
						return $this->redirect()->toRoute("front-user-login", array("action" => "logout"));
					}//end if

					// Redirect to index page
					return $this->redirect()->toRoute("front-users");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				} //end try{}
			} //end if
		} // end if

		// Load form of User
		return array("form" => $form, "objUser" => $objUser);
	} // end function


	/**
	 * Delete an existing User
	 */
	public function deleteAction()
	{
		// Get User ID
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			// Set unsuccessful message
			$this->flashMessenger()->addErrorMessage("User could not be deleted. ID is not set.");
			// Return to index page.
			return $this->redirect()->toRoute("front-users");
		}

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{
				try {
					$objUser = $this->getUsersModel()->deleteUser($id);

					// Set success message
					$this->flashMessenger()->addSuccessMessage("User deleted successfully");
				} catch (\Exception $e) {
    				//set error message
    				$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
				} // end try{}
			}//end if

			// Redirect to index page.
			return $this->redirect()->toRoute("front-users");
		}//end if

		//load the user
		$objUser = $this->getUsersModel()->fetchUser($id);
		return array(
			"objUser" => $objUser,
		);
	} // end deleteAction()


	/**
	 * Update Users.active column Active/Inactive
	 */
	public function statusAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			// Set ID unssuccessful message.
			$this->flashMessenger()->addErrorMessage("User status could not be updated. ID is not set.");

			// Return to the index page.
			return $this->redirect()->toRoute("front-users");
		}//end if

		try {
			// Load specific form of User details
			$objUser = $this->getUsersModel()->fetchUser($id);
			$objUser->set("active", (1 - $objUser->get("active")));
			$objUser = $this->getUsersModel()->manageUserStatus($objUser);

			// Set successfull message.
			$this->flashMessenger()->addSuccessMessage("User status has been updated");
		} catch (\Exception $e) {
			//extract message
			$arr = explode("||", $e->getMessage());
			$objResult = json_decode($arr[1]);

			// Set unsuccessful message.
			$this->flashMessenger()->addErrorMessage($objResult->HTTP_RESPONSE_MESSAGE);
			$this->flashMessenger()->addErrorMessage("There are values required which are not set for the user");

			//redirect to user update screen.
			return $this->redirect()->toRoute("front-users", array("id" => $id, "action" => "edit"));
		} // end try{}

		return $this->redirect()->toRoute("front-users");
	} // end function

	/**
	 * Creates an instance of Users model using the Service Manager\Locator (SM\L).
	 * @return \FrontUsers\Models\FrontUsersModel
	 */
	private function getUsersModel()
	{
		if (!$this->model_users)
		{
			$this->model_users = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersModel");
		}//end if

		return $this->model_users;
	} // end function
} // end class
