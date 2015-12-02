<?php
/**
 * This file provides a VERY basic example on how to incorporate Majestic 3 Webforms into your website or as 
 * a standalone page.
 * Please note that your security is your concern and you should make every effort to safeguard yourself against
 * threats that might be opened using the implementation below.
 * See https://github.com/ettienem3/majestic3-open-source/blob/master/LICENCE.md
 * and https://github.com/ettienem3/majestic3-open-source/blob/master/README.md for more information.
 */

/**
 * To use this implementation, you will need the following:
 * URL, this url will be the same you use to login into your profile (This ONLY applies to frontend environments made available by Majestic 3,
 * there is no gaurentee that an open source implementation will provide the same capabilities)
 * 
 * Webform id, this is easily obtained by visiting or previewing a web form created within your profile.
 * The url will look something along the lines of ...../forms/bf/232
 * 232 will be the number your looking for as an example.
 * 
 * You will need to be able to execute a script locally on your website / server to pull form information and submit since you 
 * possibly will have problems getting an ajax request performed across domains.
 * You will also require php5-curl to be running. If not, install a suitable replacement and update the
 * doRequest function at the end of the file.
 * 
 * The example below does not render all possible form elements, we leave that up to you.
 * 
 * The example below does not render all possible form elements, we leave that up to you.
 * This example aims to show one could use a library specific to generating forms. In this example, it is only used
 * to generate the form output and is used as simple html and is purely based on good old jQuery.
 * 
 * Note the JSON part in the $form_url below. This target provides basic capabilities as far as forms is concerned. If you looking for a
 * full featured implementation of all the available web form capabilities, you would need to consider building a complete solution to leverage 
 * all of the functionality webforms can provide. See https://wiki.majestic3.com/?post_type=api-sections&p=21
 */

//set url
$url = ""; //https://wiki.majestic3.com

//set form id
$fid = ""; //232

//set complete url
$form_url = "$url/forms/bf/json/$fid";

//request form information
$json = file_get_contents($form_url);
$json = str_replace("'", "\'", $json);

//process submit
if ($_POST)
{
	$result = doRequest($form_url, $_POST);
	$json = str_replace(array("'", "\\"), array("\'", "\\\\"), $result);
	//return json to ajax request
	echo $json, exit;
}//end if
?>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<script src="//code.jquery.com/jquery-2.1.4.min.js"></script>
		<script type="text/javascript">
			var global_form_json_output = '<?php echo $json;?>';
			jQuery(document).ready(function () {
				//call function to build the form
				var form = buildForm();

				//catch form submit and process the data
				form.submit(function (e) {
					e.preventDefault();

					//clear error messages
					jQuery(".error_container").remove();
					//create your submit handler
					jQuery.when(jQuery.ajax({
						"type": "post",
						"data": jQuery(this).serialize()
					})
					.done(function (data) {
						var objData = jQuery.parseJSON(data);

						if (objData.error == 0)
						{
							//check submit response for errors
							if (typeof objData.response.objForm.submit_errors == "object" && Object.keys(objData.response.objForm.submit_errors).length > 0)
							{
								jQuery.each(objData.response.objForm.submit_errors, function (element, objMessages) {								
									if (Object.keys(objMessages).length > 0)
									{				
										var error_container = jQuery("<div></div>", {"class": "error_container"});
										jQuery.each(objMessages, function (k, m) {
											error_container.append(jQuery("<span></span>", {"class": "error", "html": m}));
										});
								
										jQuery("#form_element_" + element).append(error_container);
									}//end if
								});

								return false;
							} else {
								//check if submit has been confirmed
								if (objData.response.objForm.submit_result == "OK")
								{
									alert("Your information has been saved");
									return false;
								}//end if
							}//end if
						} else {
							//submit failed
							//do something with the error
						}//end if
					}));
				});

				//set content
				jQuery("#form_container").html(form);
			});

			/**
			 * Build form
			 */
			function buildForm()
			{
				//decode json
				var objData = jQuery.parseJSON(global_form_json_output);
				
				//check if errors were encountered
				if (objData.error == 1)
				{
					alert(objData.response);
					return false;
				}//end if
				
				//confirm form data has been received
				if (typeof objData.response.objForm == "undefined")
				{
					alert("Form could not retrieved");
					return false;
				}//end if
			
				//start building the form
				var form = jQuery("<form></form>")
									.attr("id", "simple_webform_sample")
									.attr("method", "post");
				
				//do form elements
				jQuery.each(objData.response.objForm.objFormElements, function (name, objElement) {
					form.append(buildElement(objElement));
				});
				
				return form;
			}//end function

			/**
			 * Build form elements
			 */ 
			function buildElement(objElement)
			{
				switch (objElement.type)
				{
					default:
						var element = jQuery("<input/>", {"type": objElement.type});
					
						if (objElement.type == "checkbox" && objElement.value == "1")
						{
							element.attr("checked", true);
						}//end if
						break;
						
					case "radio":
						var element = jQuery("<div></div>");
						if (typeof objElement.options.value_options != "undefined")
						{
							jQuery.each(objElement.options.value_options, function (key, value) {
								var e = jQuery("<input/>", {type: "radio", value: key, text: value, name: objElement.name});
								
								//append option to select
								e.appendTo(element);
							});
						}//end if
						break;
						
					case "select":
						var element = jQuery("<select></select>");
						
						//add options
						if (typeof objElement.options.empty_option != "undefined")
						{
							element.append(jQuery("<option></option>", {value: '', text: objElement.options.empty_option}));	
						}//end if

						if (typeof objElement.options.value_options != "undefined")
						{
							jQuery.each(objElement.options.value_options, function (key, value) {
								var e = jQuery("<option></option>", {value: key, text: value});
							
								//check if value matched set value received
								if (value == objElement.value)
								{
									e.attr("selected", true);
								}//end if
								
								//append option to select
								e.appendTo(element);
							});
						}//end if

						break;
						
					case "textarea":
						var element = jQuery("<textarea></textarea>");
						break;
				}//end switch
				
				//set element attributes
				jQuery.each(objElement.attributes, function (key, value) {
					element.attr(key, value);
				});
				
				//create label
				var label = jQuery("<label></label>", {text: objElement.label});
				
				//create container
				var container = jQuery("<div></div>", {id: "form_element_" + objElement.name}).attr("class", "form_element_collection form_element_" + objElement.type);
				//add element to label
				element.appendTo(label);			
				//add label to container
				label.appendTo(container);
								
				return container;
			}//end function	
	</script>
	</head>
	<body>
		<div id="form_container"></div>
	</body>
</html>

<?php 
function doRequest($url, $arr_data)
{
	//format data into http query format
	$data_string = http_build_query($arr_data);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 40); //allow wait for 40 seconds
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

	//execute the request
	$result = curl_exec($ch);

	//close the request handle
	curl_close($ch);

	return $result;
}//end function



