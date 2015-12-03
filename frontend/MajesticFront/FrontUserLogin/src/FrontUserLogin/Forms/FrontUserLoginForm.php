<?php
namespace FrontUserLogin\Forms;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Captcha;

class FrontUserLoginForm extends Form
{
	public function __construct()
	{
		parent::__construct("user-login");
		//set form to submit method
		$this->setAttribute("method", "post");
		$this->setAttribute("class", "user-login-form");
	
		$this->add(array(
				"name" => "uname",
				"attributes" => array(
						"type" => "text",
						"id" => "uname",
 						"required" => true,
						"placeholder" => "User Name",
						"style" => "width: 90px",
				),

				'filters' => array(
						array('name' => 'Zend\Filter\StringTrim'),
						array('name' => 'Zend\Filter\StripTags'),
				),
				
				'validators' => array(
						'stringLength' => array(
								'name' => 'StringLength',
								'options' => array('max' => 100, 'min' => 0),
						),
				),

				"options" => array(

				),
		));

		$this->add(array(
				"name" => "pword",
				"type" => "password",
				"attributes" => array(
						"id" => "pword",
 						"required" => true,
						"style" => "width: 90px",
						"placeholder" => "Password",
				),

				'filters' => array(
						array('name' => 'Zend\Filter\StringTrim'),
						array('name' => 'Zend\Filter\StripTags'),
				),
				
				'validators' => array(
						'stringLength' => array(
								'name' => 'StringLength',
								'options' => array('max' => 32, 'min' => 0),
						),
				),

				"options" => array(

				),
		));
		
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
