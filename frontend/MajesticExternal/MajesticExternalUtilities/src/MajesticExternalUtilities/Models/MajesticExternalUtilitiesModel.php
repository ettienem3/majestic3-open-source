<?php
namespace MajesticExternalUtilities\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

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
		$objRequestAuthentication = $this->setRequestLogin($arr_data);
		$objApiRequest->setAPIKey($objRequestAuthentication->api_key);

		//setup the object and specify the action
		$objApiRequest->setApiAction("utils/comms/view");
		$objCommContent = $objApiRequest->performGETRequest($arr_data)->getBody()->data;

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
				$objApiRequest->setApiAction("locations/countries");
				$arr_request = array();
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
//@TODO cache data
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

		return (object) array(
				"api_key" => $arr_user["apikey"],
		);
	}//end function
}//end class
