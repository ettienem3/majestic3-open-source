<?php
namespace FrontCommsBulkSend\Helpers;

use FrontFormAdmin\Entities\FrontFormAdminFieldEntity;

class FrontCommsBulkSendCustomFieldHelper
{
	public function generateCustomFieldCriteriaHTML(FrontFormAdminFieldEntity $objField, $value = FALSE, $objParam = FALSE)
	{
		//determine field type to setup data returned for the field
		switch ($objField->get("fields_types_input_type"))
		{
			case "text":
				//check for particular field types
				switch (strtolower($objField->get("fields_types_field_type")))
				{						
					case "text": //normal texrt fields
						$html = "<select name=\"custom_field_operator_" . $objField->get("field") . "\" id=\"custom_field_operator_" . $objField->get("field") . "\" required=\"required\">
									<option value=\"\">--select--</option>";
						
						if (is_object($objParam) && $objParam->field_operator == "equals")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"equals\" $selected>is equal to</option>";
						
						if (is_object($objParam) && $objParam->field_operator == "notequals")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"notequals\" $selected>is not equal to</option>";
						
						if ($value != "" && $value == $objParam->field_value)
						{
							$field_value = "value=\"" . trim($objParam->field_value) . "\"";
						} else {
							$field_value = "";
						}//end if
						$html .= "</select>&nbsp;
								<input type=\"text\" name=\"custom_field_" . trim($objField->get("field")) . "\" id=\"custom_field_" . trim($objField->get("field")) . "\" required=\"required\" $field_value>";
						break;
						
					case "money": //monetary field						
					case "text (numeric)": //numeric field
						$html = "<select name=\"custom_field_operator_" . trim($objField->get("field")) . "\" id=\"custom_field_operator_" . trim($objField->get("field")) . "\" required=\"required\">
									<option value=\"\">--select--</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "equals")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .= 	"<option value=\"equals\" $selected>is equal to</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "notequals")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"notequals\" $selected>is not equal to</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "greaterthanequals")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"greaterthanequals\" $selected>is greater than or equal to</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "lessthanequals")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"option value=\"lessthanequals\" $selected>is less than or equal to</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "greaterthan")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"greaterthan\" $selected>is greater than</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "lessthan")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .= 	"<option value=\"lessthan\" $selected>is less than</option>";
						
						if ($value != "" && trim($value) == ($objParam->field_value))
						{
							$field_value = "value=\"" . trim($objParam->field_value) . "\"";
						} else {
							$field_value = "";
						}//end if
						$html .= "</select>&nbsp;
								<input type=\"text\" name=\"custom_field_" . trim($objField->get("field")) . "\" id=\"custom_field_" . trim($objField->get("field")) . "\" required=\"required\" $field_value>";
						break;
						
					case "date":
						$html = "<select name=\"custom_field_operator_" . trim($objField->get("field")) . "\" id=\"custom_field_operator_" . trim($objField->get("field")) . "\" required=\"required\">
									<option value=\"\">--select--</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "equals")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"equals\" $selected>is equal to</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "notequals")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"notequals\" $selected>is not equal to</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "greaterthanequals")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"greaterthanequals\" $selected>is greater than or equal to</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "lessthanequals")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"lessthanequals\" $selected>is less than or equal to</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "greaterthan")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"greaterthan\" $selected>is greater than</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "lessthan")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"lessthan\">is less than</option>";
						
						if ($value != "" && trim($value) == trim($objParam->field_value))
						{
							$field_value = "value=\"" . trim($objParam->field_value) . "\"";
						} else {
							$field_value = "";
						}//end if
						$html .= "</select>&nbsp;
								<!--activate datepicker for field-->
								<script type=\"text/javascript\">
									jQuery(document).ready(function () {
										jQuery(\".datepicker\").datepicker({ dateFormat: \"dd-mm-yy\" });
									});
								</script>
								<input type=\"text\" name=\"custom_field_" . trim($objField->get("field")) . "\" id=\"custom_field_" . trim($objField->get("field")) . "\" required=\"required\" readonly=\"readonly\" class=\"datepicker\" style=\"width: 100px;\" $field_value>";
						break;
						
					case "date range":
						$html = "<select name=\"custom_field_operator_" . trim($objField->get("field")) . "\" id=\"custom_field_operator_" . trim($objField->get("field")) . "\" required=\"required\">
									<option value=\"\">--select--</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "between")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .= 	"<option value=\"between\" $selected>is between</option>";
						
						if (is_object($objParam) && trim($objParam->field_operator) == "notbetween")
						{
							$selected = "selected=\"selected\"";
						} else {
							$selected = "";
						}//end if
						$html .=	"<option value=\"notbetween\" $selected>is not between</option>";
						
						//extract date values
						if ($value != "" && trim($value) == trim($objParam->field_value))
						{
							$arr_data = explode(" AND ", $objParam->field_value);
							$date_start_value = str_replace("'", "", str_replace(" ", "", $arr_data[0]));
							$date_end_value = str_replace("'", "", str_replace(" ", "", $arr_data[1]));
						} else {
							$date_start_value = "";
							$date_end_value = "";
						}//end if
						
						$html .= "</select>&nbsp;
								<!--activate datepicker for field-->
								<script type=\"text/javascript\">
									jQuery(document).ready(function () {
										jQuery(\".datepicker\").datepicker({ dateFormat: \"dd-mm-yy\" });
									});
								</script>
								<input type=\"text\" name=\"custom_field_date_start_" . $objField->get("field") . "\" id=\"custom_field_date_start_" . $objField->get("field") . "\" required=\"required\" readonly=\"readonly\" class=\"datepicker\" style=\"width: 100px;\" $date_start_value>
								&nbsp;-&nbsp;
								<input type=\"text\" name=\"custom_field_date_end_" . $objField->get("field") . "\"   id=\"custom_field_date_end_" . $objField->get("field") . "\" required=\"required\" readonly=\"readonly\" class=\"datepicker\" style=\"width: 100px;\" $date_end_value>";
						break;
				}//end switch
				
				break;
				
			case "textarea":
				return FALSE;
				break;
				
			case "select":
			case "radio":
				$html = "<select name=\"custom_field_operator_" . trim($objField->get("field")) . "\" id=\"custom_field_operator_" . trim($objField->get("field")) . "\" required=\"required\">
										<option value=\"\">--select--</option>";
				
				if (is_object($objParam) && trim($objParam->field_operator) == "equals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .=				"<option value=\"equals\" $selected>is equal to</option>";
				
				if (is_object($objParam) && trim($objParam->field_operator) == "notequals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .=				"<option value=\"notequals\" $selected>is not equal to</option>";
				
				$html .=			"</select>&nbsp;
								 <select name=\"custom_field_" . trim($objField->get("field")) . "\" id=\"custom_field_" . ($objField->get("field")) . "\">
									<option value=\"\">--select--</option>
									#field_values
								 </select>";
		
				foreach ($objField->get("field_values_data") as $id => $value)
				{
					if (is_object($objParam) && trim($id) == trim($objParam->field_value))
					{
						$selected = "selected=\"selected\"";
					} else {
						$selected = "";
					}//end if
					
					$html_values .= "<option value=\"" . trim($id) . "\" $selected>" . trim($value) . "</option>";
				}//end foreach
				
				$html = str_replace("#field_values", $html_values, $html);
				break;
				
			case "checkbox":
				$html = "<select name=\"custom_field_operator_" . trim($objField->get("field")) . "\" id=\"custom_field_operator_" . trim($objField->get("field")) . "\" required=\"required\">
										<option value=\"\">--select--</option>";
				
				if (is_object($objParam) && trim($objParam->field_operator) == "equals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .=				"<option value=\"equals\" $selected>is equal to</option>";
				
				if (is_object($objParam) && trim($objParam->field_operator) == "notequals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .=				"<option value=\"notequals\" $selected>is not equal to</option>";
				
				$html .=			"</select>&nbsp;
								 <select name=\"custom_field_" . trim($objField->get("field")) . "\" id=\"custom_field_" . ($objField->get("field")) . "\">
									<option value=\"\">--select--</option>
									<option value=\"checked\">checked</option>
									<option value=\"unchecked\">not checked</option>								 		
								 </select>";					
				break;
		}//end switch
		
		$element_id = str_replace(".", "", microtime(TRUE));
		$html_section = "<script type=\"text/javascript\">
							jQuery(document).ready(function () {
								//remove field section from criteria section
								jQuery(\".custom_field_section_$element_id .remove_section\").click(function () {
									//confirm removal
									if (confirm(\"Are you sure you want to remove this field?\"))
									{
										jQuery(\".custom_field_section_$element_id\").remove();
									}//end if
										
									return false;
								});
							});
						</script>";
		
		$html_section .= "<div class=\"custom_field_section custom_field_section_$element_id\">";
		$html_section .= 	"<span class=\"custom_field_section field_criteria\">" . trim($objField->get("description")) . " : </span>&nbsp;";
		$html_section .= 	$html;
		$html_section .= 	"<button class=\"remove_section\">-</button>";
		$html_section .= "</div>";
		
		return $html_section;		
	}//end function
}//end class