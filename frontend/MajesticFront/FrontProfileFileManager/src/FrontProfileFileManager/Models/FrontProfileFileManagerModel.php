<?php
namespace FrontProfileFileManager\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontProfileFileManagerModel extends AbstractCoreAdapter
{
	/**
	 * Load uploads form
	 * @return \FrontProfileFileManager\Forms\FrontProfileFileManagerForm
	 */
	public function getFileUploadForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontProfileFileManager\Forms\FrontProfileFileManagerForm");
		return $objForm;
	}//end function
	
	public function fetchFiles($mode = "", $arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		if ($mode != "")
		{
			//setup the object and specify action
			$objApiRequest->setApiAction("profile/file-manager/0?mode=" . strtolower($mode));
		} else {
			//setup the object and specify action
			$objApiRequest->setApiAction("profile/file-manager/0");
		}//end if
		
		//execute
		$objFiles = $objApiRequest->performGETRequest($arr_where)->getBody();
		return $objFiles->data;
	}//end function
	
	public function uploadFile($mode, $arr_data)
	{
		//check if data is valid. Invalid files are dropped and not converted
		if ($arr_data["data"] == "")
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Uploaded file is not valid. Operation aborted", 500);	
		}//end if
		
		//set data fields for api validation
		$arr_data["url"] = "";
		if (!isset($arr_data["data"]))
		{
			$arr_data["data"] = "";
		}//end if

		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify action
		$objApiRequest->setApiAction("profile/file-manager/0?mode=$mode");
		
		$objResult = $objApiRequest->performPOSTRequest($arr_data);
	}//end function
	
	public function toggleFileStatus($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify action
		$objApiRequest->setApiAction("profile/file-manager/" . $id);
		
		$objResult = $objApiRequest->performPUTRequest(array('id' => $id));
	}//end function
	
	public function deleteFile($mode, $location)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify action
		$objApiRequest->setApiAction("profile/file-manager/0?mode=$mode");
		
		$objResult = $objApiRequest->performDELETERequest(array("file" => $location));
	}//end function
}//end class
