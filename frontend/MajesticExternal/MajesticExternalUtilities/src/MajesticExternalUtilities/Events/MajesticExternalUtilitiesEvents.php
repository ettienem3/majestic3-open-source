<?php
namespace MajesticExternalUtilities\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class MajesticExternalUtilitiesEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
