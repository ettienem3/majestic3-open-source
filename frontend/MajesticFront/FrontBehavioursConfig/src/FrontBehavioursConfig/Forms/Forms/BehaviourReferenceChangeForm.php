<?php
namespace FrontBehavioursConfig\Forms\Forms;

use FrontCore\Forms\FrontCoreSystemFormBase;

class BehaviourReferenceChangeForm extends FrontCoreSystemFormBase
{
	public $additional_javascript;
	
	public function __construct($objForm)
	{
		parent::__construct('behaviour-form-change-contact-reference');
		$this->setAttribute("method", "post");
	
		//set field elements in correct order
		$arr_fields = array(
				'description',
				'reference',
				'content',
				'loggedin',
				'active',
				
				//hidden fields
				'event_runtime_trigger',
				'behaviour',
				'beh_action',
				'setup_complete',
		);
		
		foreach ($arr_fields as $field)
		{
			$objElement = $objForm->get($field);
			$objForm->remove($field);
			$this->add(array(
					'name' => $objElement->getAttribute('name'),
					'type' => $objElement->getAttribute('type'),
					'attributes' => $objElement->getAttributes(),
					'options' => $objElement->getOptions(),
			));
		}//end foreach
		
		$this->add(array(
				'type' => 'submit',
				'name' => 'submit',
				'attributes' => array(
						'value' => 'Submit',
				),
				'options' => array(
						'value' => 'Submit',
				),
		));
		
		$this->setJavascript();
	}//end function
	
	private function setJavascript()
	{
		$s = '<script type="text/javascript">';
		$s .=	'jQuery(document).ready(function () {
					//set some labels
					jQuery(".form-element-reference").find("label").html("Set Contact Reference to an existing value");
					jQuery(".form-element-reference").parent().after("<div class=\"form-group\"><strong>OR</strong></div>");
					jQuery(".form-element-content").find("label").html("Set Contact Reference to");
					jQuery("#content").attr("placeholder", "Enter a custom value");
					jQuery(".form-element-loggedin").find("label").html("Only change the Contact Reference where a user is logged into the system");
				
					//finally, intercept the form submit to assign values to field_value and field_operator hidden fields
					jQuery("#behaviour-form-change-contact-reference").submit(function () {				
						//set some more values
						jQuery("#behaviour").val("form");
						jQuery("#beh_action").val("__reference_change");
						jQuery("#setup_complete").val(1);
				
						if (jQuery("#content").val() == "" && jQuery("#reference").val() != "")
						{
							jQuery("#content").val(jQuery("#reference").val());
						}//end if
					});
				});';
		$s .= '</script>';
		$this->additional_javascript = $s;
	}//end function
}//end class