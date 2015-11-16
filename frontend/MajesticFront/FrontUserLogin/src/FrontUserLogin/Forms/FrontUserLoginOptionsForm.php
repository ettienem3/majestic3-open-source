<?php
namespace FrontUserLogin\Forms;

use Zend\Form\Form;
use Zend\Form\Element;

class FrontUserLoginOptionsForm extends Form
{
	public function __construct()
	{
		parent::__construct("user-login");
		
		//set form to submit method
		$this->setAttribute("method", "post");
		
		$this->add(array(
			"type" => "select",
			"name" => "theme",
			"attributes" => array(
				"id" => "theme",
			),		
			"options" => array(
				"label" => "Theme",
				"empty_option" => "--select--",
				"value_options" => array(
						"blitzer" => "Blitzer",
						"cupertino" => "Cupertino",
						"dark-hive" => "Dark Hive",
						"eggplant" => "Eggplant",
						"excite-bike" => "Excite Bike",
						"hot-sneaks" => "Hot Sneaks",
						"humanity" => "Humanity",
						"le-frog" => "Le Frog",
						"overcast" => "Overcast",
						"pepper-grinder" => "Pepper Grinder",
						"redmond" => "Redmond",
						"smoothness" => "Smoothness",
						"start" => "Start",
						"sunny" => "Sunny",
						"ui-darkness" => "Darkness",
						"ui-lightness" => "Lightness",
						"vader" => "Vader",
				),
			),
		));
		
// 		$this->add(array(
// 				"name" => "submit",
// 				"type" => "submit",
// 				"attributes" => array(
// 						"value" => "Submit",
// 				),
		
// 				"options" => array(
// 						"ignore" => TRUE,
// 				),
// 		));
	}//end function
}//end class