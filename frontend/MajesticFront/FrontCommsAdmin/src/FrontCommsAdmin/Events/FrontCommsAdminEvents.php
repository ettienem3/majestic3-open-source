<?php
namespace FrontCommsAdmin\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCommsAdminEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
