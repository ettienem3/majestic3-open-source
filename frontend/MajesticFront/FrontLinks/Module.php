<?php
namespace FrontLinks;



use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontLinks\Events\FrontLinksEvents;
use FrontLinks\Models\FrontLinksModel;
use FrontLinks\Entities\LinkEntity;

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
        $eventsFrontLinks = $e->getApplication()->getServiceManager()->get("FrontLinks\Events\FrontLinksEvents");
        $eventsFrontLinks->registerEvents();
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

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
    }

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
    					'FrontLinks\Models\FrontLinksModel' => function ($sm) {
    						$model_links = new FrontLinksModel();
    						return $model_links;
    					}, //end function

    					/**
    					 * Entities
    					 */
    					'FrontLinks\Entities\LinkEntity' => function ($sm) {
    						$entity_link = new LinkEntity();
    						return $entity_link;
    					}, //end function

    					/**
    					 * Events
    					 */
    					'FrontLinks\Events\FrontLinksEvents' => function ($sm) {
    						$events_frontlinks = new FrontLinksEvents();
    						return $events_frontlinks;
    					},
    			),
    			
    			"shared" => array(
    				"FrontLinks\Entities\LinkEntity" => FALSE,	
				),
    	);
    }//end function

    public function getViewHelperConfig()
    {
    	return array(
    			'factories' => array(

    			),
    	);
    }
}//end class