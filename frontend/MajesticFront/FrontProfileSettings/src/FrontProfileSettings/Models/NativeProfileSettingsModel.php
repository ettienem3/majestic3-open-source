<?php
namespace FrontProfileSettings\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontProfileSettings\Forms\NativeProfileSettingsForm;
use FrontUserLogin\Models\FrontUserSession;
use FrontProfileSettings\Entities\FrontProfileNativeSettingsProfileEntity;
use FrontCore\Factories\FrontCoreServiceProviderFactory;

class NativeProfileSettingsModel extends AbstractCoreAdapter
{
	private $path;
	
	/**
	 * Container for the Cache Model
	 * @var \FrontCore\Caches\FrontCachesRedis
	 */
	private $objCache;
	
	/**
	 * Set cache identifying key
	 * @var string
	 */
	private $cache_key = "native-profile-settings";
	
	/**
	 * Load Native Profile Settings Form
	 * @return \FrontProfileSettings\Forms\NativeProfileSettingsForm
	 */
	public function getProfileSettingsForm()
	{
		$objForm = new NativeProfileSettingsForm();
		
		return $objForm;
	}//end function
	
	/**
	 * Load profile settings
	 * @return \FrontProfileSettings\Entities\FrontProfileNativeSettingsProfileEntity
	 */
	public function fetchProfileSettings()
	{
		$objData = $this->readFile();
		$objProfileSettings = $this->getServiceLocator()->get("FrontProfileSettings\Entities\FrontProfileNativeSettingsProfileEntity");
		if (!$objData)
		{
			$objProfileSettings->set(array());
			return $objProfileSettings;
		}//end if
		
		$objProfileSettings->set($objData);
		return $objProfileSettings;
	}//end function
	
	public function getFilesPath()
	{
		$r = $this->setPath(FALSE);
		if (!$r)
		{
			return $r;	
		}//end if
		
		$path = $this->path . "/assets";
		if (!is_dir($path))
		{
			mkdir($path, 0755, TRUE);
		}//end if
		
		return $path;
	}//end function
	
	/**
	 * Static access proxy function
	 * @return \FrontProfileSettings\Entities\FrontProfileNativeSettingsProfileEntity
	 */
	public static function readProfileSettings()
	{
		$sm = FrontCoreServiceProviderFactory::getInstance();
		$model = $sm->get("FrontProfileSettings\Models\NativeProfileSettingsModel");
		return $model->fetchProfileSettings();
	}//end function
	
	/**
	 * Save native profile settings
	 * @param FrontProfileNativeSettingsProfileEntity $objData
	 */
	public function saveProfileSettings(FrontProfileNativeSettingsProfileEntity $objData)
	{
		$this->saveFile($objData->getArrayCopy());
	}//end function
	
	/**
	 * Retrieve data from disk
	 * @return mixed
	 */
	private function readFile()
	{
		$r = $this->setPath();
		if (!$r)
		{
			return $r;	
		}//end if
		
		//check cache
		if ($this->objCache->readCacheItem($this->cache_key))
		{
			return $this->objCache->readCacheItem($this->cache_key);	
		}//end if
		
		//encode data
		$objData = json_decode(file_get_contents($this->path));
		
		//save to cache
		$this->objCache->setCacheItem($this->cache_key, $objData, array("ttl" => (5 * 60)));
		return $objData;
	}//end function
	
	/**
	 * Save data to disk
	 * @param unknown $arr_data
	 */
	private function saveFile($arr_data)
	{
		$r = $this->setPath();
		if (!$r)
		{
			return $r;	
		}//end if
		
		$arr_settings = (array) $this->readFile();
		$arr_settings = array_merge($arr_settings, $arr_data);
		
		$objData = json_encode($arr_settings, JSON_FORCE_OBJECT); 
		file_put_contents($this->path, $objData);
		
		//save to cache
		$this->objCache->setCacheItem($this->cache_key, $objData, array("ttl" => (5 * 60)));
	}//end function
	
	/**
	 * Set path to config file
	 */
	private function setPath($include_file = TRUE)
	{
		$objUser = FrontUserSession::isLoggedIn();
		if (!$objUser)
		{
			return FALSE;
			header("location:/user/login"); //@TODO this redirect breaks external entities, is the return causing security gaps?
			exit;
		}//end if

		//set cache
		$this->objCache = $this->getServiceLocator()->get("FrontCore\Caches\FrontCachesRedis");
		
		//retrieve profile identifier
		$profile_id = $objUser->profile->profile_identifier;
		
		//check if path exists
		if (!is_dir("./data/profiles/settings/$profile_id"))
		{
			mkdir("./data/profiles/settings/$profile_id", 0755, TRUE);
		}//end if
		
		if ($include_file === TRUE)
		{
			$this->path = "./data/profiles/settings/$profile_id/" . $profile_id . ".dat";
			
			if (!is_file($this->path))
			{
				file_put_contents($this->path, json_encode(array(), JSON_FORCE_OBJECT));
			}//end if
		} else {
			$this->path = "./data/profiles/settings/$profile_id";
		}//end if
	}//end function
}//end class