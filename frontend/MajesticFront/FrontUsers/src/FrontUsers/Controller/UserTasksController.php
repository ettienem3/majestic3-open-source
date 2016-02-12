<?php
namespace FrontUsers\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class UserTasksController extends AbstractActionController
{
	/**
	 * Container for the User Tasks Model
	 * @var \FrontUsers\Models\FrontUsersTasksModel
	 */
	private $model_user_tasks;
	
	/**
	 * Container for the Users Model
	 * @var \FrontUsers\Models\FrontUsersModel
	 */
	private $model_users;
	
	public function indexAction()
	{
		//set user id
		$user_id = $this->params()->fromRoute("user_id", "");
		if ($user_id == "")
		{
			$this->flashMessenger()->addErrorMessage("User Tasks could not be loaded. User Id is not set");
			return $this->redirect()->toRoute("front-users");
		}//end if
		
		$arr_params = $this->params()->fromQuery();
		$arr_params["users_user_id"] = $user_id;
		
		//load the data
		$objUserTasks = $this->getFrontUserTasksModel()->fetchUserTasks($arr_params);
		
		//load user data
		$objUser = $this->getFrontUsersModel()->fetchUser($user_id);
		
		return array(
			"objUser" => $objUser,
			"objUserTasks" => $objUserTasks,	
		);
	}//end function
	
	public function editAction()
	{
		$id = $this->params()->fromRoute("id", "");
		$user_id = $this->params()->fromRoute("user_id", "");
		if ($id == "" || $user_id == "")
		{
			$this->flashMessenger()->addErrorMessage("User Task could not be updated. Id is not set");
			return $this->redirect()->toRoute("front-users-tasks");
		}//end if
		
		//validate the data
		$objUserTask = $this->getFrontUserTasksModel()->fetchUserTask($id);
		
		//format dates
		$objDate = \DateTime::createFromFormat(\DateTime::RFC3339, $objUserTask->get("datetime_reminder"));
		if (!$objDate)
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : An error occurred setting the reminder date", 500);
		}//end if
		$objUserTask->set("datetime_reminder", $objDate->format("d M Y H:i:s"));
			
		if ($objUserTask->get("date_email_reminder") != "")
		{
			$objDate = \DateTime::createFromFormat(\DateTime::RFC3339, $objUserTask->get("date_email_reminder"));
			if (!$objDate)
			{
				throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : An error occurred setting the email reminder date", 500);
			}//end if
			$objUserTask->set("date_email_reminder", $objDate->format("d M Y"));
		}//end if
		
		//load the form
		$form = $this->getFrontUserTasksModel()->getUserTasksForm();
		$form->bind($objUserTask);
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				try {
					//extract form data
					$objUserTask = $form->getData();
					$objUserTask->set("id", $id);
					
					$objUserTask = $this->getFrontUserTasksModel()->updateUserTask($objUserTask);
					
					//set success message
					$this->flashMessenger()->addSuccessMessage("User task updated successfully");
					
					if ($this->params()->fromQuery("redirect_url") != "")
					{
						return $this->redirect()->toUrl($this->params()->fromQuery("redirect_url"));
					} else {
						//redirect back to task index page for user
						return $this->redirect()->toRoute("front-users-tasks", array("user_id" => $user_id));
					}//end if
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if
		
		return array(
			"form" => $form,
			"user_id" => $user_id,
			"objUserTask" => $objUserTask,	
		);
	}//end function
	
	public function deleteAction()
	{
		$id = $this->params()->fromRoute("id", "");
		$user_id = $this->params()->fromRoute("user_id", "");
		if ($id == "" || $user_id == "")
		{
			$this->flashMessenger()->addErrorMessage("User Task could not be updated. Id is not set");
			return $this->redirect()->toRoute("front-users-tasks");
		}//end if
		
		//validate the data
		$objUserTask = $this->getFrontUserTasksModel()->fetchUserTask($id);
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{
				$this->getFrontUserTasksModel()->deleteUserTask($id);

				//set success message
				$this->flashMessenger()->addSuccessMessage("User Task successfully removed");
			}//end if
				
			return $this->redirect()->toRoute("front-users-tasks", array("user_id" => $user_id));
		}//end if
		
		return array(
			"objUserTask" => $objUserTask,
			"user_id" => $user_id	
		);
	}//end function
	
	public function completeTaskAction()
	{
		$id = $this->params()->fromRoute("id", "");
		$user_id = $this->params()->fromRoute("user_id", "");
		if ($id == "" || $user_id == "")
		{
			$this->flashMessenger()->addErrorMessage("User Task could not be updated. Id is not set");
			return $this->redirect()->toRoute("front-users-tasks");
		}//end if
		
		//validate the data
		$objUserTask = $this->getFrontUserTasksModel()->fetchUserTask($id);
		
		//format dates
		$objDate = \DateTime::createFromFormat(\DateTime::RFC3339, $objUserTask->get("datetime_reminder"));
		if (!$objDate)
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : An error occurred setting the reminder date", 500);
		}//end if
		$objUserTask->set("datetime_reminder", $objDate->format("d M Y H:i:s"));
			
		if ($objUserTask->get("date_email_reminder") != "")
		{
			$objDate = \DateTime::createFromFormat(\DateTime::RFC3339, $objUserTask->get("date_email_reminder"));
			if (!$objDate)
			{
				throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : An error occurred setting the email reminder date", 500);
			}//end if
			$objUserTask->set("date_email_reminder", $objDate->format("d M Y"));
		}//end if
		
		//update the task
		try {
			$objUserTask = $this->getFrontUserTasksModel()->completeUserTask($objUserTask);
			
			//set success message
			$this->flashMessenger()->addSuccessMessage("User Task has been updated");
			
			if ($this->params()->fromQuery("redirect_url") != "")
			{
				return $this->redirect()->toUrl($this->params()->fromQuery("redirect_url"));
			}//end if
		} catch (\Exception $e) {
    		//set error message
    		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
		}//end catch
		
		return $this->redirect()->toRoute("front-users-tasks", array("user_id" => $user_id));
	}//end function
	
	/**
	 * Create an instance of the Front User Tasks Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUsersTasksModel
	 */
	private function getFrontUserTasksModel()
	{
		if (!$this->model_user_tasks)
		{
			$this->model_user_tasks = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersTasksModel");
		}//end if
		
		return $this->model_user_tasks;
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