<?php
namespace FrontPanels\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontPanelsEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
