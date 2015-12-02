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

class cls_execute_request
{
	/**
	 * Set the api url to call
	 * @var string
	 */
	private $api_url;
	
	/**
	 * Set api key for request
	 * @var string
	 */
	private $api_key;
	
	/**
	 * Set api username for request
	 * @var string
	 */
	private $api_username;
	
	/**
	 * Set api password for request
	 * @var string
	 */
	private $api_password;
	
	/**
	 * Read set object value
	 * @param string $key
	 */
	public function getKey($key)
	{
		return $this->$key;
	}//end function
	
	/**
	 * Set private value
	 * @param string $key
	 * @param mixed $value
	 */
	public function setKey($key, $value)
	{
		$this->$key = $value;
	}//end function
	
	/**
	 * Set private value from an array
	 * @param array $arr_data
	 */
	public function setKeyFromArray(array $arr_data)
	{
		foreach ($arr_data as $k => $v)
		{
			$this->$k = $v;
		}//end foreach
	}//end function
	
	/**
	 * Execute a GET request
	 * Depending on the url set, this will return either a list of records, or a single record if any exists
	 * pertaing to the request performed
	 * @param array $arr_query_vars - Optional. Supplied parameters will be url encoded and appended to the 
	 * set API Url.
	 * @return stdClass
	 */
	public function performListAction($arr_query_vars = FALSE)
	{
		//execute the request
		$result = $this->executeRequest("GET", NULL, $arr_query_vars);

		//return response
		return $this->decodeResponse($result);
	}//end function
	
	/**
	 * Perform a POST request
	 * In almost all cases, POST is required to create a new record
	 * @param array $arr_data
	 * @param array $arr_query_vars - Optional. Supplied parameters will be url encoded and appended to the 
	 * set API Url.
	 * @return stdClass
	 */
	public function performCreateAction(array $arr_data, $arr_query_vars = FALSE)
	{
		$result = $this->executeRequest("POST", $arr_data, $arr_query_vars);
		
		return $this->decodeResponse($result);
	}//end function
	
	/**
	 * Perform a PUT request
	 * Using the PUT HTTP Method is exclusivly used for updating exists records
	 * @param array $arr_data
	 * @param array $arr_query_vars - Optional. Supplied parameters will be url encoded and appended to the 
	 * set API Url.
	 * @return stdClass
	 */
	public function performUpdateAction(array $arr_data, $arr_query_vars = FALSE)
	{
		$result = $this->executeRequest("PUT", $arr_data, $arr_query_vars);
		
		return $this->decodeResponse($result);
	}//end function
	
	/**
	 * Perform a DELETE request
	 * As the name implies, this will delete an existing record
	 * @param array $arr_query_vars - Optional. Supplied parameters will be url encoded and appended to the 
	 * set API Url.
	 * @return stdClass
	 */
	public function performDeleteAction($arr_query_vars = FALSE)
	{
		$result = $this->executeRequest("DELETE", NULL, $arr_query_vars);
		
		return $this->decodeResponse($result);
	}//end function
	
	/**
	 * Set request headers
	 * @return array
	 */
	private function setRequestHeaders()
	{
		//check all required values are set
		$arr_required_values = array(
			"api_url",
			"api_key",
			"api_username",
			"api_password",	
		);
		foreach ($arr_required_values as $value)
		{
			if (!$this->$value)
			{
				throw new Exception("API Request could not be performed. '$value' is not set", 500);
			}//end if
		}//end foreach
		
		include __DIR__ . "/generate_headers.php";		
		return $arr_headers;
	}//end function
	
	/**
	 * Perform an API request
	 * @throws Exception
	 * @return string
	 */
	private function executeRequest($method, $arr_data, $arr_query_vars = FALSE)
	{
		//check if curl is installed
		if (!in_array("curl", get_loaded_extensions()))
		{
			throw new Exception("API Request could not be performed. PHP CURL is not installed", 500);	
		}//end if
		
		//was any additional query params received?
		if (is_array($arr_query_vars))
		{
			$str = "";
			foreach ($arr_query_vars as $k => $v)
			{
				$str .= "$k=" . urlencode($v) . "&";
			}//end foreach
			$str = rtrim($str, "&");
				
			//append variables to url
			if (strpos($this->api_url, "?") !== FALSE)
			{
				$this->api_url .= "&$str";
			} else {
				$this->api_url .= "?$str";
			}//end if
		}//end if
		
		//start curl
		$ch = curl_init();

		//set curl options
		curl_setopt($ch, CURLOPT_URL, $this->api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->setRequestHeaders());
		
		//disable ssl certificate check
		/**
		 * Uncomment where you get SSL verification errors
		 * http://ademar.name/blog/2006/04/curl-ssl-certificate-problem-v.html
		 */
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		//which request type should be used?
		switch (strtoupper($method))
		{
			case "GET":
		
				break;
		
			case "POST":
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr_data));
				break;
		
			case "PUT";
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arr_data));
			break;
		
			case "DELETE":
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;
		}//end switch
		
		//execute the request
		$result = curl_exec($ch);

		//check for errors
		if(curl_errno($ch))
		{
			throw new Exception("API Request failed: " . curl_error($ch), 500);
		}//end if
		
		//close the request handle
		curl_close($ch);
		
		return $result;
	}//end function
	
	/**
	 * Decode response received from api
	 * @param string $json
	 * @return stdClass
	 */
	private function decodeResponse($json)
	{
		$objResponse = json_decode($json);
		
		//check if valid response has been received
		if (!is_object($objResponse))
		{
			throw new Exception("API Request failed. Response could not be decoded. Result: $json", 500);
		}//end if
		
		return $objResponse;
	}//end function
}//end class