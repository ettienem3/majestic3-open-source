<?php
namespace FrontContacts\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontContacts\Entities\FrontContactsContactEntity;
use FrontCore\Forms\FrontCoreSystemFormBase;

class FrontContactsModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Users Model
	 * @var \FrontUsers\Models\FrontUsersModel
	 */
	private $model_front_users;

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

		unset($arr_where["fid"]);

		//execute
		$objContacts = $objApiRequest->performGETRequest($arr_where)->getBody();

		return $objContacts->data;
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

		//execute
		$objContact = $objApiRequest->performGETRequest(array("id" => $id))->getBody();

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
	public function fetchContactComments($contact_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/comments");

		//excute the request
		$objContactComments = $objApiRequest->performGETRequest(array())->getBody();

		return $objContactComments->data;
	}//end function

	/**
	 * Load Contact Comm History data
	 * @param mixed $contact_id
	 */
	public function fetchContactCommHistory($contact_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/$contact_id/comm-history?debug_display_errors=1");

		//excute the request
		$objContactCommData = $objApiRequest->performGETRequest(array())->getBody();

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
}//end class
