<?php
namespace MajesticExternalForms\ViewHelpers;

use Zend\View\Helper\AbstractHelper;

class ExternalFormRenderHelper extends AbstractHelper
{
	/**
	 * Container for the view object
	 * @var \Zend\Mvc\View
	 */
	protected $view;

	/**
	 * Generate a generic form
	 * @param objForm $form
	 * @param \Zend\Mvc\View\View $view
	 * @param array $arr_options - Optional
	 * @return string
	 */
	public function __invoke($objForm, $form, $view, $arr_options = NULL)
	{
		//assign view as class variable
		$this->view = $view;

		//populate form fields with values received in the query
		foreach($_GET as $field => $value)
		{
			if ($form->has($field))
			{
				$form->get($field)->setValue($value);
			}//end if
		}//end foreach

		//load form options
		if ($objForm->stylesheet != "")
		{
//@TODO set path for uploaded files
			$view->headLink()->appendStylesheet($view->basePath() . "/css/frontcss/test_form_style.css");
		}//end if

		//check if form top content is defined
		if ($objForm->copy != "" && is_string($objForm->copy))
		{
			$html .= "<div class=\"container container_content_top form-content-top\">" . $objForm->copy . "</div>";
		}//end if

		//load form show forward warning
		if ($objForm->show_fwd_warn == 1)
		{
			//$html .= "This form might be forwarded, click here to correct<br />";
		}//end if

		//analyse options
		if (is_array($arr_options))
		{
			//remove specified elements
			if (array_key_exists("arr_remove_elements", $arr_options))
			{
				foreach ($arr_options["arr_remove_elements"] as $field)
				{
					if ($form->has($field) === TRUE)
					{
						$form->remove($field);
					}//end if
				}//end foreach
			}//end if

			if ($arr_options["generate_field_groups"] === TRUE)
			{
				$form_html = $this->generateFormWithFieldGroups($form, $view, $arr_options);

				//check if form has been generated
				if (!$form_html)
				{
					//generate normal form, grouped form could not be created
				} else {
					return $form_html;
				}//end if
			}//end if
		}//end if

		if ($form->hasAttribute("arr_field_groups") === TRUE)
		{
			$form->removeAttribute("arr_field_groups");
			$form->remove("arr_field_groups");
		}//end if

		if (!$html)
		{
			$html = "";
		}//end if

		//set form attributes
		if (!$form->hasAttribute("class"))
		{
			$form->setAttribute("class", "form");
		}//end class

		if (strtolower($form->getAttribute("id")) == "form")
		{
			$form->setAttribute("id", "form-id");
		}//end if

		//check if form has style attributes
		if ($objForm->margins != "")
		{
			$form->setAttribute("style", "margin: " . $objForm->margins . ";");
		}//end if

		//open the form tag
		$html .= $view->form()->openTag($form);

		//custom form element error structure
		$view->formElementErrors()->setMessageOpenFormat('<div class="bg-danger help-inline" style="padding: 10px; margin-top: 5px; margin-bottom: 5px;">')
				->setMessageSeparatorString('</div><div>')
				->setMessageCloseString('</div>');

		//Added by Lodi
		$html .= "<div class=\"container container_form_fields\">";

		foreach ($form as $form_element)
		{
			$html .= $this->generateFieldHtml($form, $form_element->getName());
		}//end foreach



		//add submit button
		if ($form->has("submit"))
		{
			if ($form->get("submit")->getValue() == "")
			{
				if ($objForm->submit_button != "")
				{
					$form->get("submit")->setValue($objForm->submit_button);
				} else {
					$form->get("submit")->setValue("Submit");
				}//end id
			}//end if
			$html .= "<div class=\"container container_submit\">";

			$html .= 	$view->formSubmit($form->get("submit"));
			$html .= "</div>";
		}//end if

		//Added by Lodi
		$html .= "</div>";

		//close the form
// 		$html .= $view->form()->closeTag();

		//check if terms and conditions are defined
		if ($objForm->terms != "" && is_string($objForm->terms))
		{
			$html .= "<div class=\"form-content-terms\">" . $objForm->terms . "</div>";
		}//end if

		//check if form bottom content is defined
		if ($objForm->copy2 != "" && is_string($objForm->copy2))
		{
			$html .= "<div class=\"form-content-bottom\">" . $objForm->copy2 . "</div>";
		}//end if


		//close the form
		$html .= $view->form()->closeTag(); //Moved here by Lodi

		return $html;
	}//end function

	/**
	 * Generate a form with field Groups
	 * @param objForm $form
	 * @param \Zend\Mvc\View\View $view
	 * @param array $arr_options - Optional
	 * @return mixed
	 */
	private function generateFormWithFieldGroups($form, $view, $arr_options = NULL)
	{
		//extract field groups
		$arr_field_groups = (array) $form->getAttribute("arr_field_groups");
		$form->remove("arr_field_groups");

		if (!is_array($arr_field_groups))
		{
			return FALSE;
		}//end if

		//set unique id
		$java_id = str_replace(".", "", microtime(TRUE));

		//open html string
		$html = "";

		//check if accordion must be enabled
		if ($arr_options["enable_accordion"] === TRUE)
		{
			//add javascript to load accodian plugin
			$html .= "<script type=\"text/javascript\">";
			$html .= 	"jQuery(document).ready(function () {
							jQuery(\".$java_id\").mj_accordion();
						 });";
			$html .= "</script>";
		}//end if

		//set form class attribute
		$form->setAttribute("class", "form");

		//open the form tag
		$html .= $view->form()->openTag($form);

		//add accordion div element
		$html .= "<div class=\"$java_id style-js-accordion\">";

		//custom form element error structure
		$view->formElementErrors()->setMessageOpenFormat('<div class="bg-danger help-inline" style="padding: 10px; margin-top: 5px; margin-bottom: 5px;">')
				->setMessageSeparatorString('</div><div>')
				->setMessageCloseString('</div>');

		foreach ($arr_field_groups as $key => $arr_fields)
		{
			//make sure that this groups does contain fields
			if (count($arr_fields) == 0)
			{
				continue;
			}//end if

			$html .= "<h3>$key</h3>";
			$html .= "<div class=\"accordion-section\">";

			foreach ($arr_fields as $section_internal_group => $field)
			{
				//check for grouped fields within section
				if (is_array($field))
				{
					//create fieldset
					$html .= "<fieldset><legend>$section_internal_group</legend>";
					foreach ($field as $group => $grouped_field)
					{
						$html .= $this->generateFieldHtml($form, $grouped_field);
					}//end foreach

					//close fieldset
					$html .= "</fieldset>";
				} else {
					$html .= $this->generateFieldHtml($form, $field);
				}//end if
			}//end foreach

			//$html .= "</div>"; Commented out by Lodi
		}//end foreach

		//collapse field group array
		foreach ($arr_field_groups as $key => $arr_fields)
		{
			foreach ($arr_fields as $field)
			{
				if (is_array($field))
				{
					foreach ($field as $grouped_field)
					{
						$arr_collapsed_field_groups[] = $grouped_field;
					}//end foreach
				} else {
					$arr_collapsed_field_groups[] = $field;
				}//end if
			}//end foreach
		}//end foreach

		//make sure that all elements have been rendered
		$objElements = $form->getElements();
		foreach ($objElements as $field => $objElement)
		{
			if (!in_array($field, $arr_collapsed_field_groups))
			{
				$html .= $this->generateFieldHtml($form, $field);
				continue;
			}//end if
		}//end foreach

		//close accordion div
		$html .= "</div><br/>";

		if ($form->has("submit"))
		{
			if ($form->get("submit")->getValue() == "")
			{
				$form->get("submit")->setAttribute("class", "submit");
				$form->get("submit")->setValue("Submit");
			}//end if

			$html .= $view->formSubmit($form->get("submit"));
		}//end if

		//close the form
		$html .= $view->form()->closeTag();

		return $html;
	}//end function

	private function generateFieldHtml($form, $field)
	{
		//check if field names should be displayed
		if ($_GET["show_field_names"] == 1)
		{
			$form->get($field)->setLabel($form->get($field)->getLabel() . " - (" . $form->get($field)->getName() . ")");
		}//end if

		$form->get($field)->setLabelAttributes(array("class" => "label"));

		switch (strtolower($form->get($field)->getAttribute("type")))
		{
			default:
				if (strtolower($form->get($field)->getAttribute("name")) == "submit")
				{
					//do nothing
				} else {
					$html .= "<div class=\"form-element form-element-" . $field . " \">";

					$html .= 		"<div class='container container_field_label'>";
					$html .=			 $this->view->formLabel($form->get($field));
					$html .=		"</div>";
					$html .= 		"<div class='container container_field_element'>";
 					$html .= 			$this->view->formElement($form->get($field));
 					//add required information
 					if ($form->get($field)->getAttribute("required") == "required")
 					{
 						$html .= "<span class=\"required-input\">*</span>";
 					}//end if
 					
 					//add element errors where set
 					if (count($form->get($field)->getMessages()) > 0)
 					{
 						$html .= $this->view->formElementErrors($form->get($field));
 					}//end if
 					$html .=		"</div>";
					$html .= 	"</div>";
				}//end if
				break;

// 			case "textarea":
// 				$html .= "<div class=\"form-element form-element-" . $field . " \">";

// 				$html .= 		"<div class='container container_field'>";
// 				$html .=			 $this->view->formLabel($form->get($field));
// 				$html .=		"</div>";
// 				$html .= 			$this->view->formElement($form->get($field));

// 				//add required information
// 				if ($form->get($field)->getAttribute("required") == "required")
// 				{
// 					$html .= "<span class=\"required-input\">*</span>";
// 				}//end if

// 				//add element errors where set
// 				if (count($form->get($field)->getMessages()) > 0)
// 				{
// 					$html .= $this->view->formElementErrors($form->get($field));
// 				}//end if

//  				$arr_attributes = $form->get($field)->getAttributes();

//  				if (strpos($arr_attributes["class"], "text-editor") !== FALSE)
//  				{
// 	 					$html .= "<script type=\"text/javascript\">
// 	 										jQuery(document).ready(function () {
// 	 											jQuery(\"#".  $arr_attributes["id"] . "\").attr(\"required\", false);
// 	 											tinyMCE.init({
// 	 												selector: \"#" . $arr_attributes["id"] . "\"
// 	 											});
// 	 										});
// 	 									</script>";
// 				}//end if

// 				$html .= 	"</div>";
// 				break;

			case "date":
				$html .= "<div class=\"form-element form-element-" . $field . " \">";

				$html .= 		"<div class='container container_field'>";
				$html .=			 $this->view->formLabel($form->get($field));
				$html .=		"</div>";
				$html .= 		"<div class='container container_field'>";
				$html .= 			$this->view->formElement($form->get($field));
				//add required information
				if ($form->get($field)->getAttribute("required") == "required")
				{
					$html .= "<span class=\"required-input\">*</span>";
				}//end if
				
				//add element errors where set
				if (count($form->get($field)->getMessages()) > 0)
				{
					$html .= $this->view->formElementErrors($form->get($field));
				}//end if
				$html .=		"</div>";

				$arr_attributes = $form->get($field)->getAttributes();

				if (strpos($arr_attributes["class"], "date-picker") !== FALSE)
				{
					$html .= "<script type=\"text/javascript\">
									jQuery(document).ready(function () {
										jQuery(\"#" . $field . "\").datepicker({ dateFormat: \"dd-mm-yy\" });
									});
							</script>";
				}//end if

				$html .= 	"</div>";
				break;

			case "hidden":
				$html .= $this->view->formElement($form->get($field));
				break;
		}//end switch

		//Added code to set calss on submit button
		$form->get("submit")->setAttribute("class", "submit");

		return $html;
	}//end function
}//end class