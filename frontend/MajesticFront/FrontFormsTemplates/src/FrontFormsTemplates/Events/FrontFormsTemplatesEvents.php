<?php
namespace FrontFormsTemplates\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontFormsTemplatesEvents extends AbstractCoreAdapter
{
	
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();
	

	}//end function
	
}//end class
