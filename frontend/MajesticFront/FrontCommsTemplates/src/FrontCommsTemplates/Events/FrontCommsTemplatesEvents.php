<?php
namespace FrontCommsTemplates\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCommsTemplatesEvents extends AbstractCoreAdapter
{
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	
	}//end function
}//end class
