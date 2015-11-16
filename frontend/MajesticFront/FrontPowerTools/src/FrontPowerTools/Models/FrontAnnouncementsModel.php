<?php
namespace FrontPowerTools\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontPowerTools\Entities\FrontAnnouncementEntity;

class FrontAnnouncementsModel extends AbstractCoreAdapter
{
	/**
	 * load the Admin form for the Power Tools from the Core System Forms
	 * @return \Zend\Form\Form
	 */
	public function getAnnouncementForm()
	{
		$objForm = $this-> getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm("Core\Forms\SystemForms\PowerTools\AnnouncementForm");
		
		
		return $objForm;
	}//end function
	
	/**
	 * Load a list of announcements availible
	 * @param arry $arr_where - Optional
	 * @return StdClass
	 */
	public function fetchAnnouncements($arr_where = NULL)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("powertools/admin/announcements");
		
		//@TODO apply the search vars
		if (is_array($arr_where))
		{
			//....
		}//end if
		
		$objAnnouncements = $objApiRequest->performGETRequest()->getBody();
		
		return $objAnnouncements->data;
	}//end function
	
	
	/**
	 * Request details about a specific Announcement
	 * @param mixed $id
	 * @return \FrontPowerTools\Entities\FrontAnnouncementEntity
	 */
	public function fetchAnnouncement($id)
	{
throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : This module is not available currently", 500);
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("powertools/admin/announcements/$id");
		
		//execute
		$objAnnouncement = $objApiRequest->performGETRequest(array("id" => $id))->getBody();
		
		//create powertool entity
		$objAnnouncement = $this->createAnnouncementEntity($objAnnouncement->data);
		
		return $objAnnouncement;
	}//end function
	
	/**
	 * Create an Announcement
	 * @trigger createAnnouncement.pre, createAnnouncement.post
	 * @param array $arr_data
	 * @return \FrontPowerTools\Entities\FrontAnnouncementEntity
	 */
	public function createAnnouncement($arr_data)
	{
throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : This module is not available currently", 500);
		//create the announcement entity
		$objAnnouncement = $this->createAnnouncementEntity($arr_data);
		
		//trigger pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objAnnouncement" => $objAnnouncement));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the request
		$objApiRequest->setApiAction("powertools/admin/announcements");
		
		//execute
		$objAnnouncement = $objApiRequest->performPOSTRequest($objAnnouncement->getArrayCopy())->getBody();
		
		//recreate the announcement entity
		$objAnnouncement = $this->createAnnouncementEntity($objAnnouncement);
		
		//trigger post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objAnnouncement" => $objAnnouncement));
		
		return $objAnnouncement;
	}//end function
	
	/**
	 * Update an Announcement
	 * @trigger updateAnnouncement.pre, updateAnnouncement.post
	 * @param FrontAnnouncement $objPowerTool
	 * @return \FrontPowerTools\Entities\FrontAnnouncementEntity
	 */
	public function updateAnnouncement(FrontAnnouncementEntity $objAnnouncement)
	{
throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : This module is not available currently", 500);
		//trigger the pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objAnnouncement" => $objAnnouncement));
		
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the Object and specify the action
		$objApiRequest->setApiAction($objAnnouncement->getHyperMedia("edit-announcement")->url);
		$objApiRequest->setApiModule(NULL);
		
		//execute
		$objAnnouncement = $objApiRequest->performPUTRequest($objAnnouncement->getArrayCopy())->getBody();
		
		//recreate the announcement entity
		$objAnnouncement = $this->createAnnouncementEntity($objAnnouncement->data);
		
		//trigger the post event
		$result =  $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objAnnouncement" => $objAnnouncement));
		
		return $objAnnouncement;
	}//end function
	/**
	 * Delete an Existing Announcement
	 * @param mixed $id
	 * @return \FrontPowerTools\Entities\FrontAnnouncementEntity
	 */
	public function deleteAnnouncement($id)
	{
throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : This module is not available currently", 500);
		//create the announcement entity
		$objAnnouncement = $this->fetchAnnouncement($id);
		
		//trigger the pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objAnnouncement" => $objAnnouncement));
		
		//Create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify an action
		$objApiRequest->setApiAction($objAnnouncement->getHyperMedia("delete-announcement")->url);
		$objApiRequest->setApiModule(NULL);
		
		//execute
		$objAnnouncement = $objApiRequest->performDELETERequest(array())->getBody();
		
		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objAnnouncement" => $objAnnouncement));
		
		return $objAnnouncement;
	}//end function
	
	/**
	 * Create an Announcement entity object
	 * @param mixed $objData
	 * @return \FrontPowerTools\Entities\FrontAnnouncementEntity
	 */
	private function createAnnouncementEntity($objData)
	{
		$entity_announcement = $this->getServiceLocator()->get("FrontPowerTools\Entities\FrontAnnouncementEntity");
		
		//populate the data
		$entity_announcement->set($objData);
		
		return $entity_announcement;
	}//end function
}//end class
