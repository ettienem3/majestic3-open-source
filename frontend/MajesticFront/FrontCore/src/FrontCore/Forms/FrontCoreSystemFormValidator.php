<?php
namespace FrontCore\Forms;

use Zend\InputFilter\InputFilter;

class FrontCoreSystemFormValidator extends InputFilter
{
	public function __construct($elements)
	{
		foreach ($elements as $element)
		{
			if (is_array($element))
			{
				if (strtolower($element["type"] == "submit") || strtolower($element["name"] == "submit"))
				{
					//ignore element
					continue;
				}//end if

				if (isset($element["type"]))
				{
					unset($element["type"]);
				}//end if

				try {
					$this->add($element);
				} catch (\Exception $e) {
//@TODO display proper error. This throws exceptions where validators are not set properly or not contained within its own array
					var_dump($e); exit;
				}
			}//end if
		}//end foreach
	}//end function
}//end class