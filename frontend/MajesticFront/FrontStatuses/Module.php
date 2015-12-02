<?php 
namespace FrontStatuses;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\TableGateway\TableGateway;

use FrontStatuses\Events\FrontContactStatusesEvents;
use FrontStatuses\Entities\ContactStatusesEntity;
use FrontStatuses\Models\FrontContactStatusesModel;

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
        $eventsFrontContactStatuses = $e->getApplication()->getServiceManager()->get("FrontStatuses\Events\FrontContactStatusesEvents");
        $eventsFrontContactStatuses->registerEvents();
    } // end function

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
    } // end function
    
   
    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(
    					/**
    					 * Models
    					 */
    					'FrontStatuses\Models\FrontContactStatusesModel' => function ($sm) {
    						$model_contact_statuses = new FrontContactStatusesModel();
    						return $model_contact_statuses;
    					}, //end function

    					/**
    					 * Entities
    					 */
    					'FrontContactStatuses\Entities\ContactStatusEntity' => function ($sm) {
    						$entity_contact_status = new ContactStatusEntity();
    						return $entity_contact_status;
    					}, //end function

    					/**
    					 * Events
    					 */
    					'FrontStatuses\Events\FrontContactStatusesEvents' => function ($sm) {
    						$events_front_contact_statuse = new FrontContactStatusesEvents();
    						return $events_front_contact_statuse;
    					},				
    			),	
    	);
    }//end function
}//end class