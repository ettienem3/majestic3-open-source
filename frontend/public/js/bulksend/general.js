jQuery(document).ready(function () {
	//enable datepickers
	jQuery(".datepicker").datepicker({ dateFormat: "dd-mm-yy" });
	
	//hide some fields
	//display form operator
	jQuery("#form_operator").hide();
	
	//monitor web forms dropdown
	jQuery("#form_id").change(function () {
		if (jQuery(this).val() == "*load_data*")
		{
			var element = jQuery(this);
			
			//clear the dropdown
			element.empty();
			//add waiting text
			element.append(jQuery("<option></option>").text("Loading..."));
			
			jQuery.ajax({
				url: ajax_web_forms_url,
				type: "GET",
			})
			.done(function (data) {
				objData = jQuery.parseJSON(data);
				
				if (objData.error == 1)
				{
					alert(objData.response);
					return false;
				}//end if
				
				//populate the dropdown
				//clear the dropdown
				element.empty();
				
				//add select text
				element.append(jQuery("<option></option>").text("--select--"));
				
				jQuery.each(objData.data, function (i, obj) {
					element.append(jQuery("<option></option>").val(obj.value).text(obj.text));
				});
				
				//display form operator
				jQuery("#form_operator").show("fast");
			})//end done
			.fail(function () {
				alert("An unknown error occured trying to populate the dropdown");
			}); //end fail
			
		}//end if
	});
	
	//monitor contact status dropdown
	jQuery("#reg_status_id").change(function () {
		if (jQuery(this).val() == "*load_data*")
		{
			var element = jQuery(this);
			
			//clear the dropdown
			element.empty();
			//add waiting text
			element.append(jQuery("<option></option>").text("Loading..."));
			
			jQuery.ajax({
				url: ajax_reg_status_list,
				type: "GET",
			})
			.done(function (data) {
				objData = jQuery.parseJSON(data);
				
				if (objData.error == 1)
				{
					alert(objData.response);
					return false;
				}//end if
				
				//populate the dropdown
				//clear the dropdown
				element.empty();
				
				//add select text
				element.append(jQuery("<option></option>").text("--select--"));
				
				jQuery.each(objData.data, function (i, obj) {
					element.append(jQuery("<option></option>").val(obj.value).text(obj.text));
				});
			})//end done
			.fail(function () {
				alert("An unknown error occured trying to populate the dropdown");
			}); //end fail
			
		}//end if
	});
	
	//monitor sales funnels dropdown
	jQuery("#sales_funnel_id").change(function () {
		if (jQuery(this).val() == "*load_data*")
		{
			var element = jQuery(this);
			
			//clear the dropdown
			element.empty();
			//add waiting text
			element.append(jQuery("<option></option>").text("Loading..."));
			
			jQuery.ajax({
				url: ajax_sales_funnels_url,
				type: "GET",
			})
			.done(function (data) {
				objData = jQuery.parseJSON(data);
				
				if (objData.error == 1)
				{
					alert(objData.response);
					return false;
				}//end if
				
				//populate the dropdown
				//clear the dropdown
				element.empty();
				
				//add select text
				element.append(jQuery("<option></option>").text("--select--"));
				
				jQuery.each(objData.data, function (i, obj) {
					element.append(jQuery("<option></option>").val(obj.value).text(obj.text));
				});
			})//end done
			.fail(function () {
				alert("An unknown error occured trying to populate the dropdown");
			}); //end fail
			
		}//end if
	});
	
	//monitor standard fields dropdown
	jQuery("#standard_fields_list").change(function () {
		var element = jQuery(this);
		
		if (jQuery(this).val() == "*load_data*")
		{		
			//clear the dropdown
			element.empty();
			//add waiting text
			element.append(jQuery("<option></option>").text("Loading..."));
			
			jQuery.ajax({
				url: ajax_standard_fields_list_url,
				type: "GET",
			})
			.done(function (data) {
				objData = jQuery.parseJSON(data);
				
				if (objData.error == 1)
				{
					alert(objData.response);
					return false;
				}//end if
				
				//populate the dropdown
				//clear the dropdown
				element.empty();
				
				//add select text
				element.append(jQuery("<option></option>").text("--select--"));
				
				jQuery.each(objData.data, function (i, obj) {
					element.append(jQuery("<option></option>").val(obj.value).text(obj.text));
				});
			})//end done
			.fail(function () {
				alert("An unknown error occured trying to populate the dropdown");
			}); //end fail
		}//end if
		
		if (jQuery.isNumeric(jQuery(this).val()))
		{
			jQuery.ajax({
				url: ajax_standard_field_data_url + "?sf_id=" + jQuery(this).val(),
				type: "GET"
			})
			.done(function(data) {
				var objData = jQuery.parseJSON(data);
				
				if (objData.error == 1)
				{
					alert(objData.response);
					return false;
				}//end if
				
				if (objData.html == "")
				{
					return false;
				}//end if
				
				//append html to approriate section
				jQuery("#standard_fields_criteria").append(objData.html);
				
				//remove value from dropdown
				jQuery("#standard_fields_list option[value='" + element.val() + "']").remove();
			}) //end done
			.fail(function() {
				alert("An unknown error occured loading " + jQuery("#standard_fields_list option[value='" + jQuery(this).val() + "']").text() + " Data");
				return false;
			}); //end fail
		}//end if		
	});
	
	//monitor custom fields dropdown
	jQuery("#custom_fields_list").change(function () {
		var element = jQuery(this);
		
		if (jQuery(this).val() == "*load_data*")
		{			
			//clear the dropdown
			element.empty();
			//add waiting text
			element.append(jQuery("<option></option>").text("Loading..."));
			
			jQuery.ajax({
				url: ajax_custom_fields_list_url,
				type: "GET",
			})
			.done(function (data) {
				objData = jQuery.parseJSON(data);
				
				if (objData.error == 1)
				{
					alert(objData.response);
					return false;
				}//end if
				
				//populate the dropdown
				//clear the dropdown
				element.empty();
				
				//add select text
				element.append(jQuery("<option></option>").text("--select--"));
				
				jQuery.each(objData.data, function (i, obj) {
					element.append(jQuery("<option></option>").val(obj.value).text(obj.text));
				});
			})//end done
			.fail(function () {
				alert("An unknown error occured trying to populate the dropdown");
			}); //end fail
		}//end if
		
		if (jQuery.isNumeric(jQuery(this).val()))
		{
			jQuery.ajax({
				url: ajax_custom_field_data_url + "?cf_id=" + jQuery(this).val(),
				type: "GET"
			})
			.done(function(data) {
				var objData = jQuery.parseJSON(data);
				
				if (objData.error == 1)
				{
					alert(objData.response);
					return false;
				}//end if
				
				if (objData.html == "")
				{
					return false;
				}//end if
				
				//append html to approriate section
				jQuery("#custom_fields_criteria").append(objData.html);
				
				//remove value from dropdown
				jQuery("#custom_fields_list option[value='" + element.val() + "']").remove();
			}) //end done
			.fail(function() {
				alert("An unknown error occured loading " + jQuery("#custom_fields_list option[value='" + jQuery(this).val() + "']").text() + " Data");
				return false;
			}); //end fail
		}//end if
	});
	
	//monitor web forms dropdown
	jQuery("#load_fields_from_form").change(function () {
		var element = jQuery(this);
		
		if (jQuery(this).val() == "*load_data*")
		{			
			//clear the dropdown
			element.empty();
			//add waiting text
			element.append(jQuery("<option></option>").text("Loading..."));
			
			jQuery.ajax({
				url: ajax_web_forms_url,
				type: "GET",
			})
			.done(function (data) {
				objData = jQuery.parseJSON(data);
				
				if (objData.error == 1)
				{
					alert(objData.response);
					return false;
				}//end if
				
				//populate the dropdown
				//clear the dropdown
				element.empty();
				
				//add select text
				element.append(jQuery("<option></option>").text("--select--"));
				
				jQuery.each(objData.data, function (i, obj) {
					element.append(jQuery("<option></option>").val(obj.value).text(obj.text));
				});

			})//end done
			.fail(function () {
				alert("An unknown error occured trying to populate the dropdown");
			}); //end fail
		}//end if
		
		//monitor form selection to see if fields should be loaded
		if (jQuery.isNumeric(jQuery(this).val()))
		{
			jQuery.ajax({
				url: ajax_web_form_fields_url + "?f_id=" + jQuery(this).val(),
				type: "GET"
			})
			.done(function(data) {
				var objData = jQuery.parseJSON(data);
				
				if (objData.error == 1)
				{
					alert(objData.response);
					return false;
				}//end if
				
				//reset the standard fields dropdown
				jQuery("#standard_fields_list").empty();
				jQuery("#standard_fields_list").append(jQuery("<option></option>").val("").text("--select--"));
				jQuery("#standard_fields_list").append(jQuery("<option></option>").val("*load_data*").text("Reload Fields"));
				
				jQuery.each(objData.data.standard_fields, function (i, v) {
					jQuery("#standard_fields_list").append(jQuery("<option></option>").val(i).text(v));
				});
				
				//reset the custom fields dropdown
				jQuery("#custom_fields_list").empty();
				jQuery("#custom_fields_list").append(jQuery("<option></option>").val("").text("--select--"));
				jQuery("#custom_fields_list").append(jQuery("<option></option>").val("*load_data*").text("Reload Fields"));
				
				jQuery.each(objData.data.custom_fields, function (i, v) {
					jQuery("#custom_fields_list").append(jQuery("<option></option>").val(i).text(v));
				});
			}) //end done
			.fail(function() {
				alert("An unknown error occured loading " + jQuery("#custom_fields_list option[value='" + jQuery(this).val() + "']").text() + " Data");
				return false;
			}); //end fail
		}//end if
	});
});