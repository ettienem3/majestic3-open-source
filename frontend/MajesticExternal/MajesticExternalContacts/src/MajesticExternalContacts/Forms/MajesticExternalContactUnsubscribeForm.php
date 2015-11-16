<?php
namespace MajesticExternalContacts\Forms;

use Zend\Form\Form;
use Zend\Form\Element;

class MajesticExternalContactUnsubscribeForm extends Form
{
	public function __construct()
	{
		parent::__construct("contact-unsub-form");
		//set form to submit method
		$this->setAttribute("method", "post");
		
	}//end function
}//end class
