<?php
namespace FrontSalesFunnels\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontContacts\Entities\FrontContactsContactEntity;
use FrontSalesFunnels\Entities\FrontSalesFunnelContactSalesFunnelEntity;

class FrontSalesFunnelsModel extends AbstractCoreAdapter
{
	/**
	 * Load a collection of Sales Funnels
	 * @param array $arr_where
	 * @return \FrontSalesFunnels\Entities\FrontSalesFunnelContactSalesFunnelEntity
	 */
	public function fetchSalesFunnels(array $arr_where)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("sales-funnels/admin");

		//execute
		$objResult = $objApiRequest->performGETRequest($arr_where)->getBody();

		//create sales funnel entities
		$arr = array();
		foreach ($objResult->data as $obj)
		{
			if (!is_numeric($obj->id))
			{
				continue;
			}//end if

			$objSalesFunnel = $this->createSalesFunnelEntity($obj);
			$arr[] = $objSalesFunnel;
		}//end foreach

		return (object) $arr;
	}//end function

	/**
	 * Load a specific sales funnel
	 * @param FrontContactsContactEntity $objContact
	 * @param mixed $id
	 * @return \FrontSalesFunnels\Entities\FrontSalesFunnelContactSalesFunnelEntity
	 */
	public function fetchSalesFunnel(FrontContactsContactEntity $objContact, $id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("sales-funnels/admin/$id?contact_id=" . $objContact->get("id"));

		//execute
		$objResult = $objApiRequest->performGETRequest(array())->getBody();

		//create sales funnel entity
		$objSalesFunnel = $this->createSalesFunnelEntity($objResult->data);

		return $objSalesFunnel;
	}//end function

	/**
	 * Create a new sales funnel
	 * @trigger : createSalesFunnel.pre, createSalesFunnel.post
	 * @param FrontContactsContactEntity $objContact
	 * @param array $arr_data
	 * @return \FrontSalesFunnels\Entities\FrontSalesFunnelContactSalesFunnelEntity
	 */
	public function createSalesFunnel(FrontContactsContactEntity $objContact, array $arr_data)
	{
		//create entity
		$objSalesFunnel = $this->createSalesFunnelEntity($arr_data);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSalesFunnel" => $objSalesFunnel));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("sales-funnels/admin?contact_id=" . $objContact->get("id") . "&fid=" . $arr_data["fk_form_id"]);

		//execute
		$objResult = $objApiRequest->performPOSTRequest($objSalesFunnel->getArrayCopy())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSalesFunnel" => $objSalesFunnel, 'objResult' => $objResult));

		return $objResult->data;
	}//end function

	/**
	 * Update a sales funnel
	 * @trigger : editSalesFunnel.pre, editSalesFunnel.post
	 * @param FrontContactsContactEntity $objContact
	 * @param FrontSalesFunnelContactSalesFunnelEntity $objSalesFunnel
	 * @return \FrontSalesFunnels\Entities\FrontSalesFunnelContactSalesFunnelEntity
	 */
	public function editSalesFunnel(FrontContactsContactEntity $objContact, FrontSalesFunnelContactSalesFunnelEntity $objSalesFunnel)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSalesFunnel" => $objSalesFunnel));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("sales-funnels/admin/" . $objSalesFunnel->get("id") . "?contact_id=" . $objContact->get("id"));

		//execute
		$objResult = $objApiRequest->performPUTRequest($objSalesFunnel->getArrayCopy())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSalesFunnel" => $objSalesFunnel));

		return $objSalesFunnel;
	}//end function

	/**
	 * Delete a sales funnel
	 * @trigger : deleteSalesFunnel.pre, deleteSalesFunnel.post
	 * @param FrontContactsContactEntity $objContact
	 * @param FrontSalesFunnelContactSalesFunnelEntity $objSalesFunnel
	 * @return \FrontSalesFunnels\Entities\FrontSalesFunnelContactSalesFunnelEntity
	 */
	public function deleteSalesFunnel(FrontContactsContactEntity $objContact, FrontSalesFunnelContactSalesFunnelEntity $objSalesFunnel)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSalesFunnel" => $objSalesFunnel));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("sales-funnels/admin/" . $objSalesFunnel->get("id") . "?contact_id=" . $objContact->get("id"));

		//execute
		$objResult = $objApiRequest->performDELETERequest(array())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objSalesFunnel" => $objSalesFunnel));

		return $objSalesFunnel;
	}//end function

	/**
	 * Create an instance of Sales Funnel entity
	 * @param mixed $objData
	 * @return \FrontSalesFunnels\Entities\FrontSalesFunnelContactSalesFunnelEntity
	 */
	private function createSalesFunnelEntity($objData)
	{
		$objSalesFunnel = $this->getServiceLocator()->get("FrontSalesFunnels\Entities\FrontSalesFunnelContactSalesFunnelEntity");

		//populate data
		$objSalesFunnel->set($objData);
		return $objSalesFunnel;
	}//end function
}//end class
