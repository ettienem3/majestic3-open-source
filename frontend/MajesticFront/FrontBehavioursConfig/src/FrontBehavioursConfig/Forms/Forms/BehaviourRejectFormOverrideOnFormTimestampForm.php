<?php
namespace FrontBehavioursConfig\Forms\Forms;

use FrontCore\Forms\FrontCoreSystemFormBase;

class BehaviourRejectFormOverrideOnFormTimestampForm extends FrontCoreSystemFormBase
{
	public $additional_javascript;
	
	public function __construct($objForm)
	{
		parent::__construct('behaviour-form-reject-override');
		$this->setAttribute("method", "post");
	
		//set field elements in correct order
		$arr_fields = array(
				'description',
				'fk_form_id2',
				'content',
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
					jQuery(".form-element-fk_form_id2").find("label").html("Set form which should have been completed");
					jQuery(".form-element-content").find("label").html("Set the number of days the form must have been completed");
				
					//finally, intercept the form submit to assign values to field_value and field_operator hidden fields
					jQuery("#behaviour-form-reject-override").submit(function () {				
						//set some more values
						jQuery("#behaviour").val("form");
						jQuery("#beh_action").val("__reject_form_override_on_form_timestamp");
						jQuery("#setup_complete").val(1);
					});
				});';
		$s .= '</script>';
		$this->additional_javascript = $s;
	}//end function
}//end class