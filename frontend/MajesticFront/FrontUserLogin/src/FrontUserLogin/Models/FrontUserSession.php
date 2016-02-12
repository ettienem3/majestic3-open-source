<?php
namespace FrontUserLogin\Models;

use Zend\Session\Container;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontUserSession extends AbstractCoreAdapter
{
	private $user_session;

	/**
	 * Check if a user is logged in
	 * @return boolean/User Session
	 */
	public static function isLoggedIn()
	{
		//instantiate the user session
		$objUserSession = new Container("user");

		if (!isset($objUserSession->id) || $objUserSession->id == "")
		{
			return FALSE;
		}//end if

		//check if timeout expired
		if (time() > $objUserSession->session_timeout)
		{
			//destroy session
			foreach ($objUserSession as $key => $value)
			{
				unset($objUserSession->$key);
			}//end foreach

			return FALSE;
		} else {
			//reset session timeout
			$objUserSession->session_timeout = (time() + (60 * 60));
		}//end if

		return $objUserSession;
	}//end function

	public static function readProfileData()
	{
		$objUserSession = self::isLoggedIn();

		if (!$objUserSession)
		{
			return FALSE;
		}//end if

		return $objUserSession->profile;
	}//end function

	/**
	 *
	 * @return \FrontUsers\Storage\UserFileSystemStorage | \FrontUsers\Storage\UserMySqlStorage
	 */
	public static function getUserLocalStorageObject()
	{
		$sm = \FrontCore\Factories\FrontCoreServiceProviderFactory::getInstance();
		$arr_config = $sm->get("config");
		if (!isset($arr_config["logged_in_user_settings"]))
		{
			return FALSE;
		}//end if

		if (isset($arr_config["logged_in_user_settings"]["storage_enabled"]) && $arr_config["logged_in_user_settings"]["storage_enabled"] !== TRUE)
		{
			return FALSE;
		}//end if

		try {
			$objUserStorage = $sm->get($arr_config["logged_in_user_settings"]["storage"]);
			$objUserStorage->setUserData(self::isLoggedIn());
			return $objUserStorage;
		} catch (\Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}//end catch
	}//end function

	/**
	 * Simple check if a user has access to a given resource
	 * @TODO create proper zend acl
	 * @param string $resource
	 * @return boolean
	 */
	public static function userHasAccess($resource, ServiceLocatorInterface $serviceLocator = NULL)
	{
		//default routes to be allowed
		switch ($resource)
		{
			case "home":
			case "front-profile-native-settings":
			case "front-user-login":
			case "front-profile-settings":
			case "front-locations":
				return TRUE;
				break;
		}//end switch

		//instantiate the user session
		$objUserSession = new Container("user");

		if (!isset($objUserSession->id) || $objUserSession->id == "")
		{
			return FALSE;
		}//end if

		//check api resources
		if (in_array($resource, (array) $objUserSession->acl->user_acl_access_allowed))
		{
 			return TRUE;
		}//end if

		//check user navigation array
		if (!is_array($objUserSession->arr_user_acl))
		{
			//attempt to construct user acl session data via the navigation factory
			$sm = \FrontCore\Factories\FrontCoreServiceProviderFactory::getInstance();
			$objNavigation = $sm->get("FrontCore\Navigation\FrontNavigationFactory");
 			$objNavigation->createService($sm);

			//reload user session
			$objUserSession = new Container("user");
		}//end if

		if (!is_array($objUserSession->arr_user_acl))
		{
			//user acl not set, no point in continuing
			return FALSE;
		}//end if

		if (in_array($resource, $objUserSession->arr_user_acl))
		{
			return TRUE;
		}//end if

		return FALSE;
	}//end function

	/**
	 * Create a user session after login was successful
	 * @param stdClass $objUserData
	 */
	public function createUserSession($objUserData)
	{
		foreach ($objUserData as $key => $value)
		{
			switch ($key)
			{
				case "api_key":
				case "uname":
					$value = $this->getSecurityModel()->encodeValue($value);
					$this->getUserSession()->api_key_encoded = TRUE;
					$this->getUserSession()->$key = $value;
					break;

				default:
					$this->getUserSession()->$key = $value;
					break;
			}//end switch
		}//end foreach

		//set logged in time out
		$this->getUserSession()->session_timeout = (time() + (60 * 60));
		return $this->getUserSession();
	}//end function

	/**
	 * Destroy a user session on logout or expiry
	 */
	public function destroyUserSession()
	{
		//save cookie data to cookie
		//...
		foreach($this->getUserSession() as $key => $value)
		{
			unset($this->getUserSession()->$key);
		}//end foreach

		$_SESSION = array();
		session_destroy();
	}//end function

	/**
	 * Create an instance of the User Session using the Service Manager
	 * @return \Zend\Session\Container
	 */
	private function getUserSession()
	{
		if (!$this->user_session)
		{
			$this->user_session = new Container("user");
		}//end if

		return $this->user_session;
	}//end function

	/**
	 * @return \FrontCore\Models\FrontCoreSecurityModel
	 */
	private function getSecurityModel()
	{
		return $this->getServiceLocator()->get("FrontCore\Models\FrontCoreSecurityModel");
	}//end function
}//end class