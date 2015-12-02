<?php
namespace MajesticExternalContacts\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class MajesticExternalContactsEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
