<?php
namespace FrontCommsAdmin\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCommsAdminEvents extends AbstractCoreAdapter
{
	/**
	 * Container for the Profile Cache Manager (Default is Redis)
	 * @var \FrontCore\Caches\FrontCachesRedis $model_profile_cache_manager
	 */
	private $model_profile_cache_manager;
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
	private function angularJourneyControllerClearCache($event)
	{
		
	}//end function
	
	
	/**
	 * Create an instance of the Profile's cache manager
	 * @return \FrontCore\Caches\FrontCachesRedis
	 */
	private function getProfileCacheManager()
	{
		if (!$this->model_profile_cache_manager)
		{
			$this->model_profile_cache_manager = $this->getServiceLocator()->get('FrontCore\Caches\FrontCachesRedis');
		}//end if
	
		return $this->model_profile_cache_manager;
	}//end function
}//end class
