<?php 
namespace MajesticExternalUtilities;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use MajesticExternalUtilities\Events\MajesticExternalUtilitiesEvents;
use MajesticExternalUtilities\Models\MajesticExternalUtilitiesModel;

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
        $eventsMajesticExternalUtilities = $e->getApplication()->getServiceManager()->get("MajesticExternalUtilities\Events\MajesticExternalUtilitiesEvents");
        $eventsMajesticExternalUtilities->registerEvents();
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
    
    public function getControllerConfig()
    {
    	return array(
    				'factories' => array(
    					
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
    					"MajesticExternalUtilities\Models\MajesticExternalUtilitiesModel" => function ($sm) {
    						$model_external_utilities = new MajesticExternalUtilitiesModel();
    						return $model_external_utilities;	
    					}, //end function
    					
    					/**
    					 * Entities
    					 */
    					
    					/**
    					 * Events
    					 */
    					'MajesticExternalUtilities\Events\MajesticExternalUtilitiesEvents' => function ($sm) {
    						$events_majesticexternalutilities = new MajesticExternalUtilitiesEvents();
    						return $events_majesticexternalutilities;
    					},
    			),	
    			
    	);
    }//end function
    
    public function getViewHelperConfig()
    {
    	return array(
    			'factories' => array(
    			
    			),	
    	);
    }//end function
}//end class