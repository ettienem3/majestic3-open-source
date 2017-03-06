<?php
namespace FrontBehavioursConfig\Forms\Forms;

use FrontCore\Forms\FrontCoreSystemFormBase;

class BehaviourUserChangeForm extends FrontCoreSystemFormBase
{
	public $additional_javascript;
	
	public function __construct($objForm)
	{
		parent::__construct('behaviour-form-change-contact-user');
		$this->setAttribute("method", "post");
	
		//set field elements in correct order
		$arr_fields = array(
				'description',
				'fk_user_id',
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
					jQuery(".form-element-fk_user_id").find("label").html("Set Contact User to");
					jQuery(".form-element-loggedin").find("label").html("Only change the user where a user is logged into the system");
				
					//finally, intercept the form submit to assign values to field_value and field_operator hidden fields
					jQuery("#behaviour-form-change-contact-user").submit(function () {				
						//set some more values
						jQuery("#behaviour").val("form");
						jQuery("#beh_action").val("__user_change");
						jQuery("#setup_complete").val(1);
					});
				});';
		$s .= '</script>';
		$this->additional_javascript = $s;
	}//end function
}//end class