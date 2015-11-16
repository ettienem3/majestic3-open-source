<?php
namespace FrontUserLogin\Forms;

use FrontCore\Forms\FrontCoreSystemFormBase;

class NativeUserPreferencesForm extends FrontCoreSystemFormBase
{
	public function __construct()
	{
		parent::__construct("user-native-preferences");
		//set form to submit method
		$this->setAttribute("method", "post");
		$this->setAttribute("class", "user-native-preferences-form");
		
		$this->add(array(
				"type" => "select",
				"name" => "cpp_layout_id",
				"attributes" => array(
						"id" => "cpp_layout_id",
						"title" => "Set the default My Contacts Page Layout to use when viewing Contacts",
				),
				"options" => array(
						"label" => "My Contacts Page - Default Layout",
						"empty_option" => "--select--",
						"value_options" => array(
									
						),
				),
				"required" => FALSE,
				"allow_empty" => TRUE
		));
		
		$this->add(array(
				"type" => "select",
				"name" => "cpp_form_id",
				"attributes" => array(
						"id" => "cpp_form_id",
						"title" => "Set default Form to use when creating or editing a Contact",
				),
				"options" => array(
						"label" => "View / Edit Contact - Default Layout",
						"empty_option" => "--select--",
						"value_options" => array(
									
						),
				),
				"required" => FALSE,
				"allowEmpty" => TRUE
		));
		
		$this->add(array(
				"type" => "select",
				"name" => "home_page",
				"attributes" => array(
					"id" => "home_page",
					"title" => "This page will be loaded once you logged into the system",
				),
				"options" => array(
					"label" => "Default Login Page",
					"empty_option" => "--select--",
					"value_options" => array(
					
					),
				),
				"required" => FALSE,
				"allow_empty" => TRUE,
		));
		
		$this->add(array(
				"type" => "select",
				"name" => "news_feed_options",
				"attributes" => array(
					"id" => "news_feed_options",
					"title" => "Set News Feed Options",
				),
				"options" => array(
					"label" => "News Feed Options",
					"empty_option" => "--select--",
					"value_options" => array(
						"disabled" => "Disabled",
						"enabled" => "Enabled",
					),
				),	
				"required" => FALSE,
				"allow_empty" => TRUE,
		));
		
// 		$this->add(array(
// 				"type" => "select",
// 				"name" => "contacts_toolkit_default_tab",
// 				"attributes" => array(
// 						"id" => "contacts_toolkit_default_tab",
// 						"title" => "Load this tab by default in the Contact Toolkit",
// 				),
// 				"options" => array(
// 						"label" => "Contact Toolkit - Default Tab",
// 						"empty_option" => "--select--",
// 						"value_options" => array(),
// 				),
// 				"required" => FALSE,
// 				"allow_empty" => TRUE,
// 		));
		
// 		$this->add(array(
// 				"type" => "select",
// 				"name" => "user_toolkit_default_tab",
// 				"attributes" => array(
// 						"id" => "user_toolkit_default_tab",
// 						"title" => "Load this tab by default in the User Toolkit",
// 				),
// 				"options" => array(
// 						"label" => "User Toolkit - Default Tab",
// 						"empty_option" => "--select--",
// 						"value_options" => array(
// 								"todo" => "To-Do List",
// 						),
// 				),
// 				"required" => FALSE,
// 				"allow_empty" => TRUE,
// 		));

		$this->add(array(
				"name" => "submit",
				"attributes" => array(
						"value" => "Login",
				),
		
				"options" => array(
						"ignore" => TRUE,
				),
		));
	}//end function
}//end class