<?php
/**
 * This class creates a generic adapter for loading interfaces required in most instances
 * Class that provides extended capabilities to models
 * It allows to connect to db and provides serviceLocater interaction.
 * Removes the need to inject dependecies from controllers directly, however, classes must be instantiated via the serviceLocater in order to work
 * @author ettiene
 *
 */
namespace FrontCore\Adapters;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;

use FrontCore\Models\ApiRequestModel;

abstract class AbstractCoreAdapter implements ServiceLocatorAwareInterface, EventManagerAwareInterface
{
	/**
	 * Service Locater Instance
	 * @var object
	 */
	protected $serviceLocater;

	/**
	 * Event Manager Instance
	 * @var object
	 */
	protected $events;

	/**
	 * Stores an instance of the ApiRequestModel.
	 * Loaded via the Service Manager
	 * @var \FrontCore\Models\ApiRequestModel
	 */
	protected $model_api_request;

	/**
	 * Flag to enable to delayed processing.
	 * Where set to TRUE, data submits will be processed via CLI
	 * @var bool
	 */
	protected $flag_delay_process_request;

	public function __construct()
	{

	}//end function

	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}//end function

	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}//end function

	public function getEventManager()
	{
        if (!$this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
	}//end function

	public function setEventManager(EventManagerInterface $events)
	{
        $events->setIdentifiers(array(
        	__NAMESPACE__,
            __CLASS__,
            get_class($this)
        ));
        $this->events = $events;
	}//end function

	/**
	 * Load the Api Request Model
	 * @return \FrontCore\Models\ApiRequestModel
	 */
	protected function getApiRequestModel()
	{
		if (!$this->model_api_request)
		{
			$this->model_api_request = $this->getServiceLocator()->get("FrontCore\Models\ApiRequestModel");
		}//end if

		return $this->model_api_request;
	}//end function

	/**
	 * Setter for the Api Request Model Instance
	 * @param ApiRequestModel $objApiRequestModel
	 */
	public function setApiRequestModel(ApiRequestModel $objApiRequestModel)
	{
		$this->model_api_request = $objApiRequestModel;
	}//end function

	/**
	 * Enable / Disable delayed processing
	 * @param string $flag
	 * @return bool. Returns false when module is not enabled
	 */
	public function setDelayedProcessingFlag($flag = FALSE)
	{
		if ($flag === TRUE)
		{
			//check if module is enabled
			$objModuleManager = $this->getServiceLocator()->get("ModuleManager");
			$arr_modules = $objModuleManager->getLoadedModules();

			if (!array_key_exists("FrontCLI", $arr_modules))
			{
				return FALSE;
			}//end if

			//check if this is a cli request, if it is disable cli requests to be performed since it could cause infinite loops
			if (php_sapi_name() == "cli")
			{
				return FALSE;
			}//end if
		}//end if

		return $this->flag_delay_process_request = $flag;
	}//end function

	/**
	 * Get delayed processing indicator flag
	 * @return boolean
	 */
	public function getDelayedProcessingFlag()
	{
		//check if module is enabled
		$objModuleManager = $this->getServiceLocator()->get("ModuleManager");
		$arr_modules = $objModuleManager->getLoadedModules();

		if (!array_key_exists("FrontCLI", $arr_modules))
		{
			return FALSE;
		}//end if

		//check if this is a cli request, if it is disable cli requests to be performed since it could cause infinite loops
		if (php_sapi_name() == "cli")
		{
			return FALSE;
		}//end if

		return $this->flag_delay_process_request;
	}//end function
}//end class