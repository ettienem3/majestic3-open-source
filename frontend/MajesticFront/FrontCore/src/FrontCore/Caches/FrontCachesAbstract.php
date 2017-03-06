<?php
namespace FrontCore\Caches;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontUserLogin\Models\FrontUserSession;

abstract class FrontCachesAbstract extends AbstractCoreAdapter
{
	/**
	 *
	 * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
	 */
	protected $storageFactory;

	/**
	 * Return a list of all cached items
	 * @return boolean
	 */
	public function listCachedItems($arr_params = array("Front*"))
	{
		//check if caching is enabled
		$arr_config = $this->getServiceLocator()->get("config");
		if ($arr_config["front_end_application_config"]["cache_enabled"] == FALSE)
		{
			return FALSE;
		}//end if

		$objAdapter = $this->storageFactory->getCapabilities()->getAdapter();
		
		//redis
		if ($objAdapter instanceof \Zend\Cache\Storage\Adapter\Redis)
		{
			$objRedis = $objAdapter->getOptions();
			//get resource id
			$id = $objRedis->getResourceId();
			
			//create direct instance of redis client
			$redis = $objRedis->getResourceManager()->getResource($id);
			$arr_keys = $redis->keys("*");
			return $arr_keys;
		}//end if
		
		//file system
		if ($objAdapter instanceof \Zend\Cache\Storage\Adapter\Filesystem)
		{
			return $objAdapter;
		}//end if
	}//end function
	
	/**
	 * Read an item from the cache
	 * @param string $key
	 * @param string $default_value
	 * @return string|\Zend\Cache\Storage\Adapter\mixed
	 */
	public function readCacheItem($key, $default_value = FALSE)
	{
		//check if caching is enabled
		$arr_config = $this->getServiceLocator()->get("config");
		if ($arr_config["front_end_application_config"]["cache_enabled"] == FALSE)
		{
			return FALSE;
		}//end if

		//adjust key
		$key = $this->setIdentifier($key);
		
		try {
			if (!$this->storageFactory->getItem($key))
			{
				return $default_value;
			}//end if
	
			return $this->storageFactory->getItem($key);
		} catch (\Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}//end catch
		
		return $default_value;
	}//end function

	/**
	 * Save item to cache
	 * @param string $key
	 * @param mixed $value
	 * @param array $arr_options - Optional
	 * Options: ttl => Overwrite configured ttl with set value. Reverts to configured ttl once completed
	 */
	public function setCacheItem($key, $value, $arr_options = array())
	{
		//check if caching is enabled
		$arr_config = $this->getServiceLocator()->get("config");
		if ($arr_config["front_end_application_config"]["cache_enabled"] == FALSE)
		{
			return FALSE;
		}//end if

		//adjust key
		$key = $this->setIdentifier($key);
		
		try {
			/**
			 * Overwrite ttl
			 */
			if (is_numeric($arr_options["ttl"]))
			{
				$old_ttl = $this->storageFactory->getOptions()->getTtl();
				$this->storageFactory->getOptions()->setTtl((int) $arr_options["ttl"]);
				$this->storageFactory->setItem($key, $value);
	
				//reset ttl
				$this->storageFactory->getOptions()->setTtl($old_ttl);
				return;
			}//end if
	
			$this->storageFactory->setItem($key, $value);
		} catch (\Exception $e) {
			trigger_error($e->getMessage(), E_USER_WARNING);
		}//end catch
	}//end function
	
	/**
	 * Remove an item from the cache
	 * @param string $key
	 * @param boolean $set_identifier - Used in some cases to prepend values to key. Defualts to TRUE
	 * @return boolean
	 */
	public function clearItem($key, $set_identifier = TRUE)
	{
		//check if caching is enabled
		$arr_config = $this->getServiceLocator()->get("config");
		if ($arr_config["front_end_application_config"]["cache_enabled"] == FALSE)
		{
			return FALSE;
		}//end if
		
		//adjust key
		if ($set_identifier === TRUE)
		{
			$key = $this->setIdentifier($key);
		}//end if

		$r = $this->storageFactory->removeItem($key);
		return $r;
	}//end function
	
	/**
	 * Set unique identifier so profiles do not overwrite each other
	 * @param string $key
	 * @return string
	 */
	private function setIdentifier($key)
	{
		$objUser = FrontUserSession::isLoggedIn();
		
		if (is_object($objUser) && isset($objUser->profile->profile_identifier) && $objUser->profile->profile_identifier != "")
		{
			return $objUser->profile->profile_identifier . "-" . $key;
		}//end if
		
		if (is_object($objUser) && is_numeric($objUser->site_id))
		{
			return $objUser->site_id . "-" . $key;	
		}//end if
		
		return $key;
	}//end function
}//end function