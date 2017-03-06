<?php
namespace FrontCore\ViewHelpers;

use Zend\View\Helper\AbstractHelper;

class FrontAdminFormRenderHelper extends AbstractHelper
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
	public function __invoke($form, $view, $arr_options = array("appendJavaScriptUtils" => TRUE))
	{
		//assign view as class variable
		$this->view = $view;

		//set form class
		if ($form->getAttribute("class") == "")
		{
			$form->setAttribute("class", "form-vertical");
		}//end class

		if (strtolower($form->getAttribute("id")) == "form")
		{
			$form->setAttribute("id", "form-id");
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

		$html = "<div class=\"col-md-6\">";

		//open the form tag
		$html .= $view->form()->openTag($form);

		//add help toggle button
		if ($arr_options["disable_help_button"] !== TRUE)
		{
			$html .=	"<span id=\"frm-toggle-help\" class=\"btn btn-warning\" title=\"Toggle Help\" data-toggle=\"tooltip\">";
			$html .= 		"<span class=\"glyphicon glyphicon-question-sign\"></span>";
			$html .= 	"</span>";
		}//end if

		//custom form element error structure
		$view->formElementErrors()->setMessageOpenFormat('<div class="bg-danger help-inline" style="padding: 10px; margin-top: 5px; margin-bottom: 5px;">')
				->setMessageSeparatorString('</div><div>')
				->setMessageCloseString('</div>');

		foreach ($form as $form_element)
		{
			$html .= $this->generateFieldHtml($form, $form_element->getName(), $arr_options);
		}//end foreach

		//add submit button
		if ($form->has("submit"))
		{
			if ($form->get("submit")->getValue() == "")
			{
				//set display ttitle
				$form->get("submit")->setAttribute("value", "Save");
			} else {
				////overwrite button value received from API
				$form->get("submit")->setAttribute("value", "Save");
			}//end if

			$form->get("submit")->setAttribute("class", "btn btn-primary");
			$html .= $view->formSubmit($form->get("submit"));
		}//end if

		//close the form
		$html .= $view->form()->closeTag();
		$html .= "</div>";

		//append js utils
		if (isset($arr_options["appendJavaScriptUtils"]) && $arr_options["appendJavaScriptUtils"] === TRUE)
		{
			$html .= $this->appendJavaScriptUtils($arr_options);
		}//end if

		return $html;
	}//end function

	/**
	 * Generate a form with field Groups
	 * @param objForm $form
	 * @param \Zend\Mvc\View\View $view
	 * @param array $arr_options - Optional
	 * @return mixed
	 */
	private function generateFormWithFieldGroups($form, $view, $arr_options = array())
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

		//open the form tag
		$html .= "<div>";

		if ($arr_options["disable_help_button"] !== TRUE)
		{
			//add help toggle button
			$html .= "<span id=\"frm-toggle-help\" class=\"btn btn-warning\" title=\"Toggle Help\" data-toggle=\"tooltip\"><span class=\"glyphicon glyphicon-question-sign\"></span></span>";
		}//end if

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

			$html .= "<div class=\"panel panel-default m3-panel-subsection\">";
			$html .= 	"<h3 class=\"panel-title panel-heading \"><span class=\"m3-panel-subsection-icon glyphicon glyphicon-resize-full\"></span>&nbsp;$key</h3>";
			$html .= "</div>";

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
						$html .= $this->generateFieldHtml($form, $grouped_field, $arr_options);
					}//end foreach

					//close fieldset
					$html .= "</fieldset>";
				} else {
					$html .= $this->generateFieldHtml($form, $field, $arr_options);
				}//end if
			}//end foreach

			$html .= "</div>";
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
			$form->get("submit")->setAttribute("class", "btn btn-primary");
			$form->get("submit")->setValue("Save");
			$html .= $view->formSubmit($form->get("submit"));
		}//end if

		//close the form
		$html .= $view->form()->closeTag();
		$html .= "</div>";

		//append js utils
		if (!isset($arr_options["appendJavaScriptUtils"]) || (isset($arr_options["appendJavaScriptUtils"]) && $arr_options["appendJavaScriptUtils"] !== FALSE))
		{
			$html .= $this->appendJavaScriptUtils($arr_options);
		}//end if

		return $html;
	}//end function

	private function generateFieldHtml($form, $field, $arr_options = array())
	{
		//make sure field does exist in form, form might have been manipulated since it has been received from the Core
		if (!$form->has($field))
		{
			return;
		}//end if

		//extract element title for help sections
		$title = $form->get($field)->getAttribute("title");

		//set label classes
		$form->get($field)->setLabelAttributes(array("class" => "control-label"));

		//set tooltip
		if ($title != "")
		{
			$form->get($field)->setAttribute("data-toggle", "tooltip");
		}//end if

		//open form element
		if (strtolower($form->get($field)->getAttribute("type") == "hidden"))
		{
			//do nothing
			$html .= "<div style=\"display: none;\">";
		} else {
			$html .= "<div>";
		}//end if

		//set parent element classes
		//check for required elements
		if ($form->get($field)->getAttribute("required") == "required")
		{
			$parent_css_classes = "has-feedback";
		}//end if

		//check for element errors
		if (count($form->get($field)->getMessages()) > 0)
		{
			$parent_css_classes = "has-feedback has-error";
		}//end if

		//check for checkbox and radios
		if ($form->get($field)->getAttribute("type") == "checkbox")
		{
			$parent_css_classes .= "checkbox-inline";
		}//end if

		$html .= "<div class=\"form-group form-element-" . $field . " $parent_css_classes\">";

		switch (strtolower($form->get($field)->getAttribute("type")))
		{
			default:
				if (strtolower($form->get($field)->getAttribute("name")) == "submit")
				{
					//do nothing
					$html .= "</div>";
				} else {
					if ($form->get($field)->getLabel() != "")
					{
						switch (strtolower(str_replace(' ', '', $form->get($field)->getLabel())))
						{
							case 'countryforcellnumber':
							case 'countryforcworknumber':
							case 'countryforfaxnumber':
							case 'countryforhomenumber':
								$arr_options = $form->get($field)->getOptions();
								$arr_options['label'] = 'International Dialing Code';
								$form->get($field)->setOptions($arr_options);
								break;
						}//end switch
						
						$html .= 	$this->view->formLabel($form->get($field));
					}//end if

					$form->get($field)->setAttribute("class", "form-control");
					$html .= 		$this->view->formElement($form->get($field));
					if ($form->get($field)->getAttribute("required") == "required")
					{
						$html .= "<span class=\"glyphicon glyphicon-asterisk form-control-feedback\"></span>";
					}//end if

					//add help text
					if ($title != "")
					{
						if (is_array($arr_options) && (!isset($arr_options["disable_help_button"]) || $arr_options["disable_help_button"] !== TRUE))
						{
							$html .= "<span class=\"help-block\">$title</span>";
						}//end if
					}//end if

					//add errors
					$html .= $this->view->formElementerrors($form->get($field));

					$html .= "</div>";
				}//end if
				break;

				$form->get($field)->setLabelAttributes(array("class" => "control-label", "class" => "selector-width"));

			case "checkbox":
			case "radio":
				$form->get($field)->setAttribute("class", "selector-width");
				$html .= $this->view->formElement($form->get($field));

				if ($form->get($field)->getLabel() != "")
				{
					$html .= $this->view->formLabel($form->get($field));
				} //end if

				if ($form->get($field)->getAttribute("required") == "required")
				{
					$html .= "<span class=\"glyphicon glyphicon-asterisk form-control-feedback\"></span>";
				} //end if

				//add help text
				if ($title != "")
				{
					if (is_array($arr_options) && (!isset($arr_options["disable_help_button"]) || $arr_options["disable_help_button"] !== TRUE))
					{
						$html .= "<span class=\"help-block\">$title</span>";
					}//end if
				}//end if

				//add errors
				$html .= $this->view->formElementerrors($form->get($field));

				$html .= "</div>";
				break;

			case "textarea":
				if ($form->get($field)->getLabel() != "")
				{
					$html .= $this->view->formLabel($form->get($field));
				}//end if


				$form->get($field)->setAttribute("class", "form-control");

				$html .= $this->view->formElement($form->get($field));

				if ($form->get($field)->getAttribute("required") == "required")
				{
					$html .= "<span class=\"glyphicon glyphicon-asterisk form-control-feedback\"></span>";
				}//end if

				//add help text
				if ($title != "")
				{
					if (is_array($arr_options) && (!isset($arr_options["disable_help_button"]) || $arr_options["disable_help_button"] !== TRUE))
					{
						$html .= "<span class=\"help-block\">$title</span>";
					}//end if
				}//end if

				//add errors
				$html .= $this->view->formElementerrors($form->get($field));

				$html .= "</div>";

				$arr_attributes = $form->get($field)->getAttributes();

				if (strpos($arr_attributes["class"], "text-editor") !== FALSE)
				{
					$html .= "<script type=\"text/javascript\">
										jQuery(document).ready(function () {
											jQuery(\"#".  $arr_attributes["id"] . "\").attr(\"required\", false);
											tinyMCE.init({
												selector: \"#" . $arr_attributes["id"] . "\",
											});
										});
									</script>";
				}//end if
				break;

			case "hidden":
				$html .= $this->view->formElement($form->get($field));
				$html .= "</div>";
				break;
		}//end switch

		//close form element
		$html .= "</div>";

		return $html;
	}//end function

	private function appendJavaScriptUtils($arr_options = array())
	{
		$html = "<script type=\"text/javascript\">";
		$html .= 	"jQuery(document).ready( function () {

						//check if form has help blocks attached
						if (jQuery(\"form .help-block\").length)
						{
							//hide help sections
							jQuery(\".help-block\").toggle();

							if (jQuery(\"body .nav-tabs\").length)
							{
								var element_section = jQuery(\".nav-tabs\");
								if (!element_section.find(\"li .mj3_btnhelp\").length)
								{
									element_section.append('<li class=\"mj3_btnhelp clearfix\"><a class=\"btn btn-default js-help-toggle\" href=\"\" data-toggle=\"tooltip\" data-original-title=\"Display Form help tips\">" . ICON_MEDIUM_HELP_HTML . "</a></li>');
								}//end if

								//remove where applicable
								jQuery(\".m3-options-disable-form-help-toggle\").find(\".mj3_btnhelp\").remove();
							}//end if
						}//end if

						// help button
						jQuery(\".js-help-toggle\").click(function (e) {
							e.preventDefault();
							jQuery(\".help-block\").toggle();
							jQuery(this).toggleClass(\"active\");
						});

						//disable submit buttons
						jQuery(\"#form\").submit(function () {
							jQuery(\"#form\").find(\".btn\").attr(\"disabled\", true);
						});
					});";
		$html .= "</script>";
		return $html;
	}//end function
}//end class
