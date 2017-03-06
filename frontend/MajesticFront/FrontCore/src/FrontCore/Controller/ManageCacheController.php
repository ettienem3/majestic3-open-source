<?php
namespace FrontCore\Controller;

use FrontCore\Adapters\AbstractCoreActionController;
use Zend\View\Model\JsonModel;

class ManageCacheController extends AbstractCoreActionController
{
	public function flushProfileCacheAction()
	{
		try {
			$this->getEventManager()->trigger("clearProfileCacheEvent", this, array());
			$objResponse = new JsonModel(array(
				'error' => 0,
				'response' => 'Cache cleared',
			));
		} catch (\Exception $e) {
			$objResponse = new JsonModel(array(
					'error' => 1,
					'response' => 'A failure occurred and Cache was not cleared',
					'raw_response' => $e->getMessage(),
			));
		}//end catch
		
		return $objResponse;
	}//end function
}//end class