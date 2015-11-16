<?php
namespace FrontUsers\Storage;

use FrontCore\Adapters\AbstractCoreAdapter;

class UserFileSystemStorage extends AbstractCoreAdapter
{
	/**
	 * Path to file location
	 * @var string
	 */
	private $path;
	
	/**
	 * Filename specific to user containing data
	 * @var string
	 */
	private $file;
	
	/**
	 * Container for the User Data Object acted upon
	 * @var 
	 */
	private $objUserData;
	
	/**
	 * Configure Storage path
	 * @param unknown $path
	 */
	public function setPath($path)
	{
		$this->path = $path;
	}//end function
	
	/**
	 * Set User Data Object
	 * @param stdClass $objUserData
	 */
	public function setUserData($objUserData)
	{
		$this->objUserData = $objUserData;
	}//end function
	
	public function readData($key = FALSE)
	{
		$this->prepareLocation();
		
		//load data from file
		$objUserStorageData = json_decode(file_get_contents($this->path . "/" . $this->file));

		if (!$key)
		{
			return $objUserStorageData;
		}//end if
		
		if (isset($objUserStorageData->$key))
		{
			return $objUserStorageData->$key;
		}//end if
		
		return FALSE;
	}//end function
	
	public function saveData($key, $data)
	{
		$this->prepareLocation();
	
		//load data from file
		$objUserStorageData = json_decode(file_get_contents($this->path . "/" . $this->file));
		$objUserStorageData->$key = $data;
		file_put_contents($this->path . "/" . $this->file, json_encode($objUserStorageData, JSON_FORCE_OBJECT));
	}//end function
	
	public function clearData()
	{
		$this->prepareLocation();
		file_put_contents($this->path . "/" . $this->file, json_encode(array(), JSON_FORCE_OBJECT));
	}//end function
	
	private function prepareLocation()
	{
		if (!$this->file)
		{
			if (!$this->path)
			{
				throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : User data could not be saved. Path is not set", 500);
			}//end if
			
			//create folder if it does not exist yet
			if (!is_dir($this->path))
			{
				mkdir($this->path, 0755, TRUE);
			}//end if
			
			//set filename
			$this->file = md5($this->objUserData->id . $this->objUserData->site_id . $this->objUserData->uname . $this->objUserData->email) . ".dat";
			
			//check if file exists already
			if (!is_file($this->path . "/" . $this->file))
			{
				file_put_contents($this->path . "/" . $this->file, json_encode(array(), JSON_FORCE_OBJECT));
			}//end if
		}//end if
		
		return $this->file;
	}//end function
}//end class