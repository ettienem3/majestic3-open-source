<?php
namespace MajesticExternalForms\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use Zend\Cache\Storage\Adapter\AbstractAdapter;

class MajesticExternalFormsCacheModel extends AbstractCoreAdapter
{
	private $storageFactory;

	public function __construct(AbstractAdapter $storageFactory)
	{
		$this->storageFactory = $storageFactory;
	}//end function

	/**
	 * Retrieve a form from cache
	 * @param string $key
	 */
	public function readFormCache($key)
	{
		$result = $this->storageFactory->getItem("external-form-" . md5($_SERVER["HTTP_HOST"]) . "-" . $key);
		return $result;
	}//end function

	/**
	 * Create cache item for form
	 * @param string $key
	 * @param object $objData
	 */
	public function setFormCache($key, $objData)
	{
		$this->storageFactory->setItem("external-form-" . md5($_SERVER["HTTP_HOST"]) . "-" . $key, $objData);
	}//end function

	/**
	 * Clear cache for a specific form
	 * @param string $key
	 */
	public function clearFormCache($key)
	{
		$this->storageFactory->removeItem("external-form-" . md5($_SERVER["HTTP_HOST"]) . "-" . $key);
	}//end function

	/**
	 * Clear entire forms cache
	 */
	public function clearEntireFormCache()
	{
		$this->storageFactory->flush();
	}//end function
}//end class