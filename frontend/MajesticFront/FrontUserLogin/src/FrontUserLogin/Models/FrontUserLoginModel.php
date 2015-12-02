<?php
namespace FrontUserLogin\Models;

use Zend\Session\Container;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontUserLogin\Forms\FrontUserLoginForm;
use FrontUserLogin\Forms\NativeUserPreferencesForm;

class FrontUserLoginModel extends AbstractCoreAdapter
{
	/**
	 * Container for the User Session Storage Container
	 * @var \/FrontUserLogin\Models\FrontUserSession
	 */
	private $user_session;

	/**
	 * Get FrontUserLoginForm
	 * @return $form
	 */
	public function getUserLoginForm()
	{
		$form = new FrontUserLoginForm();
		return $form;
	} //end function

	public function userLogin($arr_data)
	{
		$uname = $arr_data["uname"];
		$pword = $arr_data["pword"];

		//init user local storage
		$objUserStorage = \FrontUserLogin\Models\FrontUserSession::getUserLocalStorageObject();
		if ($objUserStorage instanceof \FrontUsers\Storage\UserMySqlStorage)
		{
			//check if user has data stored locally
			$objUserEntity = $objUserStorage->fetchUserData(array(
				"uname" => $this->getServiceLocator()->get("FrontCore\Models\Security\CryptoModel")->sha1EncryptDecryptValue("encrypt", $uname, array()),
				"pword" => $this->getServiceLocator()->get("FrontCore\Models\Security\CryptoModel")->sha1EncryptDecryptValue("encrypt", md5($pword), array()),
				//new \Zend\Db\Sql\Predicate\Operator("expires", ">", time()),
			));

			if (is_object($objUserEntity))
			{
				//lod user settings
				$objUserSettings = $objUserStorage->fetchUserSettings($objUserEntity);

				//create session for user
				$objUserSession = $this->getUserSessionContainer();

				//set login timeout
				$objUserSession->createUserSession($objUserSettings->get("data"));

				//trigger background update of user data...
				//@TODO
				return $objUserSettings;
			}//end if
		}//end if

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("user/authenticate");

		//set api request authentication details
		$objApiRequest->setAPIKey($arr_data['apikey']);
		$objApiRequest->setAPIUser(md5($arr_data['uname']));
		$objApiRequest->setAPIUserPword(md5($arr_data['pword']));

		//execute
		$objUser = $objApiRequest->performPOSTRequest($arr_data)->getBody();

		//check if access control rules are available
		if (count($objUser->data->acl->user_acl_access_allowed) == 0)
		{
			//reset the API request object
			//trigger an arbitrary api request to activate access control for the user
			try {
				$objApiRequest->setApiAction("links/admin");
				$objApiRequest->setAPIKey($objUser->data->api_key);
				$objApiRequest->setAPIUser($objUser->data->uname);
				$objApiRequest->setAPIUserPword($objUser->data->pword);
				$objApiRequest->performGETRequest();
			} catch (\Exception $e) {
				//ignore error
			}//end catch

			//perform login action again
			$objApiRequest->setApiAction("user/authenticate");
			$objUser = $objApiRequest->performPOSTRequest($arr_data)->getBody();
		}//end if

		//save data to local storage
		if ($objUserStorage instanceof \FrontUsers\Storage\UserMySqlStorage)
		{
			$objUser->data->uname_secure = $this->getServiceLocator()->get("FrontCore\Models\Security\CryptoModel")->sha1EncryptDecryptValue("encrypt", $uname, array());
			$objUser->data->pword_secure = $this->getServiceLocator()->get("FrontCore\Models\Security\CryptoModel")->sha1EncryptDecryptValue("encrypt", md5($pword), array());
			$objUser->data->profile_identifier_secure = $this->getServiceLocator()->get("FrontCore\Models\Security\CryptoModel")->sha1EncryptDecryptValue("encrypt", $objUser->data->profile->profile_identifier, array());
			$objUserStorage->setUserData($objUser->data);
		}//end if

		//create session for user
		$objUserSession = $this->getUserSessionContainer();

		//set login timeout
		$objUserSession->createUserSession($objUser->data);

		return $objUser;
	}//end function

	public function userLogout()
	{
		//notify the api of logout action, this remove the user cache from core
		$objUserSession = new Container("user");

		try {
			//create the request object
			$objApiRequest = $this->getApiRequestModel();
			//setup the object and specify the action
			$objApiRequest->setApiAction("user/authenticate/" . $objUserSession->id);
			$objApiRequest->performDELETERequest(array("id" => $objUserSession->id));
		} catch (\Exception $e) {
			//ignore error
		}//end catch

		$this->getUserSessionContainer()->destroyUserSession();

		if (session_status() != PHP_SESSION_NONE)
		{
			session_destroy();
		}//end if
	}//end function

	public function getUserNativePreferencesForm($objController)
	{
		$objForm = new NativeUserPreferencesForm();
		$objUser = \FrontUserLogin\Models\FrontUserSession::isLoggedIn();

		//load contact profile forms
		$arr_forms = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsModel")->getContactProfileForm();

		//set no option
		$arr_element_value_options = array();
		$arr_element_value_options["none"] = "No Layout";

		//create element options
		foreach ($arr_forms as $key => $form_name)
		{
			if (is_numeric($key))
			{
				$arr_element_value_options[$key] = $form_name;
			}//end if
		}//end foreach
		$objForm->get("cpp_form_id")->setValueOptions($arr_element_value_options);
		$objForm->get("cpp_layout_id")->setValueOptions($arr_element_value_options);

		//set home page locations
		$arr_locations = array(
				$objController->url()->fromRoute("front-contacts") 						=> "My Contacts",
				$objController->url()->fromRoute("front-comms-admin/journeys") 			=> "My Journeys",
				$objController->url()->fromRoute("front-form-admin") 					=> "My Forms",
				$objController->url()->fromRoute("front-users") 						=> "Manage Users",
		);

		//check if panels is enabled for profile
		if (in_array("panels", $objUser->profile->plugins_enabled))
		{
			$arr_locations[$objController->url()->fromRoute("front-panels-display")] = "My Panels";
		}//end if
		$objForm->get("home_page")->setValueOptions($arr_locations);

		//set news feed options
		$arr_config = $this->getServiceLocator()->get("config")["profile_config"];
		if (!isset($arr_config["news_feed_credentials"]))
		{
			$objForm->remove("news_feed_options");
		}//end if

		//set contact toolkit default tab options
		$arr_contact_toolkit_default_tab = array(
				"comments" => "Comments",
				"forms" => "Forms",
				"journeys" => "Journeys",
				"status-history" => "Status History",
		);

		//add some more items to the contact toolkit default tabs list
		if (in_array("to_do_list", $objUser->profile->plugins_enabled))
		{
			$arr_contact_toolkit_default_tab["user-tasks"] = "To-Do List";
		}//end if

		if (in_array("sales_funnels", $objUser->profile->plugins_enabled))
		{
			$arr_contact_toolkit_default_tab["sales-funnels"] = "Trackers";
		}//end if

// 		$objForm->get("contacts_toolkit_default_tab")->setValueOptions($arr_contact_toolkit_default_tab);

		return $objForm;
	}//end function

	/**
	 * Load user session container using the Service Manager
	 * @return \FrontUserLogin\Models\FrontUserSession
	 */
	private function getUserSessionContainer()
	{
		if (!$this->user_session)
		{
			$this->user_session = $this->getServiceLocator()->get("FrontUserLogin\Models\FrontUserSession");
		}//end if

		return $this->user_session;
	}//end function
}//end class
