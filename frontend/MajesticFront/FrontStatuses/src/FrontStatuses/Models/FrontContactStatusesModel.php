<?php
namespace FrontStatuses\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontStatuses\Entities\ContactStatusEntity;

class FrontContactStatusesModel extends AbstractCoreAdapter
{
	/**
	 *  Load the form for Contact Statuses from Core Systems Forms
	 * @return \Zend\Form\Form
	 */
	public function getContactStatusSystemForm()
	{
		$objContactStatus = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")->getSystemForm("Core\Forms\SystemForms\Statuses\RegistrationStatusForm");
		return $objContactStatus;
	} // end function


	/**
	 * Load existing Contact Status details.
	 * @param mixed $id - Mandatory
	 * @return \FrontCore\Entities\StatusEntity
	 */
	public function fetchContactStatus($id)
	{
		// Create APIRequestModel object
		$objApiRequest = $this->getApiRequestModel();

		// Setup the object and specify the action
		$objApiRequest->setApiAction("statuses/admin/contact-statuses/$id");

		// Execute
		$objContactStatus = $objApiRequest->performGETRequest(array("id" => $id))->getBody();

		// Create Contact Status entity
		$entity_contact_status = $this->createContactStatusEntity($objContactStatus->data);
		return $entity_contact_status;
	} // end function


	/**
	 * Load a list of Contact Statuses
	 * @param array $arr_where - Optional
	 * @return object
	 */
	public function fetchContactStatuses($arr_where = array())
	{
		// Create the APIRequestModel object
		$objApiRequest = $this->getApiRequestModel();

		// Setup the object and specify the action.
		$objApiRequest->setApiAction("statuses/admin/contact-statuses");

		// Execute
		$objContactStatuses = $objApiRequest->performGETRequest($arr_where)->getBody();
		return $objContactStatuses->data;
	} // end function



	/**
	 * Create a new Contact Status
	 * @trigger: createContactStatus.pre, createContactStatus.post
	 * @param array $arr_data - Mandatory
	 * @return \FrontCore\Entities\StatusEntity
	 */
	public function createContactStatus($arr_data)
	{
		// Create Contact Status entity
		$objContactStatus = $this->createContactStatusEntity($arr_data);

		// trigger .pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objContactStatus" => $objContactStatus));

		// Create APIRequest model object
		$objApiRequest = $this->getApiRequestModel();

		// Create APIRequest object and specify the action.
		$objApiRequest->setApiAction("statuses/admin/contact-statuses");

		// Execute
		$objContactStatus = $objApiRequest->performPOSTRequest($objContactStatus->getArrayCopy())->getBody();

		// Recreate Contact Status entity
		$objContactStatus = $this->createContactStatusEntity($objContactStatus);

		// trigger .post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objContactStatus" => $objContactStatus));
		return $objContactStatus;
	} //end function


	/**
	 * Update existing Contact Status
	 * @trigger : updateContactStatus.pre, updateContactStatus.post
	 * @param ContactStatusEntity $objContactStatus - Mandatory
	 * @return \FrontStatuses\Entities\ContactStatusEntity
	 */
	public function updateContactStatus(ContactStatusEntity $objContactStatus)
	{
		// trigger .pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objContactStatus" => $objContactStatus));

		// Create APIRequestModel object
		$objApiRequest = $this->getApiRequestModel();

		// Setup APIRequest object and specify action
		$objApiRequest->setApiAction($objContactStatus->getHyperMedia("edit-contact-status")->url);
		$objApiRequest->setApiModule(NULL);

		// Execute
		$objContactStatus = $objApiRequest->performPUTRequest($objContactStatus->getArrayCopy())->getBody();

		// Recreate Contact Status entity
		$objContactStatus = $this->createContactStatusEntity($objContactStatus->data);

		// trigger .post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objContactStatus" => $objContactStatus));
		return $objContactStatus;
	} // end function


	/**
	 * Delete existing Contact Status
	 * @param mixed $id - Mandatory
	 */
	public function deleteContactStatus($id)
	{
		// Create Contact Status entity
		$objContactStatus = $this->fetchContactStatus($id);

		// trigger pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objContactStatus" => $objContactStatus));

		// Create the APIRequestModel object
		$objApiRequest = $this->getApiRequestModel();

		// Setup APIRequest object and specify the action.
		$objApiRequest->setApiAction($objContactStatus->getHyperMedia("delete-contact-status")->url);
		$objApiRequest->setApiModule(NULL);

		// Execute
		$objContactStatus = $objApiRequest->performDELETERequest(array())->getBody();

		// trigger post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objContactStatus" => $objContactStatus));
		return $objContactStatus;
	} //end function


	/**
	 * Create Contact Status entity
	 * @param object $objData
	 * @return \FrontStatuses\Entities\ContactStatusEntity
	 */
	private function createContactStatusEntity($objData)
	{
		// Loads Contact Status entity using Service Manager.
		$entity_contact_status = $this->getServiceLocator()->get("FrontStatuses\Entities\ContactStatusEntity");

		// Populate Contact Status data
		$entity_contact_status->set($objData);

		return $entity_contact_status;
	} // end function
}//end class
