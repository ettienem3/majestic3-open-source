<?php
namespace FrontSalesFunnels\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontSalesFunnelsEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
