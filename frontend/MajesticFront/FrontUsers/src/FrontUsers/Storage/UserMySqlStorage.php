<?php
namespace FrontUsers\Storage;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontUsers\Entities\FrontUserEntity;
use FrontUsers\Entities\FrontUserSettingsEntity;

class UserMySqlStorage extends AbstractCoreAdapter
{
	/**
	 * Container for the Users Table
	 * @var \FrontUsers\Tables\UsersTable
	 */
	private $table_users;

	/**
	 * Container for the User Settings Table
	 * @var \FrontUsers\Tables\UserSettingsTable
	 */
	private $table_user_settings;

	/**
	 * Container for the User Preferences Table
	 * @var \FrontUsers\Tables\UserPreferencesTable
	 */
	private $table_user_preferences;

	/**
	 * Container for dataset
	 * @var stdClass
	 */
	private $objData;

	public function readData($key)
	{
		switch($key)
		{
			case "profile_id":
			case "uname":
			case "email":
				//load the user
				$objUser = $this->fetchUserData(array(
					"uname" => $this->objData->uname,
					"pword" => $this->objData->pword,
				));
				return $objUser->get("key");
				break;

			case "cookie_data":
				$objUser = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserEntity");
				$objUser->set("id", $this->objData->id);

				$objUserPreferences = $this->fetchUserPreferences($objUser);
				$objData = (object) $objUserPreferences->getArrayCopy();
				return $objData->data;
				break;

			default:
				$objUser = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserEntity");
				$objUser->set("id", $this->objData->id);
				$objUserSettings = $this->fetchUserSettings($objUser);
				$objData = (object) $objUserSettings->getArrayCopy();
	
				//append preferences to data
				$objUserPreferences = $this->fetchUserPreferences($objUser);
				$objPreferences = (object) $objUserPreferences->getArrayCopy();
				foreach ($objPreferences->data as $k => $d)
				{
					$objData->data->$k = $d;
				}//end foreach

				return $objData->data;
				break;
		}//end switch
	}//end function

	public function saveData($key, $data)
	{
		$objUser = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserEntity");
		$objUser->set("id", $this->objData->id);

		switch($key)
		{
			case "cookie_data":
				$objUserPreferences = $this->fetchUserPreferences($objUser);
				$objData = (object) $objUserPreferences->get("data");
				if (!isset($objData->cookie_data))
				{
					$objData->cookie_data = (object) array();
				}//end if

				$objData->cookie_data = $data;
				$objUserPreferences->set("data", $objData);
				$this->saveUserPreferences($objUserPreferences);
				break;

			default:
				$objUserSettings = $this->fetchUserSettings($objUser);
				$objUserSettings->set("key", $data);
				$this->saveUserSettings($objUserSettings);
				break;
		}//end switch
	}//end function

	public function setUserData($objUserData)
	{
		if ($objUserData instanceof \Zend\Session\Container)
		{
			$objUserData = (object) $objUserData->getArrayCopy();
		}//end if

		$this->objData = $objUserData;
		$objUserEntity = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserEntity");
		$objUserEntity->set($this->objData);
		$objUserEntity->set("uname", $objUserEntity->get("uname_secure"));
		$objUserEntity->set("pword", $objUserEntity->get("pword_secure"));
		$objUserEntity->set("email", $objUserEntity->get("email"));
		$objUserEntity->set("profile_id", $objUserEntity->get("id"));
		$objUserEntity->set("profile_identifier", $objUserData->profile_identifier_secure);
		$id = $objUserEntity->get("id");
		$objUserEntity->remove("id");

		//check if user exists already
		$objUser = $this->fetchUserData(array(
			\FrontUsers\Tables\UsersTable::$tableName . ".uname" => $objUserEntity->get("uname"),
			\FrontUsers\Tables\UsersTable::$tableName . ".pword" => $objUserEntity->get("pword"),
		));

		if (!$objUser)
		{
			//try using the users id, password might have been updated
			//check if user exists
			$objUser = $this->fetchUserData(array(
					\FrontUsers\Tables\UsersTable::$tableName . ".profile_id" => $id,
					\FrontUsers\Tables\UsersTable::$tableName . ".profile_identifier" => $objUserEntity->get("profile_identifier"),
			));
			if (is_object($objUser))
			{
				//update user details
				$objUserEntity->set("id", $objUser->get("id"));
			}//end if

			//create records
			$objUser = $this->saveUserData($objUserEntity);
		}//end if

		//set primary key for user in data of other operations
		$this->objData->id = $objUser->get("id");
	}//end function

	/**
	 * Load user data
	 * @return \FrontUsers\Entities\FrontUserEntity
	 */
	public function fetchUserData(array $arr_where)
	{
		return $this->getUsersTable()->selectUser($arr_where);
	}//end function

	/**
	 * Save User Settings
	 */
	public function saveUserData(FrontUserEntity $objUser)
	{
		$objUser = $this->getUsersTable()->saveUser($objUser);

		//check if settings have been created
		$objSettings = $this->fetchUserSettings($objUser);
		if (!$objSettings)
		{
			$objSettings = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserSettingsEntity");
			$objSettings->set("fk_id_users", $objUser->get("id"));
			$objSettings->set("data", $this->objData);
		}//end if
		$objSettings->set("data", $this->objData);
		$this->saveUserSettings($objSettings);

		//check if preferences have been created
		$objPreferences = $this->fetchUserPreferences($objUser);
		if (!$objPreferences)
		{
			$objPreferences = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserSettingsEntity");
			$objPreferences->set("fk_id_users", $objUser->get("id"));
			$objPreferences->set("data", (object) array("cookie_data" => (object) array()));
			$this->saveUserPreferences($objPreferences);
		}//end if

		return $objUser;
	}//end function

	/**
	 * Function exists to indicate data should be refreshed where a user logs in and data is cached
	 * @param FrontUserEntity $objUser
	 */
	public function resetUserDataOnLogin(FrontUserEntity $objUser)
	{

	}//end function

	/**
	 * Remove user data where requested
	 * Used to refresh cache user data
	 * @param FrontUserEntity $objUser
	 */
	public function clearUserSettings()
	{
		$this->getUserSettingsTable()->deleteUserSettings($this->objData->id);
	}//end function
	
	public function clearUserPreferences()
	{
		
	}//end function

	/**
	 * Load user settings
	 * @return \FrontUsers\Entities\FrontUserSettingsEntity
	 */
	public function fetchUserSettings(FrontUserEntity $objUser)
	{
		return $this->getUserSettingsTable()->selectUserSettings($objUser);
	}//end function

	/**
	 * Load user preferences
	 * @param FrontUserEntity $objUser
	 * @return \FrontUsers\Entities\FrontUserSettingsEntity
	 */
	public function fetchUserPreferences(FrontUserEntity $objUser)
	{
		return $this->getUserPreferencesTable()->selectUserSettings($objUser);
	}//end function

	/**
	 * Save user settings
	 * @param FrontUserSettingsEntity $objSettings
	 */
	public function saveUserSettings(FrontUserSettingsEntity $objSettings = NULL)
	{
		if (is_null($objSettings))
		{
			$objSettings = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserSettingsEntity");
			$objSettings->set("fk_id_users", $this->objData->id);
			$objSettings->set("data", $this->objData);
			$this->saveUserSettings($objSettings);
		}//end if
		
		$this->getUserSettingsTable()->saveUserSettings($objSettings);
	}//end function

	/**
	 * Save user preferences
	 * @param FrontUserSettingsEntity $objSettings
	 */
	public function saveUserPreferences(FrontUserSettingsEntity $objSettings)
	{
		$this->getUserPreferencesTable()->saveUserSettings($objSettings);
	}//end function

	/**
	 * Create an instance of the Users Table using the Service Manager
	 * @return \FrontUsers\Tables\UsersTable
	 */
	private function getUsersTable()
	{
		if (!$this->table_users)
		{
			$this->table_users = $this->getServiceLocator()->get('FrontUsers\Tables\UsersTable');
		}//end if

		return $this->table_users;
	}//end function

	/**
	 * Create an instance of the User Settings Table using the Service Manager
	 * @return \FrontUsers\Tables\UserSettingsTable
	 */
	private function getUserSettingsTable()
	{
		if (!$this->table_user_settings)
		{
			$this->table_user_settings = $this->getServiceLocator()->get('FrontUsers\Tables\UserSettingsTable');
		}//end if

		return $this->table_user_settings;
	}//end function

	/**
	 * Create an instance of the User Preferences Table using the Service Manager
	 * @return \FrontUsers\Tables\UserPreferencesTable
	 */
	private function getUserPreferencesTable()
	{
		if (!$this->table_user_preferences)
		{
			$this->table_user_preferences = $this->getServiceLocator()->get('FrontUsers\Tables\UserPreferencesTable');
		}//end if

		return $this->table_user_preferences;
	}//end function
}//end class