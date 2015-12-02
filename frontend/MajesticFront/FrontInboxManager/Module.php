<?php 
namespace FrontInboxManager;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontInboxManager\Events\FrontInboxManagerEvents;
use FrontInboxManager\Models\FrontInboxManagerModel;
use FrontInboxManager\Entities\FrontInboxManagerMessageEntity;

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
        $eventsFrontInboxManager = $e->getApplication()->getServiceManager()->get("FrontInboxManager\Events\FrontInboxManagerEvents");
        $eventsFrontInboxManager->registerEvents();
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
    					"FrontInboxManager\Models\FrontInboxManagerModel" => function ($sm) {
    						$model_front_inbox_manager = new FrontInboxManagerModel();
    						return $model_front_inbox_manager;	
    					}, //end function
    					
    					/**
    					 * Entities
    					 */
    					"FrontInboxManager\Entities\FrontInboxManagerMessageEntity" => function ($sm) {
    						$entity_inbox_message = new FrontInboxManagerMessageEntity();
    						return $entity_inbox_message;
    					}, //end function
    					/**
    					 * Events
    					 */
    					'FrontInboxManager\Events\FrontInboxManagerEvents' => function ($sm) {
    						$events_frontinboxmanager = new FrontInboxManagerEvents();
    						return $events_frontinboxmanager;
    					},
    			),

    			"shared" => array(
    					"FrontInboxManager\Entities\FrontInboxManagerMessageEntity" => FALSE,
    			),
    			
    	);
    }//end function
}//end class