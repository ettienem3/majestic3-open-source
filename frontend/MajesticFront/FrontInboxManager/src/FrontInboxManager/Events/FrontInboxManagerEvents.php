<?php
namespace FrontInboxManager\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontInboxManagerEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
