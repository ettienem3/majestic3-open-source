<?php
namespace FrontUserLogin\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontUserLoginEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	
	}//end function
	
}//end class
