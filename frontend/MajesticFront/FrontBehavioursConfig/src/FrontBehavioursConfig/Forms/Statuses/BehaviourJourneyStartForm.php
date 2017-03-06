<?php
namespace FrontBehavioursConfig\Forms\Statuses;

use FrontCore\Forms\FrontCoreSystemFormBase;

class BehaviourJourneyStartForm extends FrontCoreSystemFormBase
{
	public $additional_javascript;
	
	public function __construct($objForm)
	{
		parent::__construct('behaviour-status-journey-start');
		$this->setAttribute("method", "post");

		//set field elements in correct order
		$arr_fields = array(
				'description',
				'fk_journey_id',
				'generic1',
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
					jQuery(".form-element-fk_journey_id").find("label").html("Start this Journey for the contact when this status is set");
					jQuery(".form-element-generic1").find("label").html("Allow journey to start multiple times");
				
					//finally, intercept the form submit to assign values to field_value and field_operator hidden fields
					jQuery("#behaviour-status-journey-start").submit(function () {				
						//set some more values
						jQuery("#behaviour").val("reg_status");
						jQuery("#beh_action").val("__journey_start");
						jQuery("#setup_complete").val(1);
					});
				});';
		$s .= '</script>';
		$this->additional_javascript = $s;
	}//end function
}//end class