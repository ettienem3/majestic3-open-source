<?php
namespace FrontCommsAdmin\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontCommsAdmin\Entities\FrontCommEmbeddedImageEntity;

class FrontCommsAdminCommEmbeddedImagesModel extends AbstractCoreAdapter
{
	/**
	 * Load Communication Embedded Image Form
	 * @return \Zend\Form\Form
	 */
	public function getCommEmbeddedImageForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm("Core\Forms\SystemForms\CommsAdmin\CommEmbeddedImageForm", NULL, array("filters" => 0, "validators" => 0));
		
		return $objForm;
	}//end function
	
	/**
	 * Load a specific communication embedded image
	 * @param mixed $comm_id
	 * @param mixed $id
	 * @return \FrontCommsAdmin\Entities\FrontCommEmbeddedImageEntity
	 */
	public function fetchCommEmbeddedImage($comm_id, $id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comm-embedded-images/$id?comm_id=$comm_id");
		
		//execute
		$objResult = $objApiRequest->performGETRequest(array())->getBody();
		
		//create entity
		$objImage = $this->getServiceLocator()->get("FrontCommsAdmin\Entities\FrontCommEmbeddedImageEntity");
		$objImage->set($objResult->data);
		
		return $objImage;
	}//end function
	
	/**
	 * Load communication embedded images
	 * @param mixed $comm_id
	 * @return \FrontCommsAdmin\Entities\FrontCommEmbeddedImageEntity
	 */
	public function fetchCommEmbeddedImages($comm_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comm-embedded-images?comm_id=$comm_id");
		
		//execute
		$objResult = $objApiRequest->performGETRequest(array())->getBody();
		
		foreach ($objResult->data as $key => $objTImage)
		{
			if ($objTImage->id == "")
			{
				continue;
			}//end if
			
			//create entity
			$objImage = $this->getServiceLocator()->get("FrontCommsAdmin\Entities\FrontCommEmbeddedImageEntity");
			$objImage->set($objTImage);
			$arr[] = $objImage;
		}//end foreach
		
		return (object) $arr;
	}//end function
	
	/**
	 * Create a communication embedded image
	 * @param mixed $comm_id
	 * @param array $arr_data
	 * @return \FrontCommsAdmin\Entities\FrontCommEmbeddedImageEntity
	 */
	public function createCommEmbeddedImage($comm_id, $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comm-embedded-images?comm_id=$comm_id");
		
		//execute
		$objResult = $objApiRequest->performPOSTRequest($arr_data)->getBody();
		
		//create entity
		$objImage = $this->getServiceLocator()->get("FrontCommsAdmin\Entities\FrontCommEmbeddedImageEntity");
		$objImage->set($objResult->data);
		
		return $objImage;
	}//end function
	
	/**
	 * Update a communication embedded image
	 * @param FrontCommEmbeddedImageEntity $objCommEmbeddedImage
	 * @return \FrontCommsAdmin\Entities\FrontCommEmbeddedImageEntity
	 */
	public function editCommEmbeddedImage(FrontCommEmbeddedImageEntity $objCommEmbeddedImage)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comm-embedded-images/" . $objCommEmbeddedImage->get("id") . "?comm_id=" . $objCommEmbeddedImage->get("comm_id"));
		
		//exceute the request
		$objResult = $objApiRequest->performPUTRequest($objCommEmbeddedImage->getArrayCopy())->getBody();
		$objCommEmbeddedImage->set($objResult->data);
		
		return $objCommEmbeddedImage;
	}//end function
	
	/**
	 * Delete a communication embedded image
	 * @param mixed $comm_id
	 * @param mixed $id
	 * @return stdClass
	 */
	public function deleteCommEmbeddedImage($comm_id, $id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/admin/comm-embedded-images/$id?comm_id=$comm_id");
		
		$objResult = $objApiRequest->performDELETERequest(array())->getBody();
		return $objResult->data;
	}//end function
}//end class