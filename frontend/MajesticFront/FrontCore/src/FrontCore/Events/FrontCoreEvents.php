<?php
namespace FrontCore\Events;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontUserLogin\Models\FrontUserSession;

class FrontCoreEvents extends AbstractCoreAdapter
{
	/**
	 * 
	 * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
	 */
	private $cache;
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	
		/**
		 * Log events being triggered
		 */
//  		$eventManager->attach(
//  					"*",
//  					"*",
//  					function ($event) use ($serviceManager) {
//  						$serviceManager->get(get_class($this))->logEvent($event);
//  					}, //end function
//  					1000 //highest priority to catch all events taking place
//  					);

		$eventManager->attach(
			"*",
			"clearProfileCacheEvent",
			function ($event) use ($serviceManager) {
				return $serviceManager->get(__CLASS__)->flushProfileCachedItems($event);
			}//end function
		);
		
	}//end function
	
	private function logEvent($event)
	{
		//gather information
		$event_name = $event->getName();
		$event_target = get_class($event->getTarget());
	
		//exclude all native Zend events for now
		if (substr($event_target, 0, 5) == "Zend\\")
		{
			return;
		}//end if
	
		//exclude some Zend events with other namespaces
		$arr_exclude_events = array(
// 				"dispatch",
// 				"collected",
		);
		if (in_array($event_name, $arr_exclude_events))
		{
			return;
		}//end if
	
		$event_params = json_encode($event->getParams());
	
		//create log message
		$message = PHP_EOL . "************" . PHP_EOL . "Event name : $event_name" . PHP_EOL . "Target : $event_target" . PHP_EOL . "Params : " . print_r($event_params, TRUE) . PHP_EOL . "**********" . PHP_EOL;
	
		if (!is_file("./data/events.log"))
		{
			//create the file
			file_put_contents("./data/events.log", "");
		}//end if
	
		file_put_contents("./data/events.log", $message, FILE_APPEND);
	}//end function
	
	private function flushProfileCachedItems($event)
	{
		try {
			//set profile identifier
			$profile_identifier = FrontUserSession::isLoggedIn()->profile->profile_identifier;
			
			//get entries for this profile
			$objCacheManager = $this->setupProfileCache();
			$result = $objCacheManager->listCachedItems();

			if (is_object($result) && $result instanceof \Zend\Cache\Storage\Adapter\Filesystem)
			{
				//file system is being used, delete cache folder
				$folder = $result->getOptions()->getCacheDir(); //general folder
				
				//remove files from folder
				$files = new \RecursiveIteratorIterator(
							new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
							\RecursiveIteratorIterator::CHILD_FIRST
						);
				
				foreach ($files as $fileinfo) 
				{
					$todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
					$todo($fileinfo->getRealPath());
				}//end foreach
			}//end if
			
			if (is_array($result))
			{
				//redis is used, loop over identifiable items and remove
				foreach ($result as $key)
				{
					if (strrpos($key, "FrontEndCache:$profile_identifier") !== FALSE)
					{				
						$key = str_replace('FrontEndCache:', '', $key);
						$objCacheManager->clearItem($key, FALSE);
					}//end if
				}//end foreach
			}//end if
			
			//send request to core to clear cache on that side
			//create the request object
			$objApiRequest = $this->getApiRequestModel();
			
			//setup the object and specify the action
			$objApiRequest->setApiAction("profiles/admin");
			
			//execute
			$objData = $objApiRequest->performGETRequest(array('callback' => 'flushProfileCache'))->getBody();
		} catch (\Exception $e) {
			trigger_error("Unable to clear cache, failed with '" . $e->getMessage(). E_USER_ERROR);
		}//end catch		
	}//end function
	
	/**
	 * Create system form cache mechanism
	 * @throws \Exception
	 * @return \Zend\Cache\Storage\Adapter\AbstractAdapter
	 */
	private function setupProfileCache()
	{
		if (!$this->cache)
		{
			$cache = $this->getServiceLocator()->get("FrontCore\Caches\Cache");
			$this->cache = $cache;
		}//end if
	
		return $this->cache;
	}//end function
}//end class
