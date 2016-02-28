<?php
namespace FrontBehavioursConfig\Forms\FormFields;

use FrontCore\Forms\FrontCoreSystemFormBase;

class BehaviourTaskEmailForm extends FrontCoreSystemFormBase
{
	public $additional_javascript;
	
	public function __construct($objForm)
	{
		parent::__construct('behaviour-form-field-email-task');
		$this->setAttribute("method", "post");
	
		//set field elements in correct order
		$arr_fields = array(
				"fk_user_id",
				"content",
				"email",
				"email_reminder",
				"field_value",
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
					jQuery(".form-element-fk_user_id").find("label").html("Allocate task to this user");
					jQuery(".form-element-content").find("label").html("Task information").attr("placeholder", "Enter task information");
					jQuery(".form-element-email_reminder").find("label").html("Remind user");
					jQuery(".form-element-field_value").find("label").html("where email field is");
					//enable date picker for field...
				
					//finally, intercept the form submit to assign values to field_value and field_operator hidden fields
					jQuery("#behaviour-form-field-email-task").submit(function () {				
						//set some more values
						jQuery("#behaviour").val("form_fields");
						jQuery("#beh_action").val("__task");
						jQuery("#setup_complete").val(1);
					});
				});';
		$s .= '</script>';
		$this->additional_javascript = $s;
	}//end function
}//end class