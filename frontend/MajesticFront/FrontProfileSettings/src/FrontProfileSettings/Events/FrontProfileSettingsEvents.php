<?php
namespace FrontProfileSettings\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontProfileSettingsEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
