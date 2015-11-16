jQuery(document).ready(function () {
	if (jQuery("#maxlength").val() == "")
	{
		jQuery("#maxlength").val("50");
	}//end if
	
	jQuery("#fk_field_type_id").change(function () {
		manageFieldOptions(jQuery(this).val());
	});
	
	//initialize with loaded field option
	manageFieldOptions(jQuery("#fk_field_type_id").val());
	
	//hide master pool field for now
	jQuery(".form-element-fk_table_custom_id").hide();
	jQuery(".form-element-custom_table_create_field_options").hide();
});

function manageFieldOptions(value)
{
	//manipulate form fields based on field type selection
	switch(value)
	{
		case "1": //text field
			toggleMaxlengthField({visible: true, value: 50});
			toggleFieldValuesField({visible: false});
			break;
			
		case "2": //textbox field
			toggleMaxlengthField({visible: true, value: 0});
			toggleFieldValuesField({visible: false});
			break;
			
		case "3": //select field
			toggleMaxlengthField({visible: true, value: 50});
			toggleFieldValuesField({visible: true});
			break;
			
		case "4": //checkbox
		case "14": //radio
			toggleMaxlengthField({visible: false, value: 50});
			toggleFieldValuesField({visible: true});
			break;
			
		case "5": //money
		case "6": //text - numeric
		case "7": //date
		case "8": //date range
		case "9": //city list
		case "10": //province list
			toggleMaxlengthField({visible: false, value: 50});
			toggleFieldValuesField({visible: false});
			break;
			
		default:
			toggleMaxlengthField({visible: true, value: 50});
			toggleFieldValuesField({visible: true});
			break;
	}//end switch
}//end function

function toggleMaxlengthField(params)
{
	var element = jQuery("#maxlength");
	if (params.visible === true)
	{
		element.parent().show();
	} else {
		element.parent().hide();
	}//end if
	
	if (params.value != 0)
	{
		element.val(params.value);
	} else {
		element.val("");
	}//end if
}//end function

function toggleFieldValuesField(params)
{
	var element = jQuery("#field_values");
	if (params.visible == true)
	{
		element.parent().show();
	} else {
		element.parent().hide();
	}//end if
}//end function