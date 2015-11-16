<?php
namespace FrontFormAdmin\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontFormAdminEvents extends AbstractCoreAdapter
{

	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();


	}//end function

}//end class
