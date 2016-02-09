<?php
namespace FrontUserLogin\Controller;

use FrontUserLogin\Models\FrontUserSession;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use FrontUserLogin\Models\FrontUserLoginModel;
use Zend\Form\Form;

class IndexController extends AbstractActionController
{

	/**
	 * Container for UserLogin instance
	 * @var unknown
	 */
	private $model_userlogin;

	/**
	 * Display User Login Form
	 * @return multitype:\Zend\Form\Form
	 */
	public function loginAction()
	{
		// Load User Login form
		$form = $this->getUserLoginModel()->getUserLoginForm();

		// HTTP request
		$request = $this->getRequest();

		if ($request->isPost())
		{
			// Populate data into User Login form
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				//check terms and conditions
				if ($request->getPost("terms_and_conditions") != "1")
				{
					$this->flashMessenger()->addInfoMessage("You have to accept the Terms and Conditions to sign in");
					return $this->redirect()->toRoute("front-user-login");
				}//end if

				try {
					$arr_data = (array) $form->getData();
					$arr_data["apikey"] = $request->getPost("apikey");
					$objUser = $this->getUserLoginModel()->userLogin($arr_data);
					if (!$objUser)
					{
						//login failed, set message and redirect back to login form
						$this->flashMessenger()->addErrorMessage("Login Failed");
						return $this->redirect()->toRoute("front-user-login");
					}//end if

					//load user prefences
					$objUserData = FrontUserSession::readUserLocalData("cookie_data");
					if (isset($objUserData->cookie_data->home_page) && $objUserData->cookie_data->home_page != "")
					{
						// Redirect to home page
						return $this->redirect()->toUrl($objUserData->cookie_data->home_page);
					} else {
						// Redirect to panels page
						if (is_array($objUser->profile->plugins_enabled) && in_array("panels", $objUser->profile->plugins_enabled))
						{
							return $this->redirect()->toRoute("home");
						} else {
							//redirect to the contacts index page
							return $this->redirect()->toRoute("front-contacts");
						}//end if
					}//end if
				} catch (\Exception $e) {
					// Set unsuccesful message.
					$this->flashMessenger()->addErrorMessage("Login failed");

					//extract error details
					$arr_t = explode("||", $e->getMessage());
					$objResponse = json_decode(array_pop($arr_t));
					if (is_object($objResponse))
					{
						$arr_tt = explode(":", $objResponse->HTTP_RESPONSE_MESSAGE);
						if (is_array($arr_tt))
						{
							$t = array_pop($arr_tt);

						}//end if

						switch ($objResponse->HTTP_RESPONSE_CODE)
						{
							case 999:
								if ($t != "")
								{
									$this->flashMessenger()->addErrorMessage($t);
								}//end if
								break;

							default:
								trigger_error($t, E_USER_WARNING);
								break;
						}//end switch
					}//end if

					//redirect back to login page
 					return $this->redirect()->toRoute("front-user-login");
				} // end try
			} // end if
		} // end if

		//check if user is already logged in, if so, redirect to the home page
		if (FrontUserSession::isLoggedIn() !== FALSE)
		{
			return $this->redirect()->toRoute("home");
		}//end if

		// load user login form
		return array("form" => $form);
	} // end function

	public function ajaxTermsAction()
	{
		$arr_config = $this->getServiceLocator()->get("config");

		if (!isset($arr_config["login_page_settings"]["terms_source_html"]) || $arr_config["login_page_settings"]["terms_source_html"] == "")
		{
			echo json_encode(array("error" => 1), JSON_FORCE_OBJECT);
			exit;
		}//end if

		//load content
		$content = file_get_contents($arr_config["login_page_settings"]["terms_source_html"]);
		echo json_encode(array("error" => 0, "response" => $content), JSON_FORCE_OBJECT);
		exit;
	}//end function

	public function logoutAction()
	{
		try {
			//log user out of session
			$this->getUserLoginModel()->userLogout();
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
		}//end catch

		//redirect back to the login screen
		return $this->redirect()->toRoute("front-user-login");
	}//end function

	public function userNativePreferencesAction()
	{
		//check if user is already logged in, if so, redirect to the home page
		if (FrontUserSession::isLoggedIn() === FALSE)
		{
			return $this->redirect()->toRoute("home");
		}//end if

		//set layout
		$this->layout("layout/layout");
		
		//check if local storage has been enabled
		$arr_config = $this->getServiceLocator()->get("config");
		if (!isset($arr_config["logged_in_user_settings"]))
		{
			$storage_disabled = TRUE;
		} elseif (isset($arr_config["logged_in_user_settings"]) && $arr_config["logged_in_user_settings"]["storage_enabled"] !== TRUE) {
			$storage_disabled = TRUE;
		}//end if

		if (isset($storage_disabled))
		{
			$this->flashMessenger()->addInfoMessage("User preferences cannot be saved. Service is not enabled");
			return $this->redirect()->toRoute("home");
		}//end if
		
		//load form
		$form = $this->getUserLoginModel()->getUserNativePreferencesForm($this);

		//load user preferences
		$objUserData = FrontUserSession::readUserLocalData('cookie_data');	
		foreach ($objUserData->cookie_data as $key => $value)
		{
			if ($form->has($key))
			{
				$form->get($key)->setValue($value);
			}//end if
		}//end foreach

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				try {
					$arr_data = (array) $form->getData();
					foreach ($arr_data as $key => $value)
					{
						$objUserData->cookie_data->$key = $value;
					}//end foreach

					//save the data
					FrontUserSession::saveUserLocalData("cookie_data", $objUserData->cookie_data);

					$this->flashMessenger()->addSuccessMessage("Preferences saved");
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage($e->getMessage);
				}//end catch
			}//end if
		}//end if

		return array(
				"form" => $form,
		);
	}//end function
	
	public function userSettingsAction()
	{
		//check if user is already logged in, if so, redirect to the home page
		$objUser = FrontUserSession::isLoggedIn();
		if ($objUser === FALSE)
		{
			return $this->redirect()->toRoute("home");
		}//end if

		//set layout
		$this->layout("layout/layout");
		
		//create form
		$objForm = new Form();
		$objForm->add(array(
				"type" => "text",
				"name" => "locale_timezone",
				"attributes" => array(
					"id" => "locale_timezone",
					"disabled" => "disabled",
					"title" => "Timezone currently set for your profile",
				),
				"options" => array(
						"label" => "Timezone",
				),
		));
		
		//populate form values using user settings
		foreach ($objForm as $objElement)
		{
			$objForm->get($objElement->getName())->setValue($objUser->user_settings->{$objElement->getName()});
		}//end foreach
		
		return array(
			"objUser" => $objUser,
			"form" => $objForm,
		);
	}//end function

	/**
	 * Create an instance of the FrontUserLoginModel using the Service Manager
	 * @return \FrontUserLogin\Models\FrontUserLoginModel
	 */
    private function getUserLoginModel()
    {
    	if(!$this->model_userlogin)
    	{
    		$this->model_userlogin = $this->getServiceLocator()->get("FrontUserLogin\Models\FrontUserLoginModel");
    	}//end if

    	return $this->model_userlogin;
    }//end function
}//end class
