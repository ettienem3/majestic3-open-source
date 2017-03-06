<?php
namespace FrontUsers\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontUsers\Entities\FrontUsersUserTaskEntity;

class FrontUsersTasksModel extends AbstractCoreAdapter
{
	/**
	 * Load the admin form for User Tasks from Core System Forms
	 * @return \Zend\Form\Form
	 */
	public function getUserTasksForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
					->getSystemForm("Core\Forms\SystemForms\UsersTasks\UsersTasksForm");
		
		return $objForm;
	}//end function
	
	/**
	 * Load a specific user task
	 * @param mixed $id
	 * @return \FrontUsers\Entities\FrontUsersUserTaskEntity
	 */
	public function fetchUserTask($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("user/tasks/manager/$id");
		
		//execute
		$objResult = $objApiRequest->performGETRequest()->getBody();
		
		//create user task entity
		$objUserTask = $this->createUserTaskEntity($objResult->data);
		
		return $objUserTask;
	}//end function
	
	/**
	 * Load a collection of user tasks
	 * @param array $arr_where
	 * @return StdClass
	 */
	public function fetchUserTasks($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("user/tasks/manager");
		
		//execute
		$objResult = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		$arr = array();
		foreach ($objResult->data as $objUserTask)
		{
			//create user task entity
			$objUserTask = $this->createUserTaskEntity($objUserTask);
			$arr[] = $objUserTask;
		}//end foreach
		
		return (object) $arr;
	}//end function
	
	/**
	 * Create a user task
	 * @trigger createUserTask.pre, createUserTask.post
	 * @param array $arr_data
	 * @return \FrontUsers\Entities\FrontUsersUserTaskEntity
	 */
	public function createUserTask($arr_data)
	{
		if (substr($arr_data["datetime_reminder"], -6) == ' 00:00')
		{
			$arr_data["datetime_reminder"] = substr($arr_data["datetime_reminder"], 0, -6);
		}//end if
		
		$objDate = \DateTime::createFromFormat("d M Y H:i:s", $arr_data["datetime_reminder"]);

		if (!is_object($objDate))
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Task could not be created. Reminder date is not in valid format", 500); 	
		}//end if
		$arr_data["datetime_reminder"] = $objDate->format(\DateTime::RFC3339);
		
		if ($arr_data["date_email_reminder"] != "")
		{
			$objDate = \DateTime::createFromFormat("d M Y", $arr_data["date_email_reminder"]);
			if (!is_object($objDate))
			{
				throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Task could not be created. Reminder date is not in valid format", 500);
			}//end if
			$arr_data["date_email_reminder"] = $objDate->format(\DateTime::RFC3339);
		}//end if
	
		//create entity
		$objUserTask = $this->createUserTaskEntity($arr_data);
		
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objUserTask" => $objUserTask));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("user/tasks/manager");

		//execute
		$objResult = $objApiRequest->performPOSTRequest($arr_data)->getBody();
		
		//recreate user task entity
		$objUserTask = $this->createUserTaskEntity($objResult->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objUserTask" => $objUserTask));
		
		return $objUserTask;
	}//end function
	
	/**
	 * Update a user task
	 * @trigger updateUserTask.pre, updateUserTask.post
	 * @param FrontUsersUserTaskEntity $objUserTask
	 * @return \FrontUsers\Entities\FrontUsersUserTaskEntity
	 */
	public function updateUserTask(FrontUsersUserTaskEntity $objUserTask, $complete = 0)
	{
		$arr_data = $objUserTask->getArrayCopy();
		if (substr($arr_data["datetime_reminder"], -6) == ' 00:00')
		{
			$arr_data["datetime_reminder"] = substr($arr_data["datetime_reminder"], 0, -6);
		}//end if
	
		$objDate = \DateTime::createFromFormat("d M Y H:i:s", $arr_data["datetime_reminder"]);
		if (!is_object($objDate))
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Task could not be updated. Reminder date is not in valid format", 500); 	
		}//end if
		$arr_data["datetime_reminder"] = $objDate->format(\DateTime::RFC3339);
		
		if ($arr_data["date_email_reminder"] != "" && $arr_data['date_email_reminder'] != '0000-00-00')
		{
			$objDate = \DateTime::createFromFormat("d M Y", $arr_data["date_email_reminder"]);
			if (!is_object($objDate))
			{
				throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Task could not be updated. Reminder date is not in valid format", 500);
			}//end if
			$arr_data["date_email_reminder"] = $objDate->format(\DateTime::RFC3339);
		}//end if
		
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objUserTask" => $objUserTask));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("user/tasks/manager/" . $objUserTask->get("id") . "?complete=$complete");
		
		//execute
		$objResult = $objApiRequest->performPUTRequest($arr_data)->getBody();
		
		//recreate the user task entity
		$objUserTask = $this->createUserTaskEntity($objResult->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objUserTask" => $objUserTask));
		
		return $objUserTask;
	}//end function
	
	/**
	 * Complete a user task
	 * @trigger updateUserTask.pre, updateUserTask.post
	 * @param FrontUsersUserTaskEntity $objUserTask
	 * @return \FrontUsers\Entities\FrontUsersUserTaskEntity
	 */
	public function completeUserTask(FrontUsersUserTaskEntity $objUserTask)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objUserTask" => $objUserTask));
		
		//mark user task as completed
		$objUserTask = $this->updateUserTask($objUserTask, 1);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objUserTask" => $objUserTask));
		
		return $objUserTask;
	}//end function
	
	/**
	 * Delete a user task
	 * @trigger deleteUserTask.pre, deleteUserTask.post
	 * @param mixed $id
	 * @return \FrontUsers\Entities\FrontUsersUserTaskEntity
	 */
	public function deleteUserTask($id)
	{
		//load the data
		$objUserTask = $this->fetchUserTask($id);
		
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objUserTask" => $objUserTask));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("user/tasks/manager/" . $objUserTask->get("id"));
		
		//execute
		$objResult = $objApiRequest->performDELETERequest(array())->getBody();
		
		//create user task entity
		$objUserTask = $this->createUserTaskEntity($objResult->data);
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objUserTask" => $objUserTask));
		
		return $objUserTask;
	}//end function
	
	/**
	 * Create an instance of the User Task Entity
	 * @param mixed $objData
	 * @return \FrontUsers\Entities\FrontUsersUserTaskEntity
	 */
	private function createUserTaskEntity($objData)
	{
		$objUserTask = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUsersUserTaskEntity");
		$objUserTask->set($objData);
		return $objUserTask;
	}//end function
}//end class