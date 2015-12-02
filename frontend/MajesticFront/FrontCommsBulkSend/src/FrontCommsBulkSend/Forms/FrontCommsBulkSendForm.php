<?php
namespace FrontCommsBulkSend\Forms;

use Core\Forms\SystemForms\CoreSystemFormBase;

class FrontCommsBulkSendForm extends CoreSystemFormBase
{
	public function __construct()
	{
		parent::__construct("frontcommsbulksend");
		//set form to submit method
		$this->setAttribute("method", "post");
	
		$this->add(array(
				"name" => "title",
				"attributes" => array(
						"type" => "text",
						"id" => "title",
						"required" => "required",
				),
				"options" => array(
						"label" => "Title",
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
	
	public function statePurpose()
	{
		$arr["description"] = "This is a test form to test dynamic requesting of forms";
	
		return $arr;
	}//end function
}//end class
