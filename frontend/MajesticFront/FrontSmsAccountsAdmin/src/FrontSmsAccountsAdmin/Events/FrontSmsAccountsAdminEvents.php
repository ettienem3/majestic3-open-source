<?php
namespace FrontSmsAccountsAdmin\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontSmsAccountsAdminEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
