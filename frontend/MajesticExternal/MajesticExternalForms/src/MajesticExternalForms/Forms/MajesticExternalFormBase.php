<?php
namespace MajesticExternalForms\Forms;

use Zend\Form\Form;
use Zend\Form\Element;


class MajesticExternalFormBase extends Form
{
	protected $name;
	protected $arr_raw_elements;
	
	public function __construct($name = NULL)
	{
		parent::__construct($name);
		//set form to submit method
		$this->setAttribute("method", "post");
	}//end function
	
	public function add($element, array $flags = array())
	{
		$objForm = parent::add($element, $flags);
		
		//where an element is not required, set the NotEmpty Validator to NULL to pass form validation
		if (array_key_exists($element["attributes"]["required"], $element))
		{
			if ($element["attributes"]["required"] === FALSE || strtolower($element["attributes"]["required"]) != "required")
			{
				$element["validators"]["notEmpty"] = array(
						"name" => "NotEmpty",
						"options" => array(
								"null",
						),
				);
			}//end if
		} else {
			//required is not set
			$element["validators"]["notEmpty"] = array(
					"name" => "NotEmpty",
					"options" => array(
							"null",
					),
			);
		}//end if
		
		//store element for validators and filters
		$this->arr_raw_elements[$element["name"]] = $element;
		return $objForm;
	}//end function
	
	/**
	 * Override default isValid function in order to append input filters set on elements within form
	 * @see \Zend\Form\Form::isValid()
	 */
	public function isValid()
	{
		$this->addValidator();
		return parent::isValid();
	}//end function
	
	private function addValidator()
	{
		$this->setInputFilter(new MajesticExternalFormValidator($this->arr_raw_elements));
	}//end function
	
	/**
	 * Override Form remove function to perform additional operations
	 * @param string $element
	 */
	public function remove($element)
	{
		unset($this->rawElements[$element]);
		return parent::remove($element);
	}//end function
}//end class
