<?php
namespace MajesticExternalForms\Forms;

use Zend\InputFilter\InputFilter;

class MajesticExternalFormValidator extends InputFilter
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
				
				$this->add($element);
			}//end if
		}//end foreach
	}//end function
}//end class