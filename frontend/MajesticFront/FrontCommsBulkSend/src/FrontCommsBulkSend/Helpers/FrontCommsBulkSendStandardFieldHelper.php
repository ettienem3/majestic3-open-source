<?php
namespace FrontCommsBulkSend\Helpers;

use FrontFormAdmin\Entities\FrontFormAdminFieldEntity;

class FrontCommsBulkSendStandardFieldHelper
{
	public function generateStandardFieldCriteriaHTML(FrontFormAdminFieldEntity $objField, $value = FALSE, $objParam = FALSE)
	{
		switch($objField->get("field"))
		{
			case "city_id":
			case "province_id":
			case "country_id":
			case "user_id":
				$html = "<select name=\"std_field_operator_" . $objField->get("field") . "\" id=\"std_field_operator_" . $objField->get("field") . "\" required=\"required\">
							<option value=\"\">--select--</option>";
				
				if (is_object($objParam) && $objParam->field_operator == "equals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .=			"<option value=\"equals\" $selected>is equal to</option>";
				
				if (is_object($objParam) && $objParam->field_operator == "notequals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .=			"<option value=\"notequals\" $selected>is not equal to</option>";
				$html .= "</select>&nbsp;
						<select name=\"std_field_" . $objField->get("field") . "\" id=\"std_field_" . $objField->get("field") . "\">
							<option value=\"\">--select--</option>
							#field_values
						</select>";
				
				foreach ($objField->get("field_values_data") as $id => $field_value)
				{
					if ($id == $value)
					{
						$selected = "selected=\"selected\"";
					} else {
						$selected = "";
					}//end if
					
					$html_values .= "<option value=\"$id\" $selected>$field_value</option>";
				}//end foreach
				
				$html = str_replace("#field_values", $html_values, $html);
				break;
				
			case "reference":
				$html = "<select name=\"std_field_operator_" . $objField->get("field") . "\" id=\"std_field_operator_" . $objField->get("field") . "\" required=\"required\">
							<option value=\"\">--select--</option>";
				
				if (is_object($objParam) && $objParam->field_operator == "equals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .= 	"<option value=\"equals\" $selected>is equal to</option>";
				
				if (is_object($objParam) && $objParam->field_operator == "notequals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .= 	"<option value=\"notequals\">is not equal to</option>";
				
				if (is_object($objParam) && $objParam->field_operator == "like")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .=	"<option value=\"like\" $selected>is partly equal to</option>";
				
				if (is_object($objParam) && $objParam->field_operator == "notlike")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .= 	"<option value=\"notlike\" $selected>is not partly equal to</option>";
				$html .= "</select>&nbsp;
						<select name=\"std_field_" . $objField->get("field") . "\" id=\"std_field_" . $objField->get("field") . "\">
							<option value=\"\">--select--</option>
							#field_values
						</select>";
				
				foreach ($objField->get("field_values_data") as $id => $field_value)
				{
					if ($id == $value)
					{
						$selected = "selected=\"selected\"";
					} else {
						$selected = "";
					}//end if
					
					$html_values .= "<option value=\"$id\" $selected>$field_value</option>";
				}//end foreach
				
				$html = str_replace("#field_values", $html_values, $html);
				break;
				
			case "suburb":
				$html = "<select name=\"std_field_operator_" . $objField->get("field") . "\" id=\"std_field_operator_" . $objField->get("field") . "\" required=\"required\">
							<option value=\"\">--select--</option>";
				
				if (is_object($objParam) && $objField->field_operator == "equals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .= 	"<option value=\"equals\" $selected>is equal to</option>";
				
				if (is_object($objParam) && $objField->field_operator == "notequals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .= 	"<option value=\"notequals\" $selected>is not equal to</option>";
				
				if (is_object($objParam) && $objField->field_operator == "like")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .=	"<option value=\"like\" $selected>is partly equal to</option>";
				
				if (is_object($objParam) && $objField->field_operator == "notlike")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .=	"<option value=\"notlike\">is not partly equal to</option>";
						
				$html .= "</select>&nbsp;
						<select name=\"std_field_" . $objField->get("field") . "\" id=\"std_field_" . $objField->get("field") . "\">
							<option value=\"\">--select--</option>
							#field_values
						</select>";
				
				foreach ($objField->get("field_values_data") as $id => $field_value)
				{
					if ($field_value == $value)
					{
						$selected = "selected=\"selected\"";
					} else {
						$selected = "";
					}//end if
					
					$html_values .= "<option value=\"$id\" $selected>$field_value</option>";
				}//end foreach
				
				$html = str_replace("#field_values", $html_values, $html);
				break;
				
			case "source":
				$html = "<select name=\"std_field_operator_" . $objField->get("field") . "\" id=\"std_field_operator_" . $objField->get("field") . "\" required=\"required\">
							<option value=\"\">--select--</option>";
				
				if (is_object($objParam) && $objParam->field_operator == "equals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .= 	"<option value=\"equals\" $selected>is equal to</option>";
				
				if (is_object($objParam) && $objParam->field_operator == "notequals")
				{
					$selected = "selected=\"selected\"";
				} else {
					$selected = "";
				}//end if
				$html .=	"<option value=\"notequals\" $selected>is not equal to</option>";
				
				$html .= "</select>&nbsp;
						<select name=\"std_field_" . $objField->get("field") . "\" id=\"std_field_" . $objField->get("field") . "\">
							<option value=\"\">--select--</option>
							#field_values
						</select>";
				
				foreach ($objField->get("field_values_data") as $id => $field_value)
				{
					if ($field_value == $value)
					{
						$selected = "selected=\"selected\"";
					} else {
						$selected = "";
					}//end if
					
					$html_values .= "<option value=\"$id\" $selected>$field_value</option>";
				}//end foreach
				
				$html = str_replace("#field_values", $html_values, $html);
				break;
				
			default:
				return FALSE;
				break;
		}//end switch
		
		$element_id = str_replace(".", "", microtime(TRUE));
		$html_section = "<script type=\"text/javascript\">
							jQuery(document).ready(function () {
								//remove field section from criteria section
								jQuery(\".std_field_section_$element_id .remove_section\").click(function () {
									//confirm removal
									if (confirm(\"Are you sure you want to remove this field?\"))
									{
										jQuery(\".std_field_section_$element_id\").remove();
									}//end if
									
									return false;
								});
							});
						</script>";
		
		$html_section .= "<div class=\"std_field_section std_field_section_$element_id\">";
		$html_section .= 	"<span class=\"std_field_section field_criteria\">" . $objField->get("description") . " : </span>&nbsp;";
		$html_section .= 	$html;
		$html_section .= 	"<button class=\"remove_section\">-</button>";
		$html_section .= "</div>";
		
		return $html_section;
	}//end function
}//end class