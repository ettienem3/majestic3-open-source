/**
 * Load/Manage field values for the fk_fields_all_id2 field
 */ 
(function (jQuery) {
	jQuery.fn.mj_behaviour_fk_fields_all_id2 = function (params) {
		jQuery(this).change(function () {
			//load fields data
			var objFieldData = jQuery.parseJSON(JSON_field_info);
			var id = jQuery(this).val();

			//hide some fields
			jQuery(".form-element-field_operator").hide();
			jQuery(".form-element-field_value_label").hide();
			
			//clear fields
			jQuery("#field_operator").empty();
			jQuery("#field_value_label").empty();
			jQuery("#field_value_label").append(new Option("--select--", ""));
			jQuery("#field_value").val("");
			
			if (objFieldData[id].input_type == "select" || objFieldData[id].input_type == "radio")
			{
				//set url to call
				var url = params.url + "?field_type=" + objFieldData[id].field_type + "&fields_all_id=" + objFieldData[id].fields_all_id + "&include_field_values=1";

				if (objFieldData[id].field_type == "standard")
				{
					//replace #id with the correct value
					url = url.replace("0001122321", objFieldData[id].field_std_id);
				} else {
					//replace #id with the correct value
					url = url.replace("0001122321", objFieldData[id].field_custom_id);
				}//end if
				
				//load specified field possible values
				jQuery.ajax({
				    type: 'GET',
				    url: url,
				    dataType: 'json',
				    async: false,
				    success: function (data) {					    
						//convert data received to array
						jQuery.each(data.field_values_data, function (id, field_value) {
							jQuery("#field_value_label").append(new Option(field_value, id));
						});
				    },
				    fail: function () {
						alert("Data could not be loaded");
						return false;
				    }
				});
				
				//set field operator dropdown value
				jQuery("#field_operator").append(new Option("is equal to", "equals"));
				jQuery("#field_operator").append(new Option("is not equal to", "noteqauls"));
				
				//display fields
				jQuery(".form-element-field_operator").show("fast");
				jQuery(".form-element-field_value_label").show("fast");
			}//end if

			if (objFieldData[id].input_type == "checkbox")
			{
				//set field operator dropdown value
				jQuery("#field_operator").append(new Option("is checked", "checked"));
				jQuery("#field_operator").append(new Option("is not checked", "notchecked"));

				//display field
				jQuery(".form-element-field_operator").show("fast");
			}//end if
		});

		//set field value from the field value label field
		jQuery("#field_value_label").change(function () {
			jQuery("#field_value").val(jQuery(this).val());
		});
	}; //end function
}(jQuery));

/**
 * Replace delay field with sliders
 * @param jQuery
 * @returns
 */
(function (jQuery) {
	jQuery.fn.mj_behaviour_delay_days = function (params) {
		//declare global vars
		var delay_days = 0;
		var delay_hours = 0;
		var delay_mins = 0;
		//save instance of input element for later use
		var input_element = this;
		
		//set default options
		params = params || {
								delay_days_min : 0,
								delay_days_max : 90,
								delay_days_value : 0,
								delay_hours_min : 0,
								delay_hours_max : 24,
								delay_hours_value : 0,
								delay_mins_min : 0,
								delay_mins_max : 60,
								delay_mins_value : 0,
							};
							
		//prepend html to form
		jQuery(input_element).after(' <style>#slider_time_elements span { height:120px; float:left; margin:15px } </style><div id="slider_time_elements"><span class="slider_days" title="Days"></span><span class="slider_hours" title="Hours"></span><span class="slider_mins" title="Minutes"></span></div><br class="floatFix"/><div><span class="slider_days_value">Days</span>&nbsp;<span class="slider_hours_value">Hours</span>&nbsp;<span class="slider_mins_value">Minutes</span></div>');

		//hide field
		jQuery(input_element).hide();
		
		//initialise sliders
		jQuery(".slider_days").slider({
			orientation: "vertical",
			animate: true,
			value: 0,
			min: 0,
			max: 180,
			stop: function (event, ui) {
				delay_days = ui.value;
				calcDelay();
			}, //end function 
			slide: function (event, ui) {
				jQuery(".slider_days_value").html(ui.value + " Days");
			}
		});

		jQuery(".slider_hours").slider({
			orientation: "vertical",
			animate: true,
			value: 0,
			min: 0,
			max: 24,
			stop: function (event, ui) {
				delay_hours = ui.value;
				calcDelay();
			}, //end function
			slide: function (event, ui) {
				jQuery(".slider_hours_value").html(ui.value + " Hours");
			} 
		});

		jQuery(".slider_mins").slider({
			orientation: "vertical",
			animate: true,
			value: 0,
			min: 0,
			max: 60,
			stop: function (event, ui) {
				delay_mins = ui.value;
				calcDelay();
			}, //end function
			slide: function (event, ui) {
				jQuery(".slider_mins_value").html(ui.value + " Minutes");
			} 
		});
		
		function calcDelay()
		{
			var total = 0;
			total = (delay_days * 86400) + (delay_hours * 3600) + (delay_mins * 60);
			jQuery(input_element).val(total);
		}//end function
	}; //end function
}(jQuery));