<?php
namespace FrontLocations;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontLocations\Models\FrontLocationsModel;
use FrontLocations\Models\FrontLocationsCustomCitiesModel;
use FrontLocations\Entities\FrontLocationsCustomCityEntity;
use FrontLocations\Entities\FrontLocationsEntity;
use FrontLocations\Events\FrontLocationsEvents;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        /**
         * Register event listeners
         */
        $eventsFrontLocations = $e->getApplication()->getServiceManager()->get("FrontLocations\Events\FrontLocationsEvents");
        $eventsFrontLocations->registerEvents();
    }//end function

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }//end function

    public function getAutoloaderConfig()
    {
        return array(
        		'Zend\Loader\ClassMapAutoloader' => array(
        				__DIR__ . '/autoload_classmap.php',
        		),
            	'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }//end function

    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(
    					/**
    					 * Models
    					 */
    					"FrontLocations\Models\FrontLocationsModel" => function ($sm) {
    						$model_locations = new FrontLocationsModel();
    						return $model_locations;
    					}, //end functoin

    					/**
    					 * Entities
    					 */
    					"FrontLocations\Entities\FrontLocationsEntity" => function ($sm) {
    						$entity_location = new FrontLocationsEntity();
    						return $entity_location;
    					}, //end function

    					/**
    					 * Events
    					 */
    					'FrontLocations\Events\FrontLocationsEvents' => function ($sm) {
    						$events_frontlocations = new FrontLocationsEvents();
    						return $events_frontlocations;
    					},
    			),

    			'shared' => array(
    					"FrontLocations\Entities\FrontLocationsEntity" => FALSE,
    			),

    	);
    }//end function
}//end class