<?php
namespace FrontCommsBulkSend\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCommsBulkSendEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
