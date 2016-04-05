<?php 
namespace FrontCommsBulkSend;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontCommsBulkSend\Events\FrontCommsBulkSendEvents;
use FrontCommsBulkSend\Models\FrontCommsBulkSendModel;
use FrontCommsBulkSend\Entities\FrontCommsBulkSendJourneyEntity;
use FrontCommsBulkSend\Helpers\FrontCommsBulkSendStandardFieldHelper;
use FrontCommsBulkSend\Helpers\FrontCommsBulkSendCustomFieldHelper;
use FrontCommsBulkSend\Entities\FrontCommsBulkSendRequestEntity;

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
        $eventsFrontCommsBulkSend = $e->getApplication()->getServiceManager()->get("FrontCommsBulkSend\Events\FrontCommsBulkSendEvents");
        $eventsFrontCommsBulkSend->registerEvents();
    }//end if

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }//end if

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
    }//end if
    
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
    					"FrontCommsBulkSend\Models\FrontCommsBulkSendModel" => function ($sm) {
    						$model_front_comms_bulk_send = new FrontCommsBulkSendModel();
    						return $model_front_comms_bulk_send;
    					}, //end function
    					
    					/**
    					 * Entities
    					 */
						"FrontCommsBulkSend\Entities\FrontCommsBulkSendJourneyEntity" => function ($sm) {
							$entity_bulk_send_journey = new FrontCommsBulkSendJourneyEntity();
							return $entity_bulk_send_journey;
						}, //end function
						
						'FrontCommsBulkSend\Entities\FrontCommsBulkSendRequestEntity' => function ($sm) {
							$entity = new FrontCommsBulkSendRequestEntity();
							return $entity;
						}, //end function
    					
    					/**
    					 * Events
    					 */
    					'FrontCommsBulkSend\Events\FrontCommsBulkSendEvents' => function ($sm) {
    						$events_frontcommsbulksend = new FrontCommsBulkSendEvents();
    						return $events_frontcommsbulksend;
    					},
    					
    					/**
    					 * Helpers
    					 */
    					"FrontCommsBulkSend\Helpers\FrontCommsBulkSendStandardFieldHelper" => function ($sm) {
    						$helper_standard_field = new FrontCommsBulkSendStandardFieldHelper();
    						return $helper_standard_field;
    					}, //end function
    					
    					"FrontCommsBulkSend\Helpers\FrontCommsBulkSendCustomFieldHelper" => function ($sm) {
    						$helper_custom_field = new FrontCommsBulkSendCustomFieldHelper();
    						return $helper_custom_field;
    					}, //end function
    			),
    			
    			'shared' => array(
    					"FrontCommsBulkSend\Entities\FrontCommsBulkSendJourneyEntity" => FALSE,
    					'FrontCommsBulkSend\Entities\FrontCommsBulkSendRequestEntity' => FALSE,
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