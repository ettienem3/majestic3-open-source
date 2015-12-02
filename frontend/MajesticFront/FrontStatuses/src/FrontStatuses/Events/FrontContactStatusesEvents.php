<?php
namespace FrontStatuses\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontContactStatusesEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	}//end function
}//end class
