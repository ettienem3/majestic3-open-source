<?php
namespace FrontCore\Caches;

use Zend\Cache\Storage\Adapter\AbstractAdapter;

class FrontCachesRedis extends FrontCachesAbstract
{
	public function __construct(AbstractAdapter $storageFactory)
	{
		$this->storageFactory = $storageFactory;
	}//end function
}//end class