jQuery(document).ready(function () {
	//monitor the comm type field
	jQuery("#comm_via_id").change(function () {
		var option = jQuery("#" + jQuery(this).attr("id") + " option:selected").text();
		switch (option.toLowerCase().replace(/[^a-zA-Z 0-9]+/g,''))
		{
			case "email":
				//disable sms settings fields
				toggleSMSSettingsFields("hide");
				
				//enable email setting fields
				toggleEmailSettingFields("show");
				break;
				
			case "sms":
				//disbale email setting fields
				toggleEmailSettingFields("hide");
				
				//enable sms setting fields
				toggleSMSSettingsFields("show");
				break;
		}//end switch
	});	
	
	//check if comm type value has already been set
	switch(jQuery("#comm_via_id").val())
	{
		case "1": //email
			//disable sms settings fields
			toggleSMSSettingsFields("hide");
			
			//enable email setting fields
			toggleEmailSettingFields("show");
			break;
			
		case "2": //sms
			//disbale email setting fields
			toggleEmailSettingFields("hide");
			
			//enable sms setting fields
			toggleSMSSettingsFields("show");
			break;
	}//end switch
	
	//set some default values for some form elements
	var send_after_hours_radios = jQuery("input:radio[name=send_after_hours]");
	if (send_after_hours_radios.is(":checked") === false)
	{
		send_after_hours_radios.filter('[value=2]').prop('checked', true);
	}//end if
	
	var send_priority_radios = jQuery("input:radio[name=priority]");
	if (send_priority_radios.is(":checked") === false)
	{
		send_priority_radios.filter('[value=3]').prop('checked', true);
	}//end if
	
	if (!jQuery("#date_expiry").datepicker)
	{
		//lazyload css files
		LazyLoad.css([
		    '//cdn-aws.majestic3.com/bootstrap/bootstrap-datepicker/dist/css/bootstrap-datepicker3.standalone.min.css',
		]);

		//lazyload additional files
		LazyLoad.js([
		    '//cdn-aws.majestic3.com/bootstrap/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',
		], function () {
				jQuery("#date_expiry").datepicker({
					format: "yyyy-mm-dd",
					clearBtn: true,
					todayHighlight: true,
					autoclose: true,
					todayBtn: true
				}).attr("readonly", true);
			});
	} else {
		jQuery("#date_expiry").datepicker({
			format: "yyyy-mm-dd",
			clearBtn: true,
			todayHighlight: true,
			autoclose: true,
			todayBtn: true
		}).attr("readonly", true);
	}//end if
});

function toggleEmailSettingFields(action)
{
	if (action == "show")
	{
		jQuery(".email-settings").parent().show("fast");
		//set required fields
		jQuery.each(jQuery.find(".email-settings"), function (i, obj) {
			if (jQuery(obj).hasClass("required-field"))
			{
				jQuery(obj).attr("required", "required");
			}//end if
		});
	} else {
		jQuery(".email-settings").parent().hide("fast");
		//remove required fields
		jQuery.each(jQuery.find(".email-settings"), function (i, obj) {
			if (jQuery(obj).hasClass("required-field"))
			{
				jQuery(obj).attr("required", false);
			}//end if			
		});
	}//end if
}//end function

function toggleSMSSettingsFields(action)
{
	if (action == "show")
	{
		jQuery(".sms-settings").parent().show("fast");
	} else {
		jQuery(".sms-settings").parent().hide("fast");
	}//end if
}//end function
