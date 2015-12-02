<?php
/*
 Copyright (c) 2015 Majestic 3 http://majestic3.com

 Permission is hereby granted, free of charge, to any person obtaining
 a copy of this software and associated documentation files (the
 "Software"), to deal in the Software without restriction, including
 without limitation the rights to use, copy, modify, merge, publish,
 distribute, sublicense, and/or sell copies of the Software, and to
 permit persons to whom the Software is furnished to do so, subject to
 the following conditions:

 The above copyright notice and this permission notice shall be included
 in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class cls_form_element_predefined
{
	private $objField;
	
	public function __construct($objField)
	{
		$this->objField = $objField;
	}//end function
	
	/**
	 * Transform predefined element to fit raw field data structure
	 * @return StdClass
	 */
	public function generateField()
	{
		$arr_field = array(
			"name" 			=> $this->objField->attributes->name,
			"label" 		=> "",
			"maxlength" 	=> "",
			"style" 		=> "",
			"input_type" 	=> $this->objField->attributes->type,
			"required" 		=> "",
			"readonly" 		=> "",
			"hidden" 		=> "",
		);
		
		if (isset($this->objField->options->label))
		{
			$arr_field["label"] = $this->objField->options->label;	
		}//end if
		
		if (isset($this->objField->attributes->maxlength) && is_numeric($this->objField->attributes->maxlength))
		{
			$arr_field["maxlength"] = $this->objField->attributes->maxlength;
		}//end if
		
		if (isset($this->objField->attributes->required) && $this->objField->attributes->required != "")
		{
			$arr_field["required"] = 1;
		}//end if
		
		if (isset($this->objField->attributes->readonly) && is_numeric($this->objField->attributes->readonly))
		{
			$arr_field["readonly"] = $this->objField->attributes->readonly;
		}//end if
		
		return (object) $arr_field;
	}//end function
	
	/**
	 * Transform predefined element to fit raw field data structure
	 * @return StdClass
	 */
	public function generateRawFieldData()
	{
		$arr_field = array(
			"css_class" => "",
			"field_values" => "",
		);
		
		if (isset($this->objField->attributes->class))
		{
			$arr_field["css_class"] = $this->objField->attributes->class;
		}//end if
		
		if (isset($this->objField->options->value_options))
		{
			$arr_field["field_values"] = $this->objField->options->value_options;	
		}//end if
		
		return (object) $arr_field;
	}//end function
}//end function