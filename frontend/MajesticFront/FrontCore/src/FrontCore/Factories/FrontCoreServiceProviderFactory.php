<?php
namespace FrontCore\Factories;

use Zend\ServiceManager\ServiceManager;

/**
 * Creates and stores an instance of the Service Manager Instance in runtime
 * This is used within classes whom are unable to extend the AbstractCoreAdapter class
 * @author ettiene
 *
 */
class FrontCoreServiceProviderFactory
{
	/**
	 * Container for the Service Manager Instance
	 * @var ServiceManager
	 */
	private static $serviceManager;

	/**
	 * @throw ServiceLocatorFactory\NullServiceLocatorException
	 * @return Zend\ServiceManager\ServiceManager
	 */
	public static function getInstance()
	{
		if(null === self::$serviceManager)
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . ' : ServiceLocator is not set', 500);
		}//end if

		return self::$serviceManager;
	}//end function

	/**
	 * @param ServiceManager
	 */
	public static function setInstance(ServiceManager $sm)
	{
		self::$serviceManager = $sm;
	}//end function
}//end class