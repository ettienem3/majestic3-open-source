<?php
namespace FrontContacts\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontContacts\Entities\FrontContactsContactEntity;
use FrontCore\Forms\FrontCoreSystemFormBase;
use Zend\Stdlib\ArrayObject;
use FrontUserLogin\Models\FrontUserSession;
use Zend\Form\Form;

class FrontContactsModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Users Model
	 * @var \FrontUsers\Models\FrontUsersModel
	 */
	private $model_front_users;

	/**
	 * Contianer for the Statuses Model
	 * @var \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private $model_front_statuses;

	/**
	 * Container for the Front Contacts System Fields Model
	 * @var \FrontContacts\Models\FrontContactsSystemFieldsModel
	 */
	private $model_front_contact_system_fields;

	/**
	 * Request the Contact Profile Form from the API
	 * @return \FrontCore\Forms\FrontCoreSystemFormBase || mixed
	 */
	public function getContactProfileForm($id = NULL)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("forms?form_type=cpp");

		//execute request to load raw data
		$objFormData = $objApiRequest->performGETRequest(array())->getBody()->data;

		//extract form information
		foreach ($objFormData as $objForm)
		{
			if (is_object($objForm) && isset($objForm->id) && $objForm->id != "")
			{
				$arr_forms[$objForm->id] = $objForm->form;
			}//end foreach

			if ($id != "" && $objForm->id == $id)
			{
				$objContactProfileForm = $objForm;
			}//end if
		}//end foreach

		//where multiple profile forms exists, return array for user to select approriate form
		if (count($arr_forms) > 0 && (!isset($objContactProfileForm) || !is_object($objContactProfileForm)))
		{
			return $arr_forms;
		}//end if

		if (isset($objContactProfileForm))
		{
			//request form information from api and construct the form
			$objApiRequest->setApiAction("forms/external/" . $objContactProfileForm->id);

			//execute request to load raw data
			$objFormRawData = $objApiRequest->performGETRequest(array("behaviour" => "__cpp"))->getBody()->data;
			$arr_data["objFormRawData"] = $objFormRawData;

			$objForm = self::constructForm($arr_data["objFormRawData"]);
			return $objForm;
		}//end if
	}//end function

	/**
	 * Create a form catering for filtering contacts
	 * @return \Zend\Form\Form
	 */
	public function getContactFilterForm()
	{
		//load contact statuses
		$objStatuses = $this->getFrontContactStatusesModel()->fetchContactStatuses();
		$arr_statuses = array();
		foreach ($objStatuses as $objStatus)
		{
			if (isset($objStatus->id) && is_numeric($objStatus->id) && $objStatus->active == 1)
			{
				$arr_statuses[$objStatus->id] = $objStatus->status;
			}//end if
		}//end foreach

		//load users
		$objUsers = $this->getFrontUsersModel()->fetchUsers();
		$arr_users = array();
		foreach ($objUsers as $objUser)
		{
			if (isset($objUser->id) && is_numeric($objUser->id) && $objUser->active == 1)
			{
				$arr_users[$objUser->id] = $objUser->uname . ' (' . $objUser->fname . ' ' . $objUser->sname . ')';
			}//end if
		}//end foreach

		//load source values
		$objSources = $this->getFrontContactsSystemFieldsModel()->fetchDistinctContactSources();
		$arr_sources = array();
		foreach ($objSources as $objSource)
		{
			if (isset($objSource->source) && $objSource->source != '')
			{
				$arr_sources[$objSource->source] = $objSource->source;
			}//end if
		}//end foreach

		//load reference values
		$objReferences = $this->getFrontContactsSystemFieldsModel()->fetchDistinctContactReferences();
		$arr_references = array();
		foreach ($objReferences as $objReference)
		{
			if (isset($objReference->reference) && $objReference->reference != '')
			{
				$arr_references[$objReference->reference] = $objReference->reference;
			}//end if
		}//end foreach

		$objForm = new Form();
		$objForm->add(array(
				'name' => 'keyword',
				'type' => 'text',
				'attributes' => array(
					'id' => 'keyword',
					'title' => 'Enter a keyword. This value is use to search over a combination of different values',
					'placeholder' => 'e.g. First Name / Email'
				),
				'options' => array(
					'label' => 'Keyword',
				),
		));

		$objForm->add(array(
				'name' => 'regtbl_date_created_start',
				'type' => 'text',
				'attributes' => array(
						'id' => 'regtbl_date_created_start',
						'title' => 'Enter a start date to limit data from',
						'placeholder' => 'from date'
				),
				'options' => array(
						'label' => 'From date',
				),
		));

		$objForm->add(array(
				'name' => 'regtbl_date_created_end',
				'type' => 'text',
				'attributes' => array(
						'id' => 'regtbl_date_created_end',
						'title' => 'Enter an end date to limit data to',
						'placeholder' => 'to date'
				),
				'options' => array(
						'label' => 'To date',
				),
		));

		$objForm->add(array(
				'name' => 'regtbl_source',
				'type' => 'select',
				'attributes' => array(
						'id' => 'regtbl_source',
						'title' => 'Limit data to a specific source',
						'placeholder' => 'Select a source to use as filter'
				),
				'options' => array(
						'label' => 'Source',
						'empty_option' => '--select--',
						'value_options' => $arr_sources,
				),
		));

		$objForm->add(array(
				'name' => 'regtbl_ref',
				'type' => 'select',
				'attributes' => array(
						'id' => 'regtbl_ref',
						'title' => 'Limit data to a specific reference',
						'placeholder' => 'Select a reference to use as filter'
				),
				'options' => array(
						'label' => 'Reference',
						'empty_option' => '--select--',
						'value_options' => $arr_references,
				),
		));

		$objForm->add(array(
				'name' => 'regtbl_status',
				'type' => 'select',
				'attributes' => array(
						'id' => 'regtbl_status',
						'title' => 'Limit data to a specific status',
						'placeholder' => 'Select a status to use as filter'
				),
				'options' => array(
						'label' => 'Contact Status',
						'empty_option' => '--select--',
						'value_options' => $arr_statuses,
				),
		));

		$objForm->add(array(
				'name' => 'regtbl_user',
				'type' => 'select',
				'attributes' => array(
						'id' => 'regtbl_user',
						'title' => 'Limit data to a specific user',
						'placeholder' => 'Select a user to use as filter'
				),
				'options' => array(
						'label' => 'User',
						'empty_option' => '--select--',
						'value_options' => $arr_users
				),
		));

		return $objForm;
	}//end function

	/**
	 * Create form to update Contact System Fields
	 * @return \Zend\Form\Form
	 */
	public function getContactSystemFieldsForm()
	{
		//create form
		$form = new \Zend\Form\Form();
		$form->setAttribute("id", 'system-fields-form');
		$form->add(array(
				"name" => "source_dropdown",
				"type" => "select",
				"attributes" => array(
						"id" => "source_dropdown",
						"title" => "Set from existing Sources",
				),
				"options" => array(
						"label" => "Select Source",
// 						"empty_option" => "--select--",
						"value_options" => array(
								"test",
						),
				),
		));

		$form->add(array(
				"name" => "source",
				"type" => "text",
				"attributes" => array(
						"id" => "source",
				),
				"options" => array(
						"label" => "Source",
				),
		));

		$form->add(array(
				"name" => "reference_dropdown",
				"type" => "select",
				"attributes" => array(
						"id" => "reference_dropdown",
						"title" => "Set from existing References",
				),
				"options" => array(
						"label" => "Select Reference",
// 						"empty_option" => "--select--",
						"value_options" => array(
								"test"
						),
				),
		));

		$form->add(array(
				"name" => "reference",
				"type" => "text",
				"attributes" => array(
						"id" => "reference",
				),
				"options" => array(
						"label" => "Reference",
				),
		));

		$form->add(array(
				"name" => "user_id",
				"type" => "select",
				"attributes" => array(
						"id" => "user_id",
						"required" => "required",
				),
				"options" => array(
						"label" => "User",
						"value_options" => array(

						),
				),
		));

		$form->add(array(
				"type" => "submit",
				"name" => "submit",
				"attributes" => array(
						"value" => "Submit",
				),
		));


		/**
		 * Populate Dropdowns
		 */
		//users
		$objUsers = $this->getFrontUsersModel()->fetchUsers();

		$arr_users = array();
		foreach ($objUsers as $objUser)
		{
			if (!is_numeric($objUser->id))
			{
				continue;
			}//end if

			if ($objUser->active != 1)
			{
				continue;
			}//end if

			$arr_users[$objUser->id] = $objUser->uname;
		}//end foreach
		$form->get("user_id")->setValueOptions($arr_users);

		//sources
		$objSources = $this->getFrontContactsSystemFieldsModel()->fetchDistinctContactSources();
		$arr_sources = array();

		foreach ($objSources as $objSource)
		{
			$arr_sources[$objSource->source] = $objSource->source;
		}//end foreach
		$form->get("source_dropdown")->setValueOptions($arr_sources);

		//references
		$objReferences = $this->getFrontContactsSystemFieldsModel()->fetchDistinctContactReferences();
		$arr_references = array();

		foreach ($objReferences as $objRefence)
		{
			$arr_references[$objRefence->reference] = $objRefence->reference;
		}//end foreach

		$form->get("reference_dropdown")->setValueOptions($arr_references);

		return $form;
	}//end function

	/**
	 * Load a list of contacts from a profile
	 * @param array $arr_where - Optional
	 * @return object
	 */
	public function fetchContacts($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts");

		//convert array to array object for events to manipulate where required
		if (is_array($arr_where))
		{
			$arr_where = new ArrayObject($arr_where);
		}//end if

		if (isset($arr_where["fid"]))
		{
			unset($arr_where["fid"]);
		}//end if

		//limit results to 20 where not specified otherwise
		if (!isset($arr_where['qp_limit']))
		{
			$arr_where['qp_limit'] = 20;
		}//end if

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . '.pre', $this, array('objApiRequest' => $objApiRequest, 'arr_where' => $arr_where));

		//execute
		$objContacts = $objApiRequest->performGETRequest($arr_where)->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('objContacts' => $objContacts, 'objApiRequest' => $objApiRequest, 'arr_where' => $arr_where));

		return $objContacts->data;
	}//end function

	/**
	 * Request a full list of contacts
	 * This is saved to a file in the background and cached for 30 minutes
	 * This function bypasses the normal api request model and makes a direct request
	 * @param string $action
	 */
	public function fetchContactsStream($action = '')
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . '.pre', $this, array());

		/**
		 * Set user details for request
		 */
		//load user session data
		$objUserSession = FrontUserSession::isLoggedIn();

		//set file path
		$path = './data/cache/cache_streams/' . str_replace('-', '', $objUserSession->profile->profile_identifier);
		if (!is_dir($path))
		{
			mkdir($path, 0755, TRUE);
		}//end if

		$csv_file = $path . '/' . $objUserSession->profile->profile_identifier . '-contacts.csv';
		$csv_metadata_file = $path . '/' . $objUserSession->profile->profile_identifier . '-contacts.csv.metadata';
		$arr_return = array(
				'source_data_path' => $csv_file,
				'source_metadata' => $csv_metadata_file,
		);

		switch ($action)
		{
			case 'delete':
				@unlink($csv_file);
				@unlink($csv_metadata_file);
				break;
		}//end switch

		//check if data file exists
		if (is_file($csv_file) && is_file($csv_metadata_file))
		{
			//check if file has expired
			$arr = unserialize(file_get_contents($csv_metadata_file));
			if (!is_array($arr))
			{
				@unlink($csv_file);
				@unlink($csv_metadata_file);
			}//end if

			if (time() > $arr['expires'])
			{
				@unlink($csv_file);
				@unlink($csv_metadata_file);
			} else {
				return $arr_return;
			}//end if
		}//end if

		//check if this is a user or site call
		if ($this->api_pword == "" || !$this->api_pword)
		{
			//try to extract from session
			if (is_object($objUserSession))
			{
				$this->api_pword = $objUserSession->pword;
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
					$this->api_user = $key;
				} else {
					//try to extract from session
					$this->api_user = $objUserSession->uname;
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
					$this->api_key = $this->getServiceLocator()->get("FrontCore\Models\FrontCoreSecurityModel")->decodeValue($objUserSession->api_key);
				} else {
					//try to extract from session
					$this->api_key = $objUserSession->api_key;
				}//end if
			}//end if
		}//end if
		require("./config/helpers/ob1.php");
		$arr_set_headers = array();
		foreach ($arr_headers as $k => $v)
		{
			$arr_set_headers[] = "$k: $v";
		}//end foreach

		//load config
		$arr_config = $this->getServiceLocator()->get('config')['profile_config'];

		//build the url
		$arr_fields = array(
			'reg_id',
			'reg_id_encoded',
			'fname',
			'sname',
			'comm_destinations_email',
			'source',
			'reference',
			'datetime_created',
			'datetime_updated',
			'registration_status_status',
			'registration_status_colour',
			'user_uname',
			'user_sname'
		);
		$url = $arr_config['api_request_location'] . '/api/contacts?qp_limit=all&qp_stream_output_csv=1&qp_disable_hypermedia=1&qp_export_fields=' . implode(',', $arr_fields);

		/**
		 * We use curl, its just easier
		 */
		set_time_limit(0);
		$fp = fopen($csv_file, 'w');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_set_headers);
		$data = curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		//set metadata
		file_put_contents($csv_metadata_file, serialize(array('expires' => time() + (60 * 60))));

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array());

		//return file paths
		return $arr_return;
	}//end function

	/**
	 * Request details about a specific contact within a profile
	 * @param mixed $id
	 * @return \FrontContacts\Entities\FrontContactsContactEntity
	 */
	public function fetchContact($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts");

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . '.pre', $this, array('objApiRequest' => $objApiRequest, 'arr_where' => $arr_where));

		//execute
		$objContact = $objApiRequest->performGETRequest(array("id" => $id))->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('objContact' => $objContact, 'objApiRequest' => $objApiRequest));

		//create contact entity
		$objContact = $this->createContactEntity($objContact->data);

		//deal with work and home number fields
		if ($objContact->get("work_num") != "")
		{
			$objContact->set("work_num", $objContact->get("work_code") . $objContact->get("work_num"));
		}//end if

		if ($objContact->get("tel_num") != "")
		{
			$objContact->set("tel_num", $objContact->get("tel_code") . $objContact->get("tel_num"));
		}//end if

		//deal with comm destination fields
		$objContact->set("email", $objContact->get("comm_destinations_email"));
		$objContact->set("cell_num", $objContact->get("comm_destinations_cell_num"));
		$objContact->set("fax_num", $objContact->get("comm_destinations_fax_num"));

		return $objContact;
	}//end function

	/**
	 * Requst contact statistics
	 * @param int $id
	 * @param array $arr_params
	 */
	public function fetchContactStatistics($id, array $arr_params)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts");

		//execute
		$arr_params['id'] = $id;
		$objResult = $objApiRequest->performGETRequest($arr_params)->getBody();
		return $objResult->data;
	}//end function

	/**
	 * Create a contact within a profile
	 * @trigger : createContact.pre, createContact.post
	 * @param array $arr_data
	 * @param string $form_id - Optional
	 * @return \FrontContacts\Entities\FrontContactsContactEntity
	 */
	public function createContact($arr_data, $form_id = "")
	{
		//create contact entity
		$objContact = $this->createContactEntity($arr_data);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", array("objContact" => $objContact));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts" . "?fid=$form_id");

		//execute, create the contact
		$objContact = $objApiRequest->performPOSTRequest($objContact->getArrayCopy())->getBody();

		//recreate the contact entity
		$objContact = $this->createContactEntity($objContact->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", array("objContact" => $objContact));

		return $objContact;
	}//end function

	/**
	 * Update a contact within a profile
	 * @trigger : updateContact.pre, updateContact.post
	 * @param FrontContactsContactEntity $objContact
	 * @param string $form_id - Optional
	 * @return \FrontContacts\Entities\FrontContactsContactEntity
	 */
	public function updateContact(FrontContactsContactEntity $objContact, $form_id = "")
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", array("objContact" => $objContact));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		if ($form_id != "")
		{
			$objApiRequest->setApiAction($objContact->getHyperMedia("edit-contact")->url . "?fid=$form_id");
		} else {
			$objApiRequest->setApiAction($objContact->getHyperMedia("edit-contact")->url);
		}//end if

		$objApiRequest->setApiModule(NULL);

		//execute
		$objContact = $objApiRequest->performPUTRequest($objContact->getArrayCopy())->getBody();

		//recreate contact entity
		$objContact = $this->createContactEntity($objContact->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", array("objContact" => $objContact));

		return $objContact;
	}//end function

	/**
	 * Unsubscribe a contact
	 * @param FrontContactsContactEntity $objContact
	 * @param array $arr_data - Optional
	 * @return unknown
	 */
	public function unsubscribeContact(FrontContactsContactEntity $objContact, $arr_data = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/" . $objContact->get('reg_id_encoded') . "/unsubscribe");

		//@TODO, for now, unsubscribe contact from all channels
		$arr_data['comm_via_id_all'] = 1;

		//request data
		$objContactChannels = $objApiRequest->performPUTRequest($arr_data)->getBody()->data;
var_dump($objContactChannels); exit;
		return $objContactChannels;
	}//end function

	/**
	 * Delete a contact from a profile
	 * @trigger: deleteContact.pre, deleteContact.post
	 * @param mixed $id
	 * @return \FrontContacts\Entities\FrontContactsContactEntity
	 */
	public function deleteContact($id)
	{
		//load contact
		$objContact = $this->fetchContact($id);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", array("objContact" => $objContact));

		//delete the contact
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objContact->getHyperMedia("delete-contact")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objApiRequest->performDELETERequest(array())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", array("objContact" => $objContact));

		return $objContact;
	}//end function

	/**
	 * Load Comments for a contact
	 * @param mixed $contact_id
	 */
	public function fetchContactComments($contact_id, $arr_params = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/comments");

		if (isset($arr_params['cid']))
		{
			unset($arr_params['cid']);
		}//end if

		//excute the request
		$objContactComments = $objApiRequest->performGETRequest($arr_params)->getBody();
		return $objContactComments->data;
	}//end function

	/**
	 * Load Contact Comm History data
	 * @param mixed $contact_id
	 * @param array $arr_params - Optional
	 */
	public function fetchContactCommHistory($contact_id, $arr_params = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/comm-history");

		//excute the request
		$objContactCommData = $objApiRequest->performGETRequest($arr_params)->getBody();
		return $objContactCommData->data;
	}//end function

	/**
	 * Create a new comment for a contact
	 * @param mixed $contact_id
	 * @param array $arr_data
	 * @return stdClass
	 */
	public function createContactComment($contact_id, $arr_data)
	{
		//load the contact
		$objContact = $this->fetchContact($contact_id);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("arr_data" => $arr_data, "objContact" => $objContact));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//create the comment
		//setup the object and specify the action
		$objApiRequest->setApiAction($objContact->getHyperMedia("create-contact-comment")->url);
		$objApiRequest->setApiModule(NULL);

		$objResult = $objApiRequest->performPOSTRequest($arr_data)->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("arr_data" => $arr_data, "objContact" => $objContact));

		return $objResult->data;
	}//end function

	/**
	 * Remove a comment for a contact
	 * This only flags a comment as deleted and keeps the record in the background
	 * @param integer $contact_id
	 * @param integer $comment_id
	 */
	public function deleteContactComment($contact_id, $comment_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/comments/$comment_id");
		$objResult = $objApiRequest->performDELETERequest(array());
		return $objResult->data;
	}//end function

	/**
	 * Construct a form based on data received from the API
	 * @param object $objFormData
	 * @return \FrontCore\Forms\FrontCoreSystemFormBase
	 */
	protected function constructForm($objFormData)
	{
		//convert data to array
		$arr_form_data = \Zend\Json\Json::decode(\Zend\Json\Json::encode($objFormData), TRUE);

		//create form object
		$objForm = new FrontCoreSystemFormBase();
		$objForm->setAttribute("id", "form");
		$objForm->setAttributes($arr_form_data["attributes"]);
		$objForm->setOptions($arr_form_data["options"]);

		//add form elements
		foreach ($arr_form_data["arr_fields"] as $arr_element)
		{
			if ($arr_element["attributes"]["type"] == "")
			{
				$arr_element["attributes"]["type"] = "text";
			}//end if
			$arr_element["type"] = $arr_element["attributes"]["type"];
			$arr_element["name"] = $arr_element["attributes"]["name"];

			if ($arr_element["attributes"]["required"] === TRUE || strtolower($arr_element["attributes"]["required"]) == "true" || strtolower($arr_element["attributes"]["required"]) == "required")
			{
				$arr_element["required"] = TRUE;
			} else {
				$arr_element["required"] = FALSE;
				$arr_element["allow_empty"] = TRUE;
			}//end if
			$arr_element["validators"] = array();
			$arr_element["filters"] = array();

			$objForm->add($arr_element);
		}//end foreach

		$objForm->get("submit")->setValue("Submit");
		return $objForm;
	}//end function

	/**
	 * Create a contact entity
	 * @param object $objData
	 * @return \FrontContacts\Entities\FrontContactsContactEntity
	 */
	private function createContactEntity($objData)
	{
		$entity_contact = $this->getServiceLocator()->get("FrontContacts\Entities\FrontContactsContactEntity");

		//populate the data
		$entity_contact->set($objData);

		return $entity_contact;
	}//end function

	/**
	 * Create an instance of the Front User Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUsersModel
	 */
	private function getFrontUsersModel()
	{
		if (!$this->model_front_users)
		{
			$this->model_front_users = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersModel");
		}//end if

		return $this->model_front_users;
	}//end function

	/**
	 * Create an instance of the Front Contacts System Fields Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsSystemFieldsModel
	 */
	private function getFrontContactsSystemFieldsModel()
	{
		if (!$this->model_front_contact_system_fields)
		{
			$this->model_front_contact_system_fields = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsSystemFieldsModel");
		}//end if

		return $this->model_front_contact_system_fields;
	}//end function

	/**
	 * Create an instance of the Statuses Model
	 * @return \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private function getFrontContactStatusesModel()
	{
		if (!$this->model_front_statuses)
		{
			$this->model_front_statuses = $this->getServiceLocator()->get('FrontStatuses\Models\FrontContactStatusesModel');
		}//end if

		return $this->model_front_statuses;
	}//end function
}//end class
