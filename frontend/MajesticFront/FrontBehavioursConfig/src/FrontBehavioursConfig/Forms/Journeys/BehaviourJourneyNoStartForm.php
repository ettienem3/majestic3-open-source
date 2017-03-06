<?php
namespace FrontBehavioursConfig\Forms\Journeys;

use FrontCore\Forms\FrontCoreSystemFormBase;

/**
 * Form is used to restructure the form received from the API for the Journey no start behaviour since it is so invloved.
 * The default behaviour engine cannot render the received form correctly
 * @author ettiene
 *
 */
class BehaviourJourneyNoStartForm extends FrontCoreSystemFormBase
{
	/**
	 * Container for javascript set below for the form
	 * @var string
	 */
	public $additional_javascript;
	
	public function __construct($objForm)
	{
		parent::__construct('behaviour-journey-no-start');
		$this->setAttribute("method", "post");
		
		//set field elements in correct order
		$arr_fields = array(
				'description',
				'fk_fields_std_id',
				'fk_fields_custom_id',
				'fk_reg_status_id',
				'fk_form_id',
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
			'type' => 'hidden',
			'name' => 'field_value',
			'attributes' => array(
					'id' => 'field_value'
			)
		));
		
		$this->add(array(
				'type' => 'hidden',
				'name' => 'field_operator',
				'attributes' => array(
						'id' => 'field_operator'
				)
		));
		
		$this->add(array(
				'type' => 'hidden',
				'name' => 'fk_journey_id',
				'attributes' => array(
						'id' => 'fk_journey_id'
				)
		));
		
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
					//append OR keyword between the elements		
					jQuery(".form-element-fk_fields_std_id").parent().after("<div class=\"form-group\"><strong>OR</strong></div>");
					jQuery(".form-element-fk_fields_custom_id").parent().after("<div class=\"form-group\"><strong>OR</strong></div>");
					jQuery(".form-element-fk_reg_status_id").parent().after("<div class=\"form-group\"><strong>OR</strong></div>");
				
					//append each field option fields
					jQuery(".form-element-fk_fields_std_id").parent().append(jQuery("<div></div>").attr("class", "element_option_fields"));
					jQuery(".form-element-fk_fields_custom_id").parent().append(jQuery("<div></div>").attr("class", "element_option_fields"));
					jQuery(".form-element-fk_reg_status_id").parent().append(jQuery("<div></div>").attr("class", "element_option_fields"));
					jQuery(".form-element-form-element-fk_form_id").parent().append(jQuery("<div></div>").attr("class", "element_option_fields"));
				
					//amend some labels
					jQuery(".form-element-fk_reg_status_id").find("label").html("Contact Status is set to:");
					jQuery(".form-element-fk_form_id").find("label").html("Web form has been completed");
					jQuery(".form-element-fk_fields_custom_id").find("label").html("Custom Field");
				
					//monitor dropdowns for changes
					jQuery("#fk_fields_std_id").change(function () {
						//clear fields
						clearCustomField();
						clearContactStatus();
						clearWebForm();
						
						var field_container = jQuery(".form-element-fk_fields_std_id").parent().find(".element_option_fields");
						field_container.html("");
					
						//countries
						if (jQuery(this).val() == 22)
						{
							//add option fields
							field_container.append(jQuery("<select></select>")
														.attr("id", "std_field_operator_1")
														.append(jQuery("<option></option>").text("--select--"))
														.append(jQuery("<option></option>").val("equals").text("is equal to"))
														.append(jQuery("<option></option>").val("notequals").text("is not equal to"))
													);
							field_container.append("&nbsp;");
				
							//load countries data
							field_container.append(jQuery("<select></select>").attr("id", "std_field_value_1").append(jQuery("<option></option>").text("--select--")));
				
							jQuery.ajax({
								url: "/front/locations/countries/ajax-load-countries",
								type: "GET",
								dataType: "json"
							})
							.done(function (data) {
								jQuery.each(data, function (k, objCountry) {
									if (typeof objCountry.id != "undefined")
									{
										jQuery("#std_field_value_1").append(new Option(objCountry.country, objCountry.id));
									}//end if
								});
							})
							.fail(function () {
								field_container.html("<p>An unknown problem has occured, the required data could not be loaded</p>");
							});
						}//end if
				
						//city
						if (jQuery(this).val() == 23)
						{
							//add option fields
							field_container.append(jQuery("<select></select>")
														.attr("id", "std_field_operator_1")
														.append(jQuery("<option></option>").text("--select--"))
														.append(jQuery("<option></option>").val("equals").text("is equal to"))
														.append(jQuery("<option></option>").val("notequals").text("is not equal to"))
													);
							field_container.append("&nbsp;");
				
							//load cities data
							field_container.append(jQuery("<select></select>").attr("id", "std_field_value_1").append(jQuery("<option></option>").text("--select--")));
				
							jQuery.ajax({
								url: "/front/locations/countries/ajax-load-cities",
								type: "GET",
								dataType: "json"
							})
							.done(function (data) {
								jQuery.each(data, function (k, objCity) {
									if (typeof objCity.id != "undefined")
									{
										jQuery("#std_field_value_1").append(new Option(objCity.city, objCity.id));
									}//end if
								});
							})
							.fail(function () {
								field_container.html("<p>An unknown problem has occured, the required data could not be loaded</p>");
							});					
						}//end if
				
						//province
						if (jQuery(this).val() == 24)
						{
							//add option fields
							field_container.append(jQuery("<select></select>")
														.attr("id", "std_field_operator_1")
														.append(jQuery("<option></option>").text("--select--"))
														.append(jQuery("<option></option>").val("equals").text("is equal to"))
														.append(jQuery("<option></option>").val("notequals").text("is not equal to"))
													);
							field_container.append("&nbsp;");
				
							//load province data
							field_container.append(jQuery("<select></select>").attr("id", "std_field_value_1").append(jQuery("<option></option>").text("--select--")));
				
							jQuery.ajax({
								url: "/front/locations/countries/ajax-load-provinces",
								type: "GET",
								dataType: "json"
							})
							.done(function (data) {
								jQuery.each(data, function (k, objProvince) {
									if (typeof objProvince.id != "undefined")
									{
										jQuery("#std_field_value_1").append(new Option(objProvince.province, objProvince.id));
									}//end if
								});
							})
							.fail(function () {
								field_container.html("<p>An unknown problem has occured, the required data could not be loaded</p>");
							});					
						}//end if
				
						//opt-in
						if (jQuery(this).val() == 33)
						{
							//add option fields
							field_container.append(jQuery("<select></select>")
														.attr("id", "std_field_operator_1")
														.append(jQuery("<option></option>").text("--select--"))
														.append(jQuery("<option></option>").val("checked").text("is checked"))
														.append(jQuery("<option></option>").val("unchecked").text("is not checked"))
													);
						}//end if
				
						//user
						if (jQuery(this).val() == 27)
						{
							//add option fields
							field_container.append(jQuery("<select></select>")
														.attr("id", "std_field_operator_1")
														.append(jQuery("<option></option>").text("--select--"))
														.append(jQuery("<option></option>").val("equals").text("is equal to"))
													);
							field_container.append("&nbsp;");
				
							//load user data
							field_container.append(jQuery("<select></select>").attr("id", "std_field_value_1").append(jQuery("<option></option>").text("--select--")));
				
							jQuery.ajax({
								url: "/front/users/ajax-load-users",
								type: "GET",
								dataType: "json"
							})
							.done(function (data) {
								jQuery.each(data, function (k, objUser) {
									if (typeof objUser.id != "undefined")
									{
										jQuery("#std_field_value_1").append(new Option(objUser.uname, objUser.id));
									}//end if
								});
							})
							.fail(function () {
								field_container.html("<p>An unknown problem has occured, the required data could not be loaded</p>");
							});
						}//end if
				
						//reference
						if (jQuery(this).val() == 29)
						{
							//add option fields
							field_container.append(jQuery("<select></select>")
														.attr("id", "std_field_operator_1")
														.append(jQuery("<option></option>").text("--select--"))
														.append(jQuery("<option></option>").val("equals").text("is equal to"))
														.append(jQuery("<option></option>").val("notequals").text("is not equal to"))
														.append(jQuery("<option></option>").val("like").text("is partly equal to"))
														.append(jQuery("<option></option>").val("notlike").text("is not partly equal to"))
													);
							field_container.append("&nbsp;");
				
							//load reference data
							field_container.append(jQuery("<input></input>").attr("id", "std_field_value_1").attr("type", "text").attr("placeholder", "Enter reference value"));
						}//end if
				
						//source
						if (jQuery(this).val() == 28)
						{
							//add option fields
							field_container.append(jQuery("<select></select>")
														.attr("id", "std_field_operator_1")
														.append(jQuery("<option></option>").text("--select--"))
														.append(jQuery("<option></option>").val("equals").text("is equal to"))
														.append(jQuery("<option></option>").val("notequals").text("is not equal to"))
													);
							field_container.append("&nbsp;");
				
							//load source data
							field_container.append(jQuery("<select></select>").attr("id", "std_field_value_1").append(jQuery("<option></option>").text("--select--")));
				
							jQuery.ajax({
								url: "/front/contacts/ajax-load-source-list",
								type: "GET",
								dataType: "json",
							})
							.done(function (data) {
								jQuery.each(data, function(i, k) {
									if (k != "")
									{
										jQuery("#std_field_value_1").append(new Option(k, k));
									}//end if
								});
							})
							.fail(function () {
								field_container.html("<p>An unknown problem has occurred, please try again later</p>");
							});
						}//end if				
					});
				
					jQuery("#fk_fields_custom_id").change(function () {
						//clear fields
						clearStandardField();
						clearContactStatus();
						clearWebForm();
				
						//load data
						var configured_url = "/front/form/admin/fields/ajax-load-specified-field-values/0001122333?field_type=custom&include_field_values=1";
				
						var field_container = jQuery(".form-element-fk_fields_custom_id").parent().find(".element_option_fields");
						field_container.html("<p>Please wait, loading data...</p>");
								
						jQuery.ajax({
									url: configured_url.replace("0001122333", jQuery(this).val()),
									type: "GET",
									dataType: "json",
								})
								.done(function (data) {
									if (data.fields_types_input_type == "select" || data.fields_types_input_type == "radio")
									{
										//add option fields
										field_container.html("");
										field_container.append(jQuery("<select></select>")
																	.attr("id", "custom_field_operator_1")
																	.append(jQuery("<option></option>").text("--select--"))
																	.append(jQuery("<option></option>").val("equals").text("is equal to"))
																	.append(jQuery("<option></option>").val("notequals").text("is not equal to"))
																);
										field_container.append("&nbsp;");	
				
										//load field options
										field_container.append(jQuery("<select></select>").attr("id", "custom_field_value_1").append(jQuery("<option></option>").text("--select--")));
				
										jQuery.each(data.field_values_data, function(k, v) {
											jQuery("#custom_field_value_1").append(new Option(k, v));
										});
				
										return true;
									}//end if
				
									if (data.fields_types_input_type == "checkbox")
									{
										//add option fields
										field_container.html("");
										field_container.append(jQuery("<select></select>")
																	.attr("id", "custom_field_operator_1")
																	.append(jQuery("<option></option>").text("--select--"))
																	.append(jQuery("<option></option>").val("checked").text("is checked"))
																	.append(jQuery("<option></option>").val("unchecked").text("is not checked"))
																);
										return true;
									}//end if	
				
									field_container.html("<p>A problem occured, field cannot be configured</p>");
								})
								.fail(function () {
									field_container.html("<p>An unknown problem has occurred, please try again later</p>");
								});
					});
				
					jQuery("#fk_reg_status_id").change(function () {
						//clear fields
						clearStandardField();
						clearCustomField();
						clearWebForm();
				
					});
				
					jQuery("#fk_form_id").change(function () {
						//clear fields
						clearStandardField();
						clearCustomField();
						clearContactStatus();
				
					});
				
					//finally, intercept the form submit to assign values to field_value and field_operator hidden fields
					jQuery("#behaviour-journey-no-start").submit(function () {
						if (jQuery("#custom_field_operator_1").length)
						{
							jQuery("#field_operator").val(jQuery("#custom_field_operator_1").val());
						}//end if
				
						if (jQuery("#custom_field_value_1").length)
						{
							jQuery("#field_value").val(jQuery("#custom_field_value_1").val());
						}//end if
				
						if (jQuery("#std_field_operator_1").length)
						{
							jQuery("#field_operator").val(jQuery("#std_field_operator_1").val());
						}//end if
				
						if (jQuery("#std_field_value_1").length)
						{
							jQuery("#field_value").val(jQuery("#std_field_value_1").val());
						}//end if
				
						//set some more values
						jQuery("#behaviour").val("journey");
						jQuery("#beh_action").val("__journey_no_start");
						jQuery("#setup_complete").val(1);
					});
				});
				
				function clearStandardField()
				{
					var element = jQuery("#fk_fields_std_id");
					jQuery("#fk_fields_std_id option:contains(\'--select--\')").prop("selected", true);
				
					//hide option fields
					element.parent().parent().find(".element_option_fields").html("");
				}//end function
				
				function clearCustomField()
				{
					var element = jQuery("#fk_fields_custom_id");
					jQuery("#fk_fields_custom_id option:contains(\'--select--\')").prop("selected", true);
				
					//hide option fields
					element.parent().parent().find(".element_option_fields").html("");
				}//end function
				
				function clearContactStatus()
				{
					var element = jQuery("#fk_reg_status_id");
					jQuery("#fk_reg_status_id option:contains(\'--select--\')").prop("selected", true);
				
					//hide option fields
					element.parent().parent().find(".element_option_fields").html("");
				}//end function
				
				function clearWebForm()
				{
					var element = jQuery("#fk_form_id");
					jQuery("#fk_form_id option:contains(\'--select--\')").prop("selected", true);
				
					//hide option fields
					element.parent().parent().find(".element_option_fields").html("");
				}//end function
				'
		;
		
		$s .= '</script>';
		$this->additional_javascript = $s;
	}//end function
}//end class