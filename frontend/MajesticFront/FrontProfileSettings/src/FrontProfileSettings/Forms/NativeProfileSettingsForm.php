<?php
namespace FrontProfileSettings\Forms;

use FrontCore\Forms\FrontCoreSystemFormBase;
class NativeProfileSettingsForm extends FrontCoreSystemFormBase
{
	public function __construct()
	{
		parent::__construct("native_profile_settings_form");
		//set form submit post method
		$this->setAttribute("method", "post");
		
		$this->add(array(
				"type" => "text",
				"name" => "profile_title",
				"attributes" => array(
					"id" => "profile_title",
					"required" => "required",
					"title" => "Set Profile Title. This is displayed in the Browser title bar",
					"placeholder" => "Profile Title",
				),
				"options" => array(
					"label" => "Profile Title",
				),	
				"filters" => array(
						array("name" => "StripTags"),
						array("name" => "StringTrim"),
				),
				"validators" => array(
						array(
								"name" => "NotEmpty",
						),
						array(
								"name" => "Zend\I18n\Validator\Alpha",
								"options" => array(
										"allowWhiteSpace" => TRUE,
								),
						),
						array(
								"name" => "StringLength",
								"options" => array(
										"max" => 50,
								),
						),
				),
		));
		
		$this->add(array(
				"type" => "text",
				"name" => "profile_icon",
				"attributes" => array(
					"id" => "profile_icon",
					"title" => "Set icon url. This appears in the Menu bar",
					"placeholder" => "Set icon url"
				),
				"options" => array(
					"label" => "Profile Icon (Favicon)",
				),		
		));
		
		$this->add(array(
				"type" => "file",
				"name" => "profile_logo",
				"attributes" => array(
						"id" => "profile_logo",
				),
				"options" => array(
						"label" => "Profile Logo",
				),
		));
		
		$this->add(array(
				"type" => "checkbox",
				"name" => "enable_footer",
				"attributes" => array(
					"id" => "enable_footer",
				),
				"options" => array(
					"label" => "Enable Footer",
					"checked_value" => "1",
					"unchecked_value" => "0",
					"use_hidden_value" => TRUE,
				),		
		));
		
		$this->add(array(
				"type" => "textarea",
				"name" => "footer_content",
				"attributes" => array(
					"id" => "footer_content",
				),
				"options" => array(
					"label" => "Footer Content",
				),	
		));
		
		$this->add(array(
				"type" => "text",
				"name" => "menu_main_relationship",
				"attributes" => array(
					"id" => "menu_main_relationship",
					"title" => "Set Relationship Label",
					"placeholder" => "Relationship",
				),
				"options" => array(
					"label" => "Main Menu - Relationship Label",
				),	
				"filters" => array(
						array("name" => "StripTags"),
						array("name" => "StringTrim"),
				),
		));
		
		$this->add(array(
				"type" => "text",
				"name" => "menu_main_data",
				"attributes" => array(
						"id" => "menu_main_data",
						"title" => "Set Data Label",
						"placeholder" => "Data",
				),
				"options" => array(
						"label" => "Main Menu - Data Label",
				),
				"filters" => array(
						array("name" => "StripTags"),
						array("name" => "StringTrim"),
				),
		));
		
		$this->add(array(
				"type" => "text",
				"name" => "menu_main_sale",
				"attributes" => array(
						"id" => "menu_main_sale",
						"title" => "Set Sale Label",
						"placeholder" => "Sale",
				),
				"options" => array(
						"label" => "Main Menu - Sale Label",
				),
				"filters" => array(
						array("name" => "StripTags"),
						array("name" => "StringTrim"),
				),
		));
		
		$this->add(array(
				"type" => "text",
				"name" => "menu_main_administration",
				"attributes" => array(
						"id" => "menu_main_administration",
						"title" => "Set Administration Label",
						"placeholder" => "Administration",
				),
				"options" => array(
						"label" => "Main Menu - Administration Label",
				),
				"filters" => array(
						array("name" => "StripTags"),
						array("name" => "StringTrim"),
				),
		));
		
		$this->add(array(
				"name" => "submit",
				"type" => "submit",
				"attributes" => array(
						"value" => "Submit",
				),
				"options" => array(
						"ignore" => TRUE,
				),
		));
	}//end function
}//end class