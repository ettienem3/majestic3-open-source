<?php 
namespace FrontBehavioursConfig;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontBehavioursConfig\Events\FrontBehavioursConfigEvents;
use FrontBehavioursConfig\Models\FrontBehavioursConfigModel;
use FrontBehavioursConfig\Entities\FrontBehavioursBehaviourConfigEntity;

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
        $eventsFrontBehavioursConfig = $e->getApplication()->getServiceManager()->get("FrontBehavioursConfig\Events\FrontBehavioursConfigEvents");
        $eventsFrontBehavioursConfig->registerEvents();
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
    					'FrontBehavioursConfig\Models\FrontBehavioursConfigModel' => function ($sm) {
    						$model_front_behaviours_config = new FrontBehavioursConfigModel();
    						return $model_front_behaviours_config;	
    					}, //end function
    					
    					/**
    					 * Entities
    					 */
    					'FrontBehavioursConfig\Entities\FrontBehavioursBehaviourConfigEntity' => function ($sm) {
    						$entity_front_behaviour_config = new FrontBehavioursBehaviourConfigEntity();
    						return $entity_front_behaviour_config;	
    					}, //end function
    					
    					/**
    					 * Events
    					 */
    					'FrontBehavioursConfig\Events\FrontBehavioursConfigEvents' => function ($sm) {
    						$events_frontbehavioursconfig = new FrontBehavioursConfigEvents();
    						return $events_frontbehavioursconfig;
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