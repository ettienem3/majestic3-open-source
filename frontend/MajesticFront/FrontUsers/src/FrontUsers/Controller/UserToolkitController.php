<?php
namespace FrontUsers\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class UserToolkitController extends AbstractActionController
{
	/**
	 * Container for the Core User Settings Model
	 * @var \Zend\Session\Container
	 */
	private $objUserSession;

	/**
	 * Container for the Users Model
	 * @var \FrontUsers\Models\FrontUsersModel
	 */
	private $model_users;

	/**
	 * Container for the Tasks Manager Model
	 * @var \FrontUsers\Models\FrontUsersTasksModel
	 */
	private $model_user_tasks;

	public function iframeUserToolkitSectionAction()
	{
		//set layout to toolkit
		$this->layout('layout/toolkit-parent');

		$arr = array(
				"todo" => array("title" => "To-do List", "url" => $this->url()->fromRoute("front-users-toolkit", array("action" => "todo-list"))),
		);
		
		//check plugins enabled
		$objUser = \FrontUserLogin\Models\FrontUserSession::isLoggedIn();
		if (!in_array("to_do_list", $objUser->profile->plugins_enabled))
		{
			unset($arr["todo"]);
		}//end if

		return array(
				"arr_sections" => $arr,
		);
	}//end function

	public function todoListAction()
	{
		$this->renderOutputFormat();

		//load user tasks
		$objUserTasks = $this->getUserTasksModel()->fetchUserTasks(array(
			"users_user_id" => $this->objUserSession->id,
		));

		return array(
				"objUserTasks" => $objUserTasks,
		);
	}//end function

	private function renderOutputFormat($layout = "layout/layout-toolkit-body")
	{
		$this->layout($layout);
		$objUserSession = \FrontUserLogin\Models\FrontUserSession::isLoggedIn();
		$this->objUserSession = $objUserSession;
	}//end function

	/**
	 * Create an instance of the Users Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUsersModel
	 */
	private function getUsersModel()
	{
		if (!$this->model_users)
		{
			$this->model_users = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersModel");
		}//end if

		return $this->model_users;
	}//end function

	/**
	 * Create an instance of the User Tasks Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUsersTasksModel
	 */
	private function getUserTasksModel()
	{
		if (!$this->model_user_tasks)
		{
			$this->model_user_tasks = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersTasksModel");
		}//end if

		return $this->model_user_tasks;
	}//end function
}//end class