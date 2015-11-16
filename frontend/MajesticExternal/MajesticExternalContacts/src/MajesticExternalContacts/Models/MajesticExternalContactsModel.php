<?php
namespace MajesticExternalContacts\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontContacts\Entities\FrontContactsContactEntity;
use MajesticExternalContacts\Forms\MajesticExternalContactUnsubscribeForm;

class MajesticExternalContactsModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Common Comm Vias Model
	 * @var \CommonCommVias\Models\CommonCommViasModel
	 */
	private $model_common_comm_vias;

	/**
	 * Load contact details
	 * @param mixed $reg_id
	 * @return \FrontContacts\Entities\FrontContactsContactEntity
	 */
	public function loadContact($reg_id)
	{
		//request latest data from api
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		$objContactLoginDetails = $this->setContactLogin($reg_id);
		$objApiRequest->setAPIKey($objContactLoginDetails->api_key);

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts?id=$reg_id");

		//request data
		$objContact = $objApiRequest->performGETRequest(array())->getBody()->data;

		//create entity
		$objContactEntity = $this->getServiceLocator()->get("FrontContacts\Entities\FrontContactsContactEntity");
		$objContactEntity->set($objContact);

		return $objContactEntity;
	}//end function

	/**
	 * Load channel subscribption information for a conact
	 * @param mixed $reg_id
	 * @return stdClass
	 */
	public function loadContactSubscriptionStatus($reg_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		$objContactLoginDetails = $this->setContactLogin($reg_id);
		$objApiRequest->setAPIKey($objContactLoginDetails->api_key);

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$reg_id/unsubscribe");

		//request data
		$objContactChannels = $objApiRequest->performGETRequest(array())->getBody()->data;

		return $objContactChannels;
	}//end function

	/**
	 * Update contact comm channels - Unsubscribe from channel
	 * @param mixed $reg_id
	 * @param array $arr_data
	 */
	public function updateContactUnsubscribeChannels($reg_id, array $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		$objContactLoginDetails = $this->setContactLogin($reg_id);
		$objApiRequest->setAPIKey($objContactLoginDetails->api_key);

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$reg_id/unsubscribe");

		//request data
		$objContactChannels = $objApiRequest->performPUTRequest($arr_data)->getBody()->data;

		return $objContactChannels;
	}//end function

	/**
	 * Update contact comm channels - Resubscribe/Subscribe to a channel
	 * @param mixed $reg_id
	 * @param array $arr_data
	 */
	public function updateContactResubscribeChannels($reg_id, array $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		$objContactLoginDetails = $this->setContactLogin($reg_id);
		$objApiRequest->setAPIKey($objContactLoginDetails->api_key);

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$reg_id/resubscribe");

		//request data
		$objContactChannels = $objApiRequest->performPUTRequest($arr_data)->getBody()->data;

		return $objContactChannels;
	}//end function

	/**
	 * Create an instance of the Contact Unsubscribe form
	 * @param stdClass $objContactChannels
	 * @param mixed $reg_id
	 * @return \MajesticExternalContacts\Forms\MajesticExternalContactUnsubscribeForm
	 */
	public function getContactUnsubscribeForm($objContactChannels, $reg_id)
	{
		$objForm = 	new MajesticExternalContactUnsubscribeForm();

		/**
		 * Load profile communication channels
		 */
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		$objContactLoginDetails = $this->setContactLogin($reg_id);
		$objApiRequest->setAPIKey($objContactLoginDetails->api_key);

		//setup the object and specify the action
		$objApiRequest->setApiAction("profiles/comm-channels");

		$objResult = $objApiRequest->performGETRequest(array())->getBody();

		foreach ($objResult->data as $objCommVia)
		{
			if (!is_numeric($objCommVia->id))
			{
				continue;
			}//end if

			if ($objCommVia->active == "1")
			{
				$objForm->add(array(
						"name" => "comm_via_id_" . $objCommVia->id,
						"type" => "checkbox",
						"attributes" => array(
								"id" => "comm_via_id_" . $objCommVia->id,
								"title" => "Check to unsubscribe from " . $objCommVia->comm_via,
						),
						"options" => array(
								"label" => $objCommVia->comm_via,
								"use_hidden_element" => TRUE,
								"checked_value" => "1",
								"unchecked_value" => "0",
						),
				));
			}//end if
		}//end foreach

		//add all channels option
		$objForm->add(array(
				"name" => "comm_via_id_all",
				"type" => "checkbox",
				"attributes" => array(
						"id" => "comm_via_id_all",
						"title" => "Check this box to unsubscribe from all channels",
				),
				"options" => array(
						"label" => "All Channels",
						"use_hidden_element" => TRUE,
						"checked_value" => "1",
						"unchecked_value" => "0",
				),
		));

		//add submit button
		$objForm->add(array(
			"name" => "submit",
			"type" => "submit",
			"attributes" => array(
				"value" => "Unsubscribe",
			),
		));

		//set form data
		foreach ($objContactChannels as $channel => $objChannelData)
		{
			if ($objChannelData->status == 1 && $objForm->has("comm_via_id_" . $objChannelData->channel_id))
			{
				//mark checkbox as checked
  				$objForm->get("comm_via_id_" . $objChannelData->channel_id)->setAttribute("checked", "checked");
			}//end if
		}//end foreach

		return $objForm;
	}//end function

	/**
	 * Mark a journey status as not interested for a contact
	 * @param mixed $reg_id
	 * @param mixed $comm_history_id
	 * @return stdClass
	 */
	public function setNoInterestJourneyStatus($reg_id, $comm_history_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		$objContactLoginDetails = $this->setContactLogin($reg_id);
		$objApiRequest->setAPIKey($objContactLoginDetails->api_key);

		//setup the object and specify the action
		$objApiRequest->setApiAction("utils/contact/noint");

		//request data
		$objJourneyStatus = $objApiRequest->performPOSTRequest(array("comm_history_id" => $comm_history_id))->getBody()->data;

		return $objJourneyStatus;
	}//end function

	/**
	 * Check if a user is logged in
	 * If not, setup a session with the correct key for form submission to work
	 * @param int $form_id
	 * @return stdClass
	 */
	private function setContactLogin($reg_id)
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

	/**
	 * Create an instance of the Common Comm Vias Model
	 * @return \CommonCommVias\Models\CommonCommViasModel
	 */
	private function getCommonCommViasModel()
	{
		if (!$this->model_common_comm_vias)
		{
			$this->model_common_comm_vias = $this->getServiceLocator()->get("CommonCommVias\Models\CommonCommViasModel");
		}//end if

		return $this->model_common_comm_vias;
	}//end function
}//end class
