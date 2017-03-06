<?php
namespace MajesticExternalUtilities\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontUserLogin\Models\FrontUserSession;

class MajesticExternalUtilitiesModel extends AbstractCoreAdapter
{
	/**
	 * Process a trackable link
	 * @param array $arr_data
	 * @return stdClass
	 */
	public function trackLinkData($arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//load authentication details
		$arr_data["util"] = "track-link";
		$objRequestAuthentication = $this->setRequestLogin($arr_data);
		$objApiRequest->setAPIKey($objRequestAuthentication->api_key);

		//setup the object and specify the action
		$objApiRequest->setApiAction("utils/track/link");
		$objTrackedLink = $objApiRequest->performPOSTRequest($arr_data)->getBody()->data;

		//check if url has been received
		if ($objTrackedLink->url == "")
		{
			throw new \Exception("Operation could not be completed. URL is not set. Request has been terminated", 500);
		}//end if

		return $objTrackedLink;
	}//end function

	/**
	 * View a communication online
	 * Although comm_history_id and comm_id is optional, either one is created. comm_history_id takes precedence over the comm id
	 * @param mixed $comm_history_id - Optional, where set, the comm will be processed as if sent to a contact
	 * @param mixed $comm_id - Optional, where comm history id is not, the comm content will be produced without processing replace fields
	 * @return stdClass
	 */
	public function viewCommOnline($comm_history_id, $comm_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//load authentication details
		$arr_data = array(
			"comm_history_id" => $comm_history_id,
			"comm_id" => $comm_id,
		);

		$arr_data["util"] = "view-comm-online";

		//where the comm history id is not, us user is probably trying to preview the communication which requires a user session
		if ($comm_history_id == '' && !FrontUserSession::isLoggedIn())
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : You must be logged in the view this page", 500);
		}//end if

		//where comm history id is set, use util to authenticate the request regardless of user being logged in
		if ($comm_history_id != '')
		{
			$objRequestAuthentication = $this->setRequestLogin($arr_data);
			$objApiRequest->setAPIKey($objRequestAuthentication->api_key);
		}//end if

		//setup the object and specify the action
		$objApiRequest->setApiAction("utils/comms/view");
		$objResult = $objApiRequest->performGETRequest($arr_data)->getBody();
		$objCommContent = $objResult->data;

		return $objCommContent;
	}//end function

	/**
	 * Load a list of locations
	 * @param string $type - country, province or cities
	 * @param array $arr_data - Default = FALSE, required for provinces and cities requests
	 * @return stdClass
	 */
	public function loadLocations($type, $arr_data = FALSE)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		$arr_data["util"] = "location-data";
//@TODO cache key
		$objRequestAuthentication = $this->setRequestLogin($arr_data);
		$objApiRequest->setAPIKey($objRequestAuthentication->api_key);

		switch (strtolower($type))
		{
			case "country":
			case "countries":
			case "default":
				//setup the object and specify the action
				$objApiRequest->setApiAction("locations/countries?qp_export_fields=id,code,country,active&qp_limit=all&qp_disable_hypermedia=1");
				$arr_request = array(

				);
				break;

			case "province":
			case "provinces":
			case "state":
			case "states":
				//setup the object and specify the action
				$objApiRequest->setApiAction("locations/provinces");
				$arr_request = array(
									"country_id" => $arr_data["country_id"],
									"city_id" => $arr_data["city_id"],
								);
				break;

			case "city":
			case "cities":
				//setup the object and specify the action
				$objApiRequest->setApiAction("locations/cities");
				$arr_request = array(
									"country_id" => $arr_data["country_id"],
									"province_id" => $arr_data["province_id"],
								);
				break;
		}//end switch

		//check if cached data is available
		$objData = $objApiRequest->performGETRequest($arr_request)->getBody();
		return $objData->data;
	}//end function

	/**
	 * Create a authenticated gate for processes to complete
	 * @param array $arr_data
	 * @return stdClass
	 */
	private function setRequestLogin(array $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//disable api session login
		$objApiRequest->setAPISessionLoginDisable();

		//load master user details
		$arr_user = $this->getServiceLocator()->get("config")["master_user_account"];

		//set api request authentication details
		$objApiRequest->setAPIKey($arr_user['apikey']);
		$objApiRequest->setAPIUser(md5($arr_user['uname']));
		$objApiRequest->setAPIUserPword(md5($arr_user['pword']));

		//setup the object and specify the action
		$objApiRequest->setApiAction("utils/authenticate");

		//set payload
		$arr_data["tstamp"] = time();
		$arr_data['key'] = $arr_user['apikey'];

		$objData = $objApiRequest->performPOSTRequest($arr_data)->getBody();

		return $objData->data;
	}//end function
}//end class
