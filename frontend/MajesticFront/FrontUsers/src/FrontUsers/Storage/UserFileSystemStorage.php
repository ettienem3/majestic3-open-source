<?php
namespace FrontUsers\Storage;

use FrontCore\Adapters\AbstractCoreAdapter;

class UserFileSystemStorage extends AbstractCoreAdapter
{
	/**
	 * Container for the profile identifier
	 * @var string
	 */
	private $profile_identifier;
	
	/**
	 * Container for the profile user id
	 * @var int
	 */
	private $profile_user_id;
	
	/**
	 * String identifier for user
	 * @var string
	 */
	private $user_local_identifier;
	
	/**
	 * Container for the folder path containing files
	 * @var string
	 */
	private $path = './data/cache/users';
	
	/**
	 * Container for the set file path
	 * @var string $file
	 */
	private $file;
	
	/**
	 * Configure Storage path
	 * @param unknown $path
	 */
	public function setPath($path)
	{
		$this->path = $path;
	}//end function
	
	/**
	 * Initialise the class to read and write data
	 * @param object $objUserSession
	 */
	public function setUserData($objUserSession)
	{
		//set profile identifier
		$this->profile_identifier = $objUserSession->profile->profile_identifier;
	
		//set user id
		$this->profile_user_id = $objUserSession->id;
	
		$s = $this->profile_user_id . '-' . $this->profile_identifier;
		$this->user_local_identifier = md5($s);
		
		//set file location
		$this->prepareLocation();
	}//end function
	
	/**
	 * Check if class is initialized
	 * @throws \Exception
	 */
	private function isInitialized()
	{
		if (!$this->user_local_identifier)
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Class is not initialized", 500);
		}//end if
	}//end function
	
	/**
	 * Save user native preferences
	 */
	public function setUserNativePreferences($key, $value = FALSE)
	{
		$this->isInitialized();
	
		//load save data
		$objData = $this->readUserNativePreferences();
		if (!$objData)
		{
			$objData = new \stdClass();
		}//end if
	
		if (is_object($key))
		{
			foreach($key as $k => $v)
			{
				$objData->$k = $v;
			}//end foreach
		} else {
			$objData->$key = $value;
		}//end if
	
		$data = json_encode($objData);
		file_put_contents($this->path . '/' . $this->file, $data);
	}//end function
	
	/**
	 * Read user native preferences
	 * @return \FrontUsers\Entities\FrontUserNativePreferencesEntity | mixed
	 */
	public function readUserNativePreferences($key = FALSE)
	{
		$this->isInitialized();
	
		//load data
		$data = file_get_contents($this->path . '/' . $this->file);
		$objData = json_decode($data);

		if (!$objData)
		{
			return FALSE;
		}//end if
	
		if ($key !== FALSE)
		{
			return $objData->$key;
		} else {
			return $objData;
		}//end if
	}//end function
	
	/**
	 * Clear user native preferences
	 */
	public function clearUserNativePreferences()
	{
		$this->isInitialized();
		unlink($this->path . "/" . $this->file);
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
			$this->file = md5($this->user_local_identifier) . ".dat";
			
			//check if file exists already
			if (!is_file($this->path . "/" . $this->file))
			{
				file_put_contents($this->path . "/" . $this->file, json_encode(array(), JSON_FORCE_OBJECT));
			}//end if
		}//end if
		
		return $this->file;
	}//end function
}//end class