/******* Location Fields *********/
jQuery(document).ready(function () {
	
	//monitor changes in country field
	jQuery("#country_id").change(function () {
		//trigger provinces update
		setProvincesFromCountry(jQuery(this).val());
		
		//trigger cities update
		setCitiesFromCountry(jQuery(this).val());
	});
	
	//monitor changes in province field
	jQuery("#province_id").change(function () {
		//trigger cities update
		setCitiesFromProvince(jQuery(this).val());
		
		//set country field
		setCountryFromProvince(jQuery(this).val());
	});
	
	//monitor changes in the city field
	jQuery("#city_id").change(function () {
		//set province field update
		setProvinceFromCity(jQuery(this).val());
	});	
	
	//enable date fields
	if (jQuery("input[data-form-field-type=date]").length)
	{
		//lazyload css files
 		LazyLoad.css([
			'//cdn-aws.majestic3.com/bootstrap/bootstrap-datepicker/dist/css/bootstrap-datepicker3.standalone.min.css',
 		]);

		//lazyload additional files
		LazyLoad.js([
		    '//cdn-aws.majestic3.com/bootstrap/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',
		], function () {
			jQuery("input[data-form-field-type=date]").attr("readonly", true).datepicker({
				 showOn: "both",
				 buttonImageOnly: true,
				 buttonImage: "/img/icons/small/calendar.png",
				 buttonText: "Calendar",
				 dateFormat: "dd/mm/yy",
				 changeMonth: true,
				 changeYear: true
			});
		});
	}//end if
});

/**
 * Load provinces where a Country has been selected and province has not been selected
 */
function setProvincesFromCountry(country_id)
{
	var element = jQuery("#province_id");
	//disable element
	element.attr("disabled", "disablebd");
	
	//update element status to updating
	element.empty();
	element.append(jQuery("<option></option>").val("").text("Loading..."));
	
	//load data from api
	jQuery.ajax({
		url: "/utils/ajax/locations/ajax-provinces?country_id=" + country_id,
		type: "GET",
	})
	.done(function (data) {
		objData = jQuery.parseJSON(data);
		if (objData.error == 1)
		{
			alert("Provinces could not be loaded. Error: " + objData.response);
			return false;
		}//end if
		
		element.empty();
		element.append(jQuery("<option></option>").val("").text("--select--"));
		jQuery.each(objData.response, function (i, obj) {
			element.append(jQuery("<option></option>").val(obj.id).text(obj.province));
		});
	})
	.fail(function () {
		alert("Provinces could not be loaded. An unknown error has occurred.");
	});
	
	//enable element
	element.removeAttr("disabled");
}//end function

/**
 * Load cities where a province has been selected and city has not been selected
 */
function setCitiesFromProvince(province_id)
{
	var element = jQuery("#city_id");
	
	//disable element
	element.attr("disabled", "disabled");
	
	//update element status to updating
	element.empty();
	element.append(jQuery("<option></option>").val("").text("Loading..."));
	
	//load data from api
	jQuery.ajax({
		url: "/utils/ajax/locations/ajax-cities?province_id=" + province_id,
		type: "GET",
	})
	.done(function (data) {
		objData = jQuery.parseJSON(data);
		if (objData.error == 1)
		{
			alert("Cities could not be loaded. Error: " + objData.response);
			return false;
		}//end if
		
		element.empty();
		element.append(jQuery("<option></option>").val("").text("--select--"));
		jQuery.each(objData.response, function (i, obj) {
			element.append(jQuery("<option></option>").val(obj.id).text(obj.city));
		});
	})
	.fail(function () {
		alert("Cities could not be loaded. An unknown error has occurred.");
	});
	
	//enable element
	element.removeAttr("disabled");
}//end function

/**
 * Load cities where a country has been selected and city has not been selected
 */
function setCitiesFromCountry(country_id)
{
	var element = jQuery("#city_id");
	
	//disable element
	element.attr("disabled", "disabled");
	
	//update element status to updating
	element.empty();
	element.append(jQuery("<option></option>").val("").text("Loading..."));
	
	//load data from api
	jQuery.ajax({
		url: "/utils/ajax/locations/ajax-cities?country_id=" + country_id,
		type: "GET",
	})
	.done(function (data) {
		objData = jQuery.parseJSON(data);
		if (objData.error == 1)
		{
			alert("Cities could not be loaded. Error: " + objData.response);
			return false;
		}//end if
		
		element.empty();
		element.append(jQuery("<option></option>").val("").text("--select--"));
		jQuery.each(objData.response, function (i, obj) {
			element.append(jQuery("<option></option>").val(obj.id).text(obj.city));
		});
	})
	.fail(function () {
		alert("Cities could not be loaded. An unknown error has occurred.");
	});
	
	//enable element
	element.removeAttr("disabled");
}//end function

/**
 * Set country where a province is selected and country has not been set
 */
function setCountryFromProvince(province_id)
{	
	//load data from api
	jQuery.ajax({
		url: "/utils/ajax/locations/ajax-provinces?province_id=" + province_id,
		type: "GET",
	})
	.done(function (data) {
		objData = jQuery.parseJSON(data);
		if (objData.error == 1)
		{
			alert("Province could not be loaded. Error: " + objData.response);
			return false;
		}//end if
		
		jQuery.each(objData.response, function (i, obj) {
			//update country dropdown
			jQuery("#country_id option[value='" + obj.country_id + "']").attr("selected", "selected");

			return false;
		});
	})
	.fail(function () {
		alert("Provinces could not be loaded. An unknown error has occurred.");
	});
}//end function

/**
 * Set province where a city is selected and province has not been set
 */
function setProvinceFromCity(city_id)
{
	//load data from api
	jQuery.ajax({
		url: "/utils/ajax/locations/ajax-cities?city_id=" + city_id,
		type: "GET",
	})
	.done(function (data) {
		objData = jQuery.parseJSON(data);
		if (objData.error == 1)
		{
			alert("Province could not be loaded. Error: " + objData.response);
			return false;
		}//end if
		
		jQuery.each(objData.response, function (i, obj) {
			//update province dropdown
			jQuery("#province_id option[value='" + obj.province_id + "']").attr("selected", "selected");
			
			//trigger country update
			setCountryFromProvince(obj.province_id);
			return false;
		});
	})
	.fail(function () {
		alert("Provinces could not be loaded. An unknown error has occurred.");
	});
}//end function



