<?php
namespace FrontUsers\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontUsersEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class

