jQuery(document).ready(function () {
	//monitor the form type dropdown
	jQuery("#fk_form_type_id").change(function () {
		var option = jQuery("#" + jQuery(this).attr("id") + " option:selected").text();
		switch (option.toLowerCase().replace(/[^a-zA-Z 0-9]+/g,''))
		{
			case "web": //web forms
				toggleWebFormFields("show");
				break;
				
			default:
				//hide web form fields
				toggleWebFormFields("hide");
				break;
		}//end switch
	});
	
	//check if form type has been set already
	switch (jQuery("#fk_form_type_id").val())
	{
		case "1":		//web forms
			toggleWebFormFields("show");
			break;
			
		default:
			toggleWebFormFields("hide");
			break;
	}//end switch
	
	//set form forward data format default option to json
	var form_forward_data_format = jQuery("input:radio[name=form_forward_data_format]");
	if (form_forward_data_format.is(":checked") === false)
	{
		form_forward_data_format.filter('[value=2]').prop('checked', true);
	}//end if
});

function toggleWebFormFields(action)
{
return;
	if (action == "show")
	{
		jQuery(".web-form-settings").parent().show("fast");
		//set required fields
		jQuery.each(jQuery.find(".web-form-settings"), function (i, obj) {
			if (jQuery(obj).hasClass("required-field"))
			{
				jQuery(obj).attr("required", "required");
			}//end if
		});
	} else {
		jQuery(".web-form-settings").parent().hide("fast");
		//set required fields
		jQuery.each(jQuery.find(".web-form-settings"), function (i, obj) {
			if (jQuery(obj).hasClass("required-field"))
			{
				jQuery(obj).attr("required", false);
			}//end if
		});
	}//end if
}//end function

function toggleContactProfileFields(action)
{
	
}//end function
