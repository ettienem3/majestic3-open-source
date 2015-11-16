<?php 
namespace FrontProfileSettings;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontProfileSettings\Events\FrontProfileSettingsEvents;
use FrontProfileSettings\Entities\FrontProfileSettingsProfileEntity;
use FrontProfileSettings\Entities\FrontProfileNativeSettingsProfileEntity;
use FrontProfileSettings\Models\FrontProfileSettingsModel;
use FrontProfileSettings\Models\NativeProfileSettingsModel;

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
        $eventsFrontProfileSettings = $e->getApplication()->getServiceManager()->get("FrontProfileSettings\Events\FrontProfileSettingsEvents");
        $eventsFrontProfileSettings->registerEvents();
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
    					'FrontProfileSettings\Models\FrontProfileSettingsModel' => function ($sm) {
    						$model_profile_settings = new FrontProfileSettingsModel();
    						return $model_profile_settings;	
    					}, //end function
    					
    					'FrontProfileSettings\Models\NativeProfileSettingsModel' => function ($sm) {
    						$model_native_profile_settings = new NativeProfileSettingsModel();
    						return $model_native_profile_settings;
    					}, //end function
    					
    					/**
    					 * Entities
    					 */
						'FrontProfileSettings\Entities\FrontProfileSettingsProfileEntity' => function ($sm) {
							$entity_profile_settings = new FrontProfileSettingsProfileEntity();
							return $entity_profile_settings;
						}, //end function
    					
						'FrontProfileSettings\Entities\FrontProfileNativeSettingsProfileEntity' => function ($sm) {
							$entity_profile_settings = new FrontProfileNativeSettingsProfileEntity();
							return $entity_profile_settings;
						}, //end function
						
    					/**
    					 * Events
    					 */
    					'FrontProfileSettings\Events\FrontProfileSettingsEvents' => function ($sm) {
    						$events_frontprofilesettings = new FrontProfileSettingsEvents();
    						return $events_frontprofilesettings;
    					},
    			),

    			'shared' => array(
    					'FrontProfileSettings\Entities\FrontProfileSettingsProfileEntity' => FALSE,
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