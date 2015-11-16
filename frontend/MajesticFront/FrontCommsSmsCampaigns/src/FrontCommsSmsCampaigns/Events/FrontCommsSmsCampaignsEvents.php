<?php
namespace FrontCommsSmsCampaigns\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCommsSmsCampaignsEvents extends AbstractCoreAdapter
{
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();

	}//end function

}//end class
