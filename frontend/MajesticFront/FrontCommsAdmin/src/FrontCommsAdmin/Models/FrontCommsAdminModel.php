<?php
namespace FrontCommsAdmin\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontCommsAdmin\Entities\FrontCommAdminEntity;

class FrontCommsAdminModel extends AbstractCoreAdapter
{
	/**
	 * Load the Admin for the Comms Admin From the Core System Form
	 * @return \Zend\Form\Form
	 */
	public function getCommsAdminForm($arr_data = array())
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
							->getSystemForm("Core\Forms\SystemForms\CommsAdmin\CommsAdminForm", NULL, array("filters" => 1, "validators" => 0));

		/**
		 * Populate send after comm dropdown
		 */
		if (is_numeric($arr_data["journey_id"]))
		{
			$objComms = $this->fetchCommsAdmin(array("journey_id" => $arr_data["journey_id"]));
			$arr_comms = $objForm->get("send_after")->getOption("value_options");
			foreach($objComms as $objComm)
			{
				if (!is_numeric($objComm->id))
				{
					continue;
				}//end if

				if (isset($arr_data["comm_id"]) && $arr_data["comm_id"] == $objComm->id)
				{
					continue;
				}//end if

				$arr_comms[$objComm->comm_num] = $objComm->comm_num . " : " . $objComm->subject;
			}//end foreach
			$objForm->get("send_after")->setValueOptions($arr_comms);
		}//end if

		return $objForm;
	}//end function

	/**
	 * Load a list of the diffent Communication Statuses defined in the system
	 * @return object
	 */
	public function fetchCommunicationStatusList()
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/status-list");

		//execute
		$objComms = $objApiRequest->performGETRequest(array())->getBody();
		return $objComms->data;
	}//end function

	/**
	 * Load a list of Comms
	 * @param array $arr_where - Optional
	 * @return StdClass
 	 */
	public function fetchCommsAdmin($arr_where = NULL)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comms");

		//execute
		$objComms = $objApiRequest->performGETRequest($arr_where)->getBody();
		return $objComms->data;
	}//end function

	/**
	 * Request details about a specific Comm Admin
	 * @param mixed $id
	 * @return \FrontCommsAdmin\Entities\FrontCommsAdminEntity
	 */
	public function fecthCommAdmin($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comms/$id");

		//execute
		$objCommAdmin = $objApiRequest->performGETRequest(array("id" => $id))->getBody();

		//create the commadmin entity
		$objCommAdmin = $this->createCommAdminEntity($objCommAdmin->data);

		return $objCommAdmin;
	}//end function

	/**
	 * Create a CommAdmin entry
	 * @trigger createCommsAdmin.pre, createCommsAdmin.post
	 * @param array $arr_data
	 * @return \FrontCommsAdmin\Entity\FrontCommAdminEntity
	 */
	public function createCommsAdmin($arr_data)
	{
		//create commsadmin entity
		$objCommAdmin = $this->createCommAdminEntity($arr_data);

		//trigger the pre event
		$this->getEventManager()->trigger(__FUNCTION__ . "pre", $this, array("objCommAdmin" => $objCommAdmin));

		//create the request Object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comms");

		//extract data
		$arr_data = $objCommAdmin->getDataForSubmit();

		//execute
		$objCommAdmin  = $objApiRequest->performPOSTRequest($arr_data)->getBody();

		//recreate the commdate entity
		$objCommAdmin = $this->createCommAdminEntity($objCommAdmin->data);

		//trigger the post event
		$this->getEventManager()->trigger(__FUNCTION__ . "post", $this, array("objCommAdmin" => $objCommAdmin));

		return $objCommAdmin;
	}//end function

	/**
	 * Update the CommsAdmin
	 * @trigger updateCommsAdmin.pre, updateCommsAdmin.post
	 * @param CommsAdminEntity $objCommAdmin
	 * @return \FrontCommsAdmin\Entities\FrontCommAdminEntity
	 */
	public function updateCommAdmin(FrontCommAdminEntity $objCommAdmin)
	{
		//trigger the pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . "pre", $this, array("objCommAdmin" => $objCommAdmin));

		//create the request object
		$objApirequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApirequest->setApiAction($objCommAdmin->getHyperMedia("edit-comm")->url);
		$objApirequest->setApiModule(NULL);

		//extract data
		$arr_data = $objCommAdmin->getDataForSubmit();

		//execute
		$objCommAdmin = $objApirequest->performPUTRequest($arr_data)->getBody();

		//recreate the commAdmin entity
		$objCommAdmin = $this->createCommAdminEntity($objCommAdmin->data);

		//trigger the post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objCommAdmin" => $objCommAdmin));

		return $objCommAdmin;
	}//end function

	public function updateCommStatus($id)
	{
		//create the request object
		$objApirequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApirequest->setApiAction("comms/admin/comm-status/$id");

		//execute
		//1 = dummy value
		$objCommAdmin = $objApirequest->performPUTRequest(array("status" => 1))->getBody();
	}//end function

	/**
	 * Delete a CommAdmin entry
	 * @param mixed $id
	 *
	 */
	public function deleteCommsAdmin($id)
	{
		$objCommAdmin = $this->fecthCommAdmin($id);

		//trigger the pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objCommAdmin" => $objCommAdmin));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction($objCommAdmin->getHyperMedia("delete-comm")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objCommAdmin = $objApiRequest->performDELETERequest(array())->getBody();

		//trigger the post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objCommAdmin" => $objCommAdmin));

		return $objCommAdmin;
	}//end function

	/**
	 * Create the Comm Admin entity object
	 * @param object $objData
	 * @return \FrontCommsAdmin\Entities\FrontCommsAdminEntity
	 */
	private function createCommAdminEntity($objData)
	{
		$entity_commsadmin = $this->getServiceLocator()->get("FrontCommsAdmin\Entities\FrontCommAdminEntity");

		//populate the data
		$entity_commsadmin->set($objData);

		return $entity_commsadmin;
	}//end function
}//end class
