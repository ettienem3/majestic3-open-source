<?php
namespace FrontBehavioursConfig\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontBehavioursConfigEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
