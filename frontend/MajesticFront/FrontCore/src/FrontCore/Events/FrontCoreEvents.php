<?php
namespace FrontCore\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCoreEvents extends AbstractCoreAdapter
{
	
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
}//end class
