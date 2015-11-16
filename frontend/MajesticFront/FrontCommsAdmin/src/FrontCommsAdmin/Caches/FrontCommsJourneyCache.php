<?php
namespace FrontCommsAdmin\Caches;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCommsJourneyCache extends AbstractCoreAdapter
{
	/**
	 * Instance of the Core Cahce Manager
	 * @var \FrontCore\Caches\FrontCachesRedis
	 */
	private $storageFactory;
	
	public function readCacheItem($key, $default_value = FALSE)
	{
		return $this->getCacheManager()->readCacheItem($key, $default_value);
	}//end function
	
	public function setCacheItem($key, $value, $arr_options = array())
	{
		return $this->getCacheManager()->setCacheItem($key, $value, $arr_options);
	}//end function
	
	public function clearCacheItem($key)
	{
		return $this->getCacheManager()->clearItem($key);
	}//end function
	
	/**
	 * Create an instance of the Core Redis Cache Manager using the Service Manager
	 * @return \FrontCore\Caches\FrontCachesRedis
	 */
	private function getCacheManager()
	{
		if (!$this->storageFactory)
		{
			$this->storageFactory = $this->getServiceLocator()->get("FrontCore\Caches\FrontCachesRedis");
		}//end if
		
		return $this->storageFactory;
	}//end function
}//end class