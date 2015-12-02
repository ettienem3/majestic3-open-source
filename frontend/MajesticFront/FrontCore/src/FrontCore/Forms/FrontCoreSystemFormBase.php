<?php
namespace FrontCore\Forms;

use Zend\Form\Form;
use FrontCore\Forms\FrontCoreSystemFormValidator;

class FrontCoreSystemFormBase extends Form
{
	protected $name;
	protected $rawElements;

	public function __construct($name = NULL)
	{
		parent::__construct($name);
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

	/**
	 * Override Form remove function to perform additional operations
	 * @param string $element
	 */
	public function remove($element)
	{
		unset($this->rawElements[$element]);
		return parent::remove($element);
	}//end function

	/**
	 * Override Zend Form add function in order to create list of validators added to elements
	 * @param array $element
	 * @param array $flags
	 * @see \Zend\Form\Form::add()
	 */
	public function add($element, array $flags = array())
	{
		$objForm = parent::add($element, $flags);

		//where an element is not required, set the NotEmpty Validator to NULL to pass form validation
		if (isset($element["attributes"]["required"]))
		{
			if ($element["attributes"]["required"] === FALSE || strtolower($element["attributes"]["required"]) != "required")
			{
				$element["validators"]["notEmpty"] = array(
						"name" => "NotEmpty",
						"options" => array(
								"null",
						),
				);
				$element["required"] = false;
				$element["allow_empty"] = true;
			}//end if
		} else {
			//required is not set
			$element["validators"]["notEmpty"] = array(
					"name" => "NotEmpty",
					"options" => array(
							"null",
					),
			);
			$element["required"] = false;
			$element["allowEmpty"] = true;
		}//end if

		$this->rawElements[$element["name"]] = $element;
		return $objForm;
	}//end function

	private function addValidator()
	{
		$this->setInputFilter(new FrontCoreSystemFormValidator($this->rawElements));
	}//end function
}//end class