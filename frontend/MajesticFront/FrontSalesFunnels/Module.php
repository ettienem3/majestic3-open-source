<?php 
namespace FrontSalesFunnels;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontSalesFunnels\Events\FrontSalesFunnelsEvents;
use FrontSalesFunnels\Models\FrontSalesFunnelsModel;
use FrontSalesFunnels\Entities\FrontSalesFunnelContactSalesFunnelEntity;

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
        $eventsFrontSalesFunnels = $e->getApplication()->getServiceManager()->get("FrontSalesFunnels\Events\FrontSalesFunnelsEvents");
        $eventsFrontSalesFunnels->registerEvents();
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
    					"FrontSalesFunnels\Models\FrontSalesFunnelsModel" => function ($sm) {
    						$model_sales_funnels = new FrontSalesFunnelsModel();
    						return $model_sales_funnels;	
    					}, //end function
    					
    					/**
    					 * Entities
    					 */
						"FrontSalesFunnels\Entities\FrontSalesFunnelContactSalesFunnelEntity" => function ($sm) {
							$entity_contact_sales_funnel = new FrontSalesFunnelContactSalesFunnelEntity();
							return $entity_contact_sales_funnel;							
						},//end function
    					
    					/**
    					 * Events
    					 */
    					'FrontSalesFunnels\Events\FrontSalesFunnelsEvents' => function ($sm) {
    						$events_frontsalesfunnels = new FrontSalesFunnelsEvents();
    						return $events_frontsalesfunnels;
    					},
    			),

    			"shared" => array(
    					"FrontSalesFunnels\Entities\FrontSalesFunnelContactSalesFunnelEntity" => FALSE,
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