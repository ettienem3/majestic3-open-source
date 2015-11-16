<?php
namespace FrontCommsAdmin\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCommDatesEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
