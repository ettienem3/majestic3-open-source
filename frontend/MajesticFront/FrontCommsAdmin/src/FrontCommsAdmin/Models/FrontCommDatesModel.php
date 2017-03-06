<?php
namespace FrontCommsAdmin\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontCommsAdmin\Entities\FrontCommDateEntity;


class FrontCommDatesModel extends AbstractCoreAdapter
{
	/**
	 * Load the Admin for theComm dates from the Core System Forms
	 * @return \Zend\Form\Form
	 */
	public function getCommDatesForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
					->getSystemForm("Core\Forms\SystemForms\CommsAdmin\CommDatesForm");
		
		return $objForm;
	}//end function
	
	/**
	 * Load a list of the Comms
	 * @param array $arr_where - Optional
	 * @return Object
	 */
	public function fetchCommDates($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/dates");
		
		//execute
		$objCommDates = $objApiRequest->performGETRequest($arr_where)->getBody();
		
		return $objCommDates->data;
	}//end function
	
	/**
	 * Request details about a specific Comm Date
	 * @param mixed $id
	 * @return \FrontCommsAdmin\Entities\CommDateEntity
	 */
	public function fetchCommDate($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/dates/$id");
		
		//execute
		$objCommDate = $objApiRequest->performGETRequest(array("id" => $id))->getBody();
		
		//create the commdate entiy
		$entity_commdate = $this->createCommDateEntity($objCommDate->data);
		
		return $entity_commdate;
	}//end function
	
	
	/**
	 * Create a Commdate entry
	 * @trigger createCommdate.pre, createCommDate.post
	 * @param array $arr_data
	 * @return \FrontCommsAdmin\Entities\CommDateEntity
	 */
	public function createCommDate($arr_data)
	{
		//create commdate entity
		$objCommDate = $this->createCommDateEntity($arr_data);
		
		//trigger the pre envent
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objCommDate" => $objCommDate));
		
		//create the request Object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/dates");
		
		//execute
		$objCommDate = $objApiRequest->performPOSTRequest($objCommDate->getArrayCopy())->getBody();
		
		//recreate the commdate entity
		$objCommDate = $this->createCommDateEntity($objCommDate->data);
		
		//trigger the post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objCommDate" => $objCommDate));
		
		return $objCommDate;
	}//end functiom
	
	/**
	 * Update a CommDate
	 * @trigger updateCommdate.pre, udateCommdate.post
	 * @param CommDateEntity $objCommDate
	 * @return \FrontCommsAdmin\Entities\FrontCommDateEntity
	 */
	public function updateCommDate(FrontCommDateEntity $objCommDate)
	{
		//trigger the pre event.
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objCommDate" => $objCommDate));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction('comms/admin/dates/' . $objCommDate->get('id'));
		
		//execute 
		$objCommDate = $objApiRequest->performPUTRequest($objCommDate->getArrayCopy())->getBody();
		
		//recreate the commdate entity
		$objCommDate = $this->createCommDateEntity($objCommDate->data);
		
		//trigger the post event.
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objCommDate" => $objCommDate));
		
		return $objCommDate;
		
	}//end function
	
	/**
	 * Delete an Commdate entry
	 * @param mixed $id
	 * @return Ambigous <\Zend\Json\mixed, mixed, NULL, \Zend\Json\$_tokenValue, multitype:, multitype:Ambigous <\Zend\Json\mixed, \Zend\Json\$_tokenValue, NULL> , stdClass, multitype:Ambigous <\Zend\Json\mixed, \Zend\Json\$_tokenValue, multitype:, multitype:Ambigous <\Zend\Json\mixed, \Zend\Json\$_tokenValue, NULL> , NULL> >
	 */
	public function deleteCommDate($id)
	{
		
		$objCommDate = $this->fetchCommDate($id);
		
		//trigger the pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objCommDate" => $objCommDate));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction($objCommDate->getHyperMedia("delete-comm-date")->url);
		$objApiRequest->setApiModule(NULL);
		
		//execute
		$objCommDate = $objApiRequest->performDELETERequest(array())->getBody();
		
		//trigger the post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objCommDate" => $objCommDate));
		
		return $objCommDate;
		
	}//end function
	
	/**
	 * Create a Comm date entity object
	 * @param object $objData
	 * @return \FrontCommsAdmin\Entities\FrontCommDateEntity
	 */
	private function createCommDateEntity($objData)
	{
		$entity_commdate = $this->getServiceLocator()->get("FrontCommsAdmin\Entities\FrontCommDateEntity");
		
		//populate the data
		$entity_commdate->set($objData);
		
		return $entity_commdate;
	}//end function
		
}//end class
