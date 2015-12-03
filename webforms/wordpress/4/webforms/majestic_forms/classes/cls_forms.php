<?php
/*
 Copyright (c) 2015 Majestic 3 http://majestic3.com

 Permission is hereby granted, free of charge, to any person obtaining
 a copy of this software and associated documentation files (the
 "Software"), to deal in the Software without restriction, including
 without limitation the rights to use, copy, modify, merge, publish,
 distribute, sublicense, and/or sell copies of the Software, and to
 permit persons to whom the Software is furnished to do so, subject to
 the following conditions:

 The above copyright notice and this permission notice shall be included
 in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class cls_forms
{
	/**
	 * Contains the form id
	 * @var int
	 */
	private $form_id;

	/**
	 * Container for the form field elements
	 * @var array
	 */
	private $arr_form_elements = array();

	/**
	 * Container for the Form Data received from API
	 * @var stdClass
	 */
	private $objFormData;

	/**
	 * Container for the API Request Model
	 * @var cls_execute_request
	 */
	private $objApiRequest;

	/**
	 * Container for base api url
	 * @var string
	 */
	private $api_url;

	/**
	 * Some general form attributes
	 * @var string
	 */
	public $form_method = "post";

	/**
	 * Some general form attributes
	 * @var string
	 */
	public $form_action = "";

	/**
	 * Some general form attributes
	 * @var string
	 */
	public $form_class= "my-custom-form";

	/**
	 * Some general form attributes
	 * @var string
	 */
	public $form_name = "frmMyForm";

	/**
	 * Some general form attributes
	 * @var string
	 */
	public $form_css_id = "frmMyForm";

	/**
	 * Messages to be displayed on the form
	 * @var array
	 */
	public $form_messages = array();

	/**
	 * Inject API Request Object
	 * @param unknown $objApiRequest
	 */
	public function __construct($objApiRequest)
	{
		$this->objApiRequest = $objApiRequest;
		$this->api_url = $objApiRequest->getKey("api_url");
	}//end function

	/**
	 * Generate the form and its elements
	 * @param unknown $form_id
	 */
	public function generateForm($form_id)
	{
		$this->form_id = $form_id;
		$this->requestFormData();
	}//end function

	/**
	 * Obtain form HTML
	 * @param array $arr_form_data - Data to be used to populate the form with
	 * @return string
	 */
	public function generateOutput($arr_form_data = array())
	{
		$html = "<form method=\"$this->form_method\" action=\"$this->form_action\" class=\"$this->form_class\" name=\"$this->form_name\" id=\"$this->form_css_id\">";

		//add form messages
		if (is_array($this->form_messages) && count($this->form_messages) > 0)
		{
			$html .= "<ul class=\"form-messages-list\">";
			foreach ($this->form_messages as $message)
			{
				$html .= "<li><div class=\"form-message\">$message</div></li>";
			}//end foreach
			$html .= "</ul>";
		}//end if

		//inject elements
		foreach ($this->arr_form_elements as $objField)
		{
			if (isset($arr_form_data[$objField->name]))
			{
				$objField->setElementValue($arr_form_data[$objField->name]);
			}//end if

			//ignore some elements
			if (strtolower($objField->name) == "submit")
			{
				continue;
			}//end if

			$html .= "<div class=\"form_element\">" . $objField->generateOutput() . "</div>";
		}//end foreach

		//add submit button
		$html .= "<div class=\"form_element\"><input type=\"submit\" value=\"" . $this->objFormData->submit_button . "\" name=\"submit\"/></div>";
		$html .= "</form>";

		return $html;
	}//end function

	/**
	 * Submit the form and its data to the API and process the response
	 * @param array $arr_form_data
	 */
	public function submitForm(array $arr_form_data)
	{
		//check which form type has been received
		switch ($this->objFormData->form_types_behaviour)
		{
			default:
				$api_url = $this->api_url . "/forms/external/" . $this->objFormData->id;
				$this->objApiRequest->setKey("api_url", $api_url);
				$objResult = $this->objApiRequest->performCreateAction($arr_form_data, array("fid" => $this->objFormData->id));

				//process response
				if ($objResult->HTTP_RESPONSE_CODE != 200)
				{
					$this->form_messages[] = $objResult->HTTP_RESPONSE_MESSAGE;

					//add errors to elements
					foreach ($this->arr_form_elements as $objField)
					{
						foreach ($objResult->data as $objFieldResponse)
						{
							if (!isset($objFieldResponse->attributes))
							{
								continue;
							}//end if

							if (!isset($objFieldResponse->attributes->name) || !is_object($objFieldResponse) || $objFieldResponse->attributes->name != $objField->name)
							{
								continue;
							}//end if

							//set error message
							if (isset($objFieldResponse->messages) && count($objFieldResponse->messages) > 0)
							{
								$objField->setErrors((object) $objFieldResponse->messages);
							}//end if
						}//end foreach
					}//end foreach
				} else {
					$this->form_messages[] = "Form has been submitted";
				}//end if
				break;
		}//end switch
	}//end function

	/**
	 * Request form data and its elements data from the API
	 */
	private function requestFormData()
	{
		//set endpoint
		$api_url = $this->api_url . "/forms/form/" . $this->form_id;

		//request form data from the api
		$this->objApiRequest->setKey("api_url", $api_url);
		$objData = $this->objApiRequest->performListAction();
		$this->objFormData = $objData->data->form;

		//set some form tags
		if ($this->objFormData->submit_button == "")
		{
			$this->objFormData->submit_button = "Submit";
		}//end if

		/**
		 * Load form fields
		 * Some form types have different endpoint concerning their data
		 * Although all data could be loaded via '/forms/form/" . $this->form_id . "?include_fields=1' endpoint, it only returns raw data
		 * To obtain already defined form elements, use the external form endpoint: '/forms/external/" . $this->form_id'
		 * This will return elements along with some other data such as filter and validators in some cases.
		 */
 		switch ($this->objFormData->form_types_behaviour)
 		{
 			default:
 			case "__web":
 				$url = $this->api_url . "/forms/external/" . $this->form_id;
 				$this->requestWebFormFields($url);
 				break;
 		}//end switch
	}//end function

	/**
	 * Request raw fields associated with a form, this data is not available by the external form endpoint
	 * @param string $url
	 */
	private function requestRawFormFields($url)
	{
		//request form data from the api
		$this->objApiRequest->setKey("api_url", $url);
		$objData = $this->objApiRequest->performListAction();

		//extract field elements
		foreach ($objData->data->fields as $objField)
		{
			//filter our data elements, not really necesarry anymore
			if ((!is_numeric($objField->fields_std_id) && !is_numeric($objField->fields_custom_id)))
			{
				continue;
			}//end if

			$objFormElement = new cls_form_element($objField);
			$this->arr_form_elements[] = $objFormElement;
		}//end foreach
	}//end function

	/**
	 * Request web form fields
	 * @param string $url
	 */
	private function requestWebFormFields($url)
	{
		$this->objApiRequest->setKey("api_url", $url);
		$objData = $this->objApiRequest->performListAction();

		//extract field elements
		foreach ($objData->data->arr_fields as $objField)
		{
			$objPredefinedField = new cls_form_element_predefined($objField);

			$objFormElement = new cls_form_element($objPredefinedField);
			$this->arr_form_elements[] = $objFormElement;
		}//end foreach
	}//end function
}//end class