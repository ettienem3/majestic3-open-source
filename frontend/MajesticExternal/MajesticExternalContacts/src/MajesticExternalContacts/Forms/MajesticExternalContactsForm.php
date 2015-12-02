<?php
namespace MajesticExternalContacts\Forms;

use Zend\Form\Form;
use Zend\Form\Element;

class MajesticExternalContactsForm extends Form
{
	public function __construct()
	{
		parent::__construct(majesticexternalcontacts);
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
	}//end function
}//end class
