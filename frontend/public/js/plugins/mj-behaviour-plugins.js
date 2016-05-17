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
		//save instance of input element for later use
		var input_element = this;
		//declare global vars
		var delay_days = 0;
		var delay_hours = 0;
		var delay_mins = 0;
		
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
							
		//lazyload the slider files
 		LazyLoad.css([
			cdn_url + "/bootstrap/bootstrap-slider/bootstrap-slider.css"
 		], function () {

 		});

		//lazyload additional files
		LazyLoad.js([
			cdn_url + "/bootstrap/bootstrap-slider/bootstrap-slider.js"
		], function () {
			if (jQuery(input_element).val() != 0 && jQuery(input_element).val() != "")
			{
				var val = jQuery(input_element).val();
				
				if (val >= 86400)
				{
					delay_days = parseInt(val / 86400);
					
					//subtract days from total
					val = val - parseInt(delay_days * 86400);					
				}//end if
				
				if (val >= 3600)
				{
					delay_hours = parseInt(val / 3600);
					
					//subtract hours from total
					val = val - parseInt(delay_hours * 3600);
				}//end if
				
				if (val > 0 && val < 3600)
				{
					delay_mins = parseInt(val / 60);
				}//end if
			}//end if
			
			//prepend html to form
			jQuery(input_element).after(jQuery("<div></div>").append(
					jQuery("<p/>").append(
						jQuery("<input/>", {
								'id':'slider_days',
								'type':'text',
								'data-slider-min':'0',
								'data-slider-max':'180',
								'data-slider-step':'1',
								'data-slider-value':delay_days
							})
						).append(
								jQuery("<span/>", {
									'class':'slider_days_value',
									'style': 'padding-left:10px;',
								}).html('&nbsp;' + delay_days + ' Days')
						)
					)
					.append(
						jQuery("<p/>").append(
								jQuery("<input/>", {
										'id':'slider_hours',
										'type':'text',
										'data-slider-min':'0',
										'data-slider-max':'23',
										'data-slider-step':'1',
										'data-slider-value':delay_hours
									})
								).append(
										jQuery("<span/>", {
											'class':'slider_hours_value',
											'style': 'padding-left:10px;',
										}).html('&nbsp;' + delay_hours + ' Hours')
								)							
					)
					.append(
						jQuery("<p/>").append(
								jQuery("<input/>", {
										'id':'slider_minutes',
										'type':'text',
										'data-slider-min':'0',
										'data-slider-max':'59',
										'data-slider-step':'1',
										'data-slider-value':delay_mins
									})
								).append(
										jQuery("<span/>", {
											'class':'slider_minutes_value',
											'style': 'padding-left:10px;',
										}).html('&nbsp;' + delay_mins + ' Minutes')
								)							
					)
			);
			
			//enable days slider
			jQuery("#slider_days").bootstrapSlider({tooltip:'hide'});
			jQuery("#slider_days").on("slide", function(slideEvt) {
				jQuery(".slider_days_value").html('&nbsp;' + slideEvt.value + '&nbsp;Days');
				delay_days = slideEvt.value;
				calcDelay();
			});
			
			//enable hours slider
			jQuery("#slider_hours").bootstrapSlider({tooltip:'hide'});
			jQuery("#slider_hours").on("slide", function(slideEvt) {
				jQuery(".slider_hours_value").html('&nbsp;' + slideEvt.value + '&nbsp;Hours');
				delay_hours = slideEvt.value;
				calcDelay();
			});
			
			//enable minutes slider
			jQuery("#slider_minutes").bootstrapSlider({tooltip:'hide'});
			jQuery("#slider_minutes").on("slide", function(slideEvt) {
				jQuery(".slider_minutes_value").html('&nbsp;' + slideEvt.value + '&nbsp;Minutes');
				delay_mins = slideEvt.value;
				calcDelay();
			});			
				
			//hide field
			jQuery(input_element).hide();		
		});
		
		function calcDelay()
		{
			var total = 0;
			total = (delay_days * 86400) + (delay_hours * 3600) + (delay_mins * 60);
			jQuery(input_element).val(total);
		}//end function
	}; //end function
}(jQuery));