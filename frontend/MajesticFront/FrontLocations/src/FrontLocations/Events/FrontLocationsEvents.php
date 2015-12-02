<?php
namespace FrontLocations\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontLocationsEvents extends AbstractCoreAdapter
{

	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();


	}//end function

}//end class
