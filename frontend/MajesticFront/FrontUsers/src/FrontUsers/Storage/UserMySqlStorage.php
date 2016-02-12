<?php
namespace FrontUsers\Storage;

use FrontCore\Adapters\AbstractCoreAdapter;

class UserMySqlStorage extends AbstractCoreAdapter
{
	/**
	 * Container for the user preferences table
	 * @var \FrontUsers\Tables\UserNativePreferencesTable
	 */
	private $table_user_native_preferences;
	
	/**
	 * Container for the user cache settings table
	 * @var \FrontUsers\Tables\UserCacheSettingsTable
	 */
	private $table_user_cache_settings;
	
	/**
	 * Container for the crypto model
	 * @var \FrontCore\Models\Security\CryptoModel $model_core_crypto
	 */
	private $model_core_crypto;
	
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
		$objData = $this->getUserNativePreferencesTable()->get($this->user_local_identifier);
		if (!$objData)
		{
			$objData = new \stdClass();
		} else {
			$objData = unserialize($objData->get('data'));
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
		
		$data = serialize($objData);
		$this->getUserNativePreferencesTable()->save($this->user_local_identifier, $data);
	}//end function
	
	/**
	 * Read user native preferences
	 * @return \FrontUsers\Entities\FrontUserNativePreferencesEntity | mixed
	 */
	public function readUserNativePreferences($key = FALSE)
	{
		$this->isInitialized();
		
		//load data
		$objData = $this->getUserNativePreferencesTable()->get($this->user_local_identifier);

		if (!$objData)
		{
			return FALSE;
		} else {
			//decrypt data
			$d = $objData->get('data');
			$objData = unserialize($d);	
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
		$this->getUserNativePreferencesTable()->delete($this->user_local_identifier);
	}//end function
	
	/**
	 * Create an instance of the User Preferences Table
	 * @return \FrontUsers\Tables\UserNativePreferencesTable
	 */
	private function getUserNativePreferencesTable()
	{
		if (!$this->table_user_native_preferences)
		{
			$this->table_user_native_preferences = $this->getServiceLocator()->get('FrontUsers\Tables\UserNativePreferencesTable');
		}//end if
		
		return $this->table_user_native_preferences;
	}//end function
	
	/**
	 * Create an instance of the User Cache Settings Table
	 * @return \FrontUsers\Tables\UserCacheSettingsTable
	 */
	private function getUserCacheSettingsTable()
	{
		if (!$this->table_user_cache_settings)
		{
			$this->table_user_cache_settings = $this->getServiceLocator()->get('FrontUsers\Tables\UserCacheSettingsTable');
		}//end if
		
		return $this->table_user_cache_settings;
	}//end function
	
	/**
	 * Create an instance of the Core Crypto Model
	 * @return \FrontCore\Models\Security\CryptoModel
	 */
	private function getCryptoModel()
	{
		if (!$this->model_core_crypto)
		{
			$this->model_core_crypto = $this->getServiceLocator()->get('FrontCore\Models\Security\CryptoModel');
		}//end if
		
		return $this->model_core_crypto;
	}//end function
}//end class