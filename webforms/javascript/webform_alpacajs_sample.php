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
 * This example aims to show one could use a library specific to generating forms. In this example, it is only used
 * to generate the form output and is used as simple html. 
 * 
 * This library used is available at http://www.alpacajs.org and is really worthwhile a good read.
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
$json = str_replace(array("'"), array("\'"), $json);

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
        <link type="text/css" rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" />
        <link type="text/css" href="//code.cloudcms.com/alpaca/1.5.14/bootstrap/alpaca.min.css" rel="stylesheet" />
        <script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/3.0.3/handlebars.js"></script>
        <script type="text/javascript" src="//code.cloudcms.com/alpaca/1.5.14/bootstrap/alpaca.min.js"></script>
    </head>
    <body>
        <div id="form_container">
        	<form id="form" method="post">
        	
        	</form>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                var objFormData = jQuery.parseJSON('<?php echo $json;?>');
                var objForm = objFormData.response.objForm;

                //handler for form submit
                jQuery("#form").submit(function (e) {
					//disable form behaviour
					e.preventDefault();

					//send data via ajax
					jQuery.when(jQuery.ajax({
						"method": "post",
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
									
											jQuery("#" + element).after(error_container);
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
						})
					);
                });

                //create field collections
                var properties = {};
                var fields = {};
				jQuery.each(objForm.objFormElements, function (name, objElement) {

					switch (objElement.type)
					{
						default:
							properties[name] = {
								"type": "string",
								"title": objElement.label
							};
	
							fields[name] = {
								"type": objElement.type,
								"id": objElement.name
							};

							//carry on for more specific element types
							switch (objElement.type)
							{
								case "select": //http://www.alpacajs.org/docs/fields/select.html
									var k = [];
									var v = [];
									jQuery.each(objElement.options.value_options, function (value, label) {
										k.push(value);
										v.push(label);
									});
									
									properties[name] = {
										"type": "string",
										"title": objElement.label,
										"enum": k
									};
									
									fields[name] = {
										"datasource": objElement.options.value_options,
										"type": objElement.type,
										"optionLabels": v
									};
									break;
							}//end switch
							break;

						case "submit":
							//ignore element
							break;
					}//end switch

				});
				
                //build form element using alpaca
                //http://www.alpacajs.org/documentation.html
                jQuery("#form").alpaca({
                    "schema": {
                        "title": objForm.form,
                        "description":"Set a title if you need to",
                        "type":"object",
                        "properties" : properties
                    },
                    "options": {
                        "helper": "Maybe something here to help users complete the form perhaps...",
                        	"fields": fields
                    }
                })
                .append(jQuery("<input/>", {"type": "submit", "value": "Submit"}));
            });
        </script>
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



