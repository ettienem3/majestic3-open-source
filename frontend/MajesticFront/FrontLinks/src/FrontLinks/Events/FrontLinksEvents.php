<?php
namespace FrontLinks\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontLinksEvents extends AbstractCoreAdapter
{

	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();


	}//end function

}//end class
