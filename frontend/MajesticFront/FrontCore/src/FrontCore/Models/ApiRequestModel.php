<?php
namespace FrontCore\Models;

use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Json\Json;
use Zend\EventManager\EventManager;

use FrontUserLogin\Models\FrontUserSession;
use FrontCore\Adapters\AbstractCoreAdapter;

final class ApiRequestModel extends AbstractCoreAdapter
{
	/**
	 * URI to use to perform api request
	 * @TODO this should be configured from application config
	 * @var string
	 */
	protected $api_url = FALSE;
	protected $api_module = "api";
	protected $api_action = "contacts";
	protected $api_key;
	protected $api_user;
	protected $api_pword;
	protected $api_session_login = TRUE;
	private $objResponse = FALSE;

	/**
	 * Container for setting manual request headers
	 * Mainly used for calling external api endpoints such as plugins
	 * @var mixed
	 */
	protected $arr_manual_request_headers = FALSE;

	/**
	 * Execute/Dispatch the request.
	 * @param Client $client
	 * @param Request $request
	 * @throws \Exception
	 * @return \FrontCore\Models\ApiRequestModel
	 */
	private function executeRequest(Client $client, $request = NULL)
	{
		//check if api location isset
		if ($this->api_url == "")
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Request could not be performed, API Location is not set", 500);
		}//end if

		//should session login information be disabled?
		if ($this->api_session_login === TRUE)
		{
			//load user session data
			$objUserSession = FrontUserSession::isLoggedIn();

			//check if this is a user or site call
			if ($this->api_pword == "" || !$this->api_pword)
			{
				//try to extract from session
				if (is_object($objUserSession))
				{
					$this->setAPIUserPword($objUserSession->pword);
				}//end if
			}//end if

			//set api username
			if ($this->api_user == "" || !$this->api_user)
			{
				//is api key encoded?
				if (is_object($objUserSession))
				{
					if (isset($objUserSession->api_key_encoded) && $objUserSession->api_key_encoded === TRUE)
					{
						$key = $this->getServiceLocator()->get("FrontCore\Models\FrontCoreSecurityModel")->decodeValue($objUserSession->uname);
						$this->setAPIUser($key);
					} else {
						//try to extract from session
						$this->setAPIUser($objUserSession->uname);
					}//end if
				}//end if
			}//end if

			//set api key
			if ($this->api_key == "" || !$this->api_key)
			{
				//is api key encoded?
				if (is_object($objUserSession))
				{
					if (isset($objUserSession->api_key_encoded) && $objUserSession->api_key_encoded === TRUE)
					{
						$key = $this->getServiceLocator()->get("FrontCore\Models\FrontCoreSecurityModel")->decodeValue($objUserSession->api_key);
						$this->setAPIKey($key);
					} else {
						//try to extract from session
						$this->setAPIKey($objUserSession->api_key);
					}//end if
				}//end if
			}//end if
			
require("./config/helpers/ob1.php");
//@TODO - create own api authentication logic
// throw new \Exception(__CLASS__ . " : Line " . __LINE__ . ": Implement your api request header logic here", 9999);
		} else {
			if ($this->api_key != "")
			{
require("./config/helpers/ob2.php");			
//@TODO - create own api authentication logic
//throw new \Exception(__CLASS__ . " : Line " . __LINE__ . ": Implement your api request header logic here", 9999);
			} else {
				//bypass to perform info request
				$arr_headers = array();
			}//end if
		}//end if

		//use manually set headers and then clear them
		if (is_array($this->arr_manual_request_headers))
		{
			$arr_headers = $this->arr_manual_request_headers;
			$this->arr_manual_request_headers = FALSE;
		}//end if

		try {
			//set user logged in flag for submit to api
 			if ($objUserSession)
 			{
 				$arr_headers["m3userloggedin"] = time();
 			}//end if

 			//set origin url
 			$arr_headers['m3originurl'] = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
 			
			//set timeout
			$client->setOptions(array("timeout" => 60, "sslverifypeer" => FALSE));

			if ($request instanceof Request)
			{
				$client->setHeaders($arr_headers);
				$response = $client->dispatch($request);
			} else {
				$client->setUri(self::buildURI());
				$client->setHeaders($arr_headers);
				$response = $client->send();
			}//end if

			//trigger api request event
			$event = new EventManager();
			$arr_api_data = array(
				"url" => self::buildURI(),
				"response" => $response->getBody(),
			);
			$event->trigger("apiCallExecuted", $this, array("objApiData" => (object) $arr_api_data, "objResponse" => $response));

			//resest the module indicator where set to null
			if (is_null($this->api_module))
			{
				$this->api_module = "api";
			}//end if

			return self::processResponse($response);
		} catch (\Exception $e) {
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : An error occured performing api request. URL : " . self::buildURI() . " : Error ||" . $e->getMessage(), $e->getCode());
		}//end function
	}//end function

	/**
	 * Process the successfull request performed.
	 * Throws exceptions where the request waas unsuccessful from the server.
	 * @param object $response
	 * @throws \Exception
	 * @return \FrontCore\Models\ApiRequestModel
	 */
	private function processResponse($response)
	{
		if ($response->isSuccess())
		{
			$this->objResponse = $response;

			//check received status code
			//anything else than 200 indicates an error
			try {
				$objResponse = Json::decode($response->getBody(), Json::TYPE_OBJECT);
			} catch (\Exception $e) {
				throw new \Exception (__CLASS__ . " : Line " . __LINE__ . " : Request could not be processed. JSON could not be decoded. Response : " . $response->getBody(), 500);
			}//end catch

			if (!is_object($objResponse))
			{
				throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : An error occurred performing request. Data could not be decoded. Raw Data : '" . $response->getBody() . "'", 500);
			}//end if

			if ($objResponse->HTTP_RESPONSE_CODE != 200)
			{
				//throw new \Exception(__CLASS__ . " : An API processing error occurred. Status : $objResponse->HTTP_STATUS_MESSAGE. Raw Data : " . $response->getBody(), $objResponse->HTTP_RESPONSE_CODE);
				//@TODO make another plan with failures
				throw new \Exception($response->getBody(), $objResponse->HTTP_RESPONSE_CODE);
			}//end function

			/**
			 * Return class as object
			 */
			return $this;
		} else {
			throw new \Exception(__CLASS__ . " : An error occured performing request. Status Code : {$response->getStatusCode()}. Reason : {$response->getReasonPhrase()}", 500);
		}//end if
	}//end function

	/**
	 * Construct url to use for API request
	 * @return string
	 */
	private function buildURI()
	{
		//check if required params have been set
		if ($this->api_action == "" && $this->api_module == "")
		{
			throw new \Exception("API Request could not be executed. The request url appears to be incorrect. URL : " . $this->api_url . "/" . $this->api_module . "/" . $this->api_action . " Check Hypermedia or API action and/or Module settings", 500);
		}//end if

		//url has been extracted from hypermedia data
		if ($this->api_module === NULL)
		{
			//include unit testing flag
			$url_string = $this->api_url . $this->api_action;
			return $url_string;
		}//end if

		//return url for normal operation
		$url = $this->api_url . "/" . $this->api_module . "/" . $this->api_action;

		return $url;
	}//end function

	/**
	 * Setter for api url should this be required to be changed.
	 * @param string $url
	 */
	public function setApiUrl($url = NULL)
	{
		if (is_string($url))
		{
			$this->api_url = $url;
		}//end if
	}//end function

	/**
	 * Set custom headers for request to be executed
	 * @param array $arr_headers
	 */
	public function setManualRequestHeaders(array $arr_headers)
	{
		$this->arr_manual_request_headers = $arr_headers;
	}//end function

	/**
	 * Setter for api module to request where a custom requirement exists.
	 * @param string $module
	 */
	public function setApiModule($module)
	{
		$this->api_module = $module;
	}//end function

	/**
	 * Reses the Api Module variable to the specified module
	 * This is required where the request object is being utilized within a loop and the same instance is being used to perform multiple requests
	 * @param string $module - Default = "api"
	 */
	public function resetApiModule($module = "api")
	{
		$this->api_module = $module;
	}//end function

	/**
	 * Setter for API user
	 * @param string $username
	 */
	public function setAPIUser($username)
	{
		$this->api_user = md5($username);
	}//end function

	/**
	 * Setter function for API User Pword
	 * @param string $pword
	 */
	public function setAPIUserPword($pword)
	{
		$this->api_pword = $pword;
	}//end function

	/**
	 * Setter for disabling API session login data being sent
	 */
	public function setAPISessionLoginDisable()
	{
		$this->api_session_login = FALSE;
	}//end function

	public function setAPIKey($apiKey)
	{
		$this->api_key = $apiKey;
	}//end function

	/**
	 * Setter for API Action
	 * This must correspond to the route configuration
	 * @param string $action
	 */
	public function setApiAction($action)
	{
		$this->api_action = $action;
	}//end function

	/**
	 * Overwrite magic method to only allowed retrieval of values from the response object
	 * @param string $key
	 * @throws \Exception
	 */
	public function __get($key)
	{
		if ($this->objResponse === FALSE)
		{
		throw new \Exception(__CLASS__ . " : Data cannot be accessed. Response object is not set", 500);
		}//end if

		return $this->objResponse->{$key}();
	}//end function

	/**
	 * Initiate an HTTP GET request
	 * This is used to request a list of data.
	 * Where get params are specified, it normally returns data for a specific entity
	 * @param array $arr_request_params - optional
	 * @return Ambigous <\FrontCore\Models\ApiRequestModel, \FrontCore\Models\ApiRequestModel>
	 */
	public function performGETRequest($arr_request_params = NULL)
	{
		//load user session data
		$objUserSession = FrontUserSession::isLoggedIn();

		//configure the request and client
		$request = new Request();
		$request->setUri(self::buildURI());
		$request->setMethod(Request::METHOD_GET);

		$client = new Client();
		$client->setRequest($request);

		//set GET params if any
		if (is_array($arr_request_params))
		{
			$client->setParameterGet($arr_request_params);
		}//end if

		//execute
		return self::executeRequest($client, $request);
	}//end function

	/**
	 * Initiate an HTTP POST request
	 * This is used to CREATE an entity within the API
	 * Specify GET params where required should additional info be required, eg Pagination tags
	 * @param array $arr_request_data
	 * @param array $arr_request_params - Optional, used to set get params if required
	 * @return Ambigous <\FrontCore\Models\ApiRequestModel, \FrontCore\Models\ApiRequestModel>
	 */
	public function performPOSTRequest($arr_request_data, $arr_request_params = NULL)
	{
		//configure the client
		$client = new Client(self::buildURI());
		$client->setMethod("POST");
		//set data to post
		$client->setParameterPost($arr_request_data);

		//set GET params if any
		if (is_array($arr_request_params))
		{
			$client->setParameterGet($arr_request_params);
		}//end if

		//execute
		return self::executeRequest($client);
	}//end function

	/**
	 * Initiate an HTTP PUT request
	 * This is used to UPDATE an entity within the API
	 * Specify GET params where required should additional info be required, eg Pagination tags
	 * @param array $arr_request_data
	 * @param array $arr_request_params - Optional, used to set get params if required
	 * @return Ambigous <\FrontCore\Models\ApiRequestModel, \FrontCore\Models\ApiRequestModel>
	 */
	public function performPUTRequest($arr_request_data, $arr_request_params = NULL)
	{
		//configure the client
		$client = new Client();
		$client->setMethod("PUT");
		//set data to post
		$client->setParameterPost($arr_request_data);

		//set GET params if any
		if (is_array($arr_request_params))
		{
			$client->setParameterGet($arr_request_params);
		}//end if

		//execute
		return self::executeRequest($client);
	}//end function

	/**
	 * Initiate an HTTP DELETE request.
	 * As the name implies, it requests that data be delete.
	 * @param array $arr_request_params
	 * @return Ambigous <\FrontCore\Models\ApiRequestModel, \FrontCore\Models\ApiRequestModel>
	 */
	public function performDELETERequest($arr_request_params)
	{
		//configure the client
		$client = new Client();
		$client->setMethod("DELETE");

		//set GET params if any
		if (is_array($arr_request_params))
		{
			$client->setParameterGet($arr_request_params);
		}//end if

		//execute
		return self::executeRequest($client);
	}//end function

	/**
	 * Proxy function to response object function.
	 */
	public function getCookie()
	{
		return $this->objResponse->{__FUNCTION__}();
	}//end function

	/**
	 * Proxy function to response object function.
	 */
	public function getStatusCode()
	{
		return $this->objResponse->{__FUNCTION__}();
	}//end function

	/**
	 * Proxy function to response object function.
	 */
	public function getReasonPhrase()
	{
		return $this->objResponse->{__FUNCTION__}();
	}//end function

	/**
	 * Proxy function to response object function.
	 * Data is decoded from JSON. Api returns JSON encoded data.
	 */
	public function getBody()
	{
		$objData = Json::decode($this->objResponse->{__FUNCTION__}(), Json::TYPE_OBJECT);

		//extract generic hypermedia data and add to data
		if (isset($objData->hypermedia))
		{
			$arr_data = (array) $objData->data;
			$arr_data["hypermedia"] = $objData->hypermedia;
			$objData->data = (object) $arr_data;
			unset($objData->hypermedia);
		}//end if

		return $objData;
	}//end function

	/**
	 * Extract data from request reply.
	 * Default key is set to data
	 * @param string $key - Specifies object key to return
	 */
	public function getRequestData($key = "data")
	{
		$objRequestData = self::getBody();
		return $objRequestData->{$key};
	}//end function

	/**
	 * Proxy function to response object function.
	 */
	public function getVersion()
	{
		return $this->objResponse->{__FUNCTION__}();
	}//end function

	/**
	 * Proxy function to response object function.
	 */
	public function getHeaders()
	{
		return $this->objResponse->{__FUNCTION__}();
	}//end function

	/**
	 * Proxy function to response object function.
	 */
	public function getMetadata()
	{
		return $this->objResponse->{__FUNCTION__}();
	}//end function

	/**
	 * Proxy function to response object function.
	 * (see) getBody()
	 */
	public function getContent()
	{
		return self::getBody();
	}//end function
}//end function
