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

class cls_form_element
{
	/**
	 * Container for field data received from the API
	 * @var stdClass
	 */
	private $objRawFieldData;
	
	/**
	 * Container for parsed field data
	 * @var stdClass
	 */
	private $objField;
	
	/**
	 * Container for element errors
	 * @var stdClass
	 */
	private $objElementErrors;
	
	private $element_value;
	private $element_label;
	private $element_html;
	
	public function __construct($objField)
	{
		//check for predefined fields
		if ($objField instanceof cls_form_element_predefined)
		{
			$this->objField = $objField->generateField();
			$this->objRawFieldData = $objField->generateRawFieldData();	
			return;
		}//end if
		
		$this->objRawFieldData = $objField;

		//setup element
		$this->objField = new stdClass();
		if (is_numeric($this->objRawFieldData->fields_std_id))
		{
			/**
			 * Standard field
			 */
			$this->objField->name 			= $this->objRawFieldData->fields_std_field;
			$this->objField->label 			= $this->objRawFieldData->fields_std_description;
			$this->objField->maxlength 		= $this->objRawFieldData->fields_std_maxlength;
			$this->objField->style 			= $this->objRawFieldData->fields_std_css_style;
			$this->objField->input_type 	= $this->objRawFieldData->fields_std_input_type;

		} else {
			/**
			 * Custom field
			 */
			$this->objField->name 			= $this->objRawFieldData->fields_custom_field;
			$this->objField->label 			= $this->objRawFieldData->fields_custom_description;
			$this->objField->maxlength 		= $this->objRawFieldData->fields_custom_maxlength;
			$this->objField->style 			= $this->objRawFieldData->fields_custom_css_style;
			$this->objField->input_type 	= $this->objRawFieldData->fields_custom_input_type;
		}//end if
		
		$this->objField->required 		= $this->objRawFieldData->mandatory;
		$this->objField->readonly		= $this->objRawFieldData->readonly;
		$this->objField->hidden			= $this->objRawFieldData->hidden;
	}//end function
	
	/**
	 * Set value to be used when html is generated
	 * @param mixed $value
	 */
	public function setElementValue($value)
	{
		$this->element_value = $value;	
	}//end function
	
	/**
	 * Set element specific errors
	 * @param stdClass $objErrors
	 */
	public function setErrors($objErrors)
	{
		$this->objElementErrors = $objErrors;
	}//end function
	
	/**
	 * Override magic function to pull element data from $objField
	 * @param string $key
	 * @return string
	 */
	public function __get($key)
	{
		return $this->objField->$key;
	}//end function
	
	/**
	 * Returns the generate html to be used on a form
	 */
	public function generateOutput()
	{
		$this->generateElementHTML();
		return $this->element_label . $this->element_html;
	}//end function
	
	/**
	 * Process field data and create html element from it
	 */
	private function generateElementHTML()
	{
		$html = null;
		if ($this->objField->hidden == 1)
		{
			$this->objField->input_type = "hidden";
		}//end if
		
		switch ($this->objField->input_type)
		{
			case "text":
			default: //catch any undefined types
				$html = "<input type=\"text\" name=\"" . $this->objField->name . "\" id=\"" . $this->objField->name . "\" #required #style #maxlength #class #value/>";
				break;
				
			case "hidden":
				$html = "<input type=\"hidden\" name=\"" . $this->objField->name . "\" id=\"" . $this->objField->name . "\" #value/>";
				break;
				
			case "radio":
				foreach ($arr_field_values as $value)
				{
					$value = str_replace("\r", "", $value);
					$html .= "<input type=\"radio\" name=\"" . $this->objField->name . "\" value=\"$value\"/ #class>&nbsp$value";
				}//end foreach
				break;
				
			case "checkbox":
				$html = "<input type=\"checkbox\" name=\"" . $this->objField->name . "\" id=\"" . $this->objField->name . "\" value=\"1\" #required #style #class/>";
				break;
				
			case "select":
				$html = "<select name=\"" . $this->objField->name . "\" id=\"" . $this->objField->name . "\" #required #style #class>";
				$html .= 	"<option value=''>--select--</option>";
				
				if (is_array($this->objRawFieldData->field_values) || is_object($this->objRawFieldData->field_values))
				{
					foreach ($this->objRawFieldData->field_values as $value => $text)
					{
						$html .= "<option value=\"$value\">$text</option>";
					}//end foreach
				} else {
					$arr_field_values = explode("\n", $this->objRawFieldData->field_values);
					if (is_array($arr_field_values))
					{
						foreach ($arr_field_values as $value)
						{
							$value = str_replace("\r", "", $value);
							$html .= "<option value=\"$value\">$value</option>";
						}//end foreach
					}//end if
				}//end if
				
				$html .= "</select>";
				break;
				
			case "textarea":
				$html = "<textarea name=\"" . $this->objField->name . "\" id=\"" . $this->objField->name . "\" #required #style #maxlength #class>#value<textarea/>";
				break;
		}//end switch
		
		//set label
		$label = "<label for=\"" . $this->objField->name . "\">" . $this->objField->label . "</label>";
		
		//deal with element metadata
		if ($this->objRawFieldData->css_class != "")
		{
			$html = str_replace("#class", "class=\"" . $this->objRawFieldData->css_class . "\"", $html);
		}//end if
		
		if ($this->objField->style != "")
		{
			$html = str_replace("#style", "style=\"" . $this->objField->style . "\"", $html);
		}//end if
		
		if ($this->objField->required == 1)
		{
			$html = str_replace("#required", "required=\"required\"", $html);
		}//end if
		
		if ($this->objField->readonly == 1)
		{
			$html = str_replace("#readonly", "readonly=\"readonly\"", $html);
		}//end if
		
		if ($this->objField->maxlength > 0)
		{
			$html = str_replace("#maxlength", "maxlength=\"" . $this->objField->maxlength . "\"", $html);
		}//end if
		
		//remove any unprocessed tags
		$html = str_replace(array("#class", "#style", "#required", "#readonly", "#maxlength"), "", $html);
		
		//finally, does the element have a predefined value?
		if ($this->element_value != FALSE)
		{
			$html = str_replace("#value", "value=\"" . $this->element_value . "\"", $html);
		} else {
			$html = str_replace("#value", "", $html);
		}//end if
		
		$this->element_label = $label;
		$this->element_html = $html;
		
		//add error messages where set
		if ($this->objElementErrors !== FALSE && is_object($this->objElementErrors))
		{
			foreach ($this->objElementErrors as $error)
			{
				$this->element_html .= "<div class=\"form-element-error\">$error</div>";
			}//end foreach
		}//end if
	}//end function
}//end class