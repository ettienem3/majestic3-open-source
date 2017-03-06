<?php
namespace FrontContacts\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontContacts\Entities\FrontContactsContactEntity;

class FrontContactsLinkedContactsModel extends AbstractCoreAdapter
{
	/**
	 * Load contacts linked to a specific contact
	 * @param unknown $objContact
	 * @param array $arr_params
	 */
	public function fetchLinkedContacts(FrontContactsContactEntity $objContact, $arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/" . $objContact->get('id') . '/linked-contacts');

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . '.pre', $this, array('objApiRequest' => $objApiRequest, 'arr_where' => $arr_where));

		//execute
		$objContacts = $objApiRequest->performGETRequest()->getBody()->data;

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('objContact' => $objContact, 'objApiRequest' => $objApiRequest));

		$arr_contacts = array();
		foreach ($objContacts as $objData)
		{
			$arr = (array) $objData;
			$arr['fname'] = $objData->registrations_fname;
			$arr['sname'] = $objData->registrations_sname;
			$arr_contacts[] = $arr;
		}//end foreach

		return (object) $arr_contacts;
	}//end function

	/**
	 * Load contacts this contact is linked to
	 * @param FrontContactsContactEntity $objContact
	 * @param array $arr_where
	 */
	public function fetchLinkedToContacts(FrontContactsContactEntity $objContact, $arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts/data/" . $objContact->get('id') . '/linked-to-contacts');

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . '.pre', $this, array('objApiRequest' => $objApiRequest, 'arr_where' => $arr_where));

		//execute
		$objContacts = $objApiRequest->performGETRequest()->getBody()->data;

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . '.post', $this, array('objContact' => $objContact, 'objApiRequest' => $objApiRequest));

		$arr_contacts = array();
		foreach ($objContacts as $objData)
		{
			$arr = (array) $objData;
			$arr['fname'] = $objData->registrations_fname;
			$arr['sname'] = $objData->registrations_sname;
			$arr_contacts[] = $arr;
		}//end foreach

		return (object) $arr_contacts;
	}//end function
}//end class