<?php
namespace FrontProfileFileManager\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontProfileFileManagerEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
