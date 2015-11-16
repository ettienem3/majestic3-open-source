<?php
namespace FrontContacts\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontContactsEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
