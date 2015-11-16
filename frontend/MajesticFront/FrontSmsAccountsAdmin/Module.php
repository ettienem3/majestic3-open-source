<?php 
namespace FrontSmsAccountsAdmin;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontSmsAccountsAdmin\Events\FrontSmsAccountsAdminEvents;
use FrontSmsAccountsAdmin\Models\FrontSmsAccountsAdminModel;
use FrontSmsAccountsAdmin\Entities\FrontSmsAccountsAdminSmsAccountEntity;

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
        $eventsFrontSmsAccountsAdmin = $e->getApplication()->getServiceManager()->get("FrontSmsAccountsAdmin\Events\FrontSmsAccountsAdminEvents");
        $eventsFrontSmsAccountsAdmin->registerEvents();
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
    					"FrontSmsAccountsAdmin\Models\FrontSmsAccountsAdminModel" => function ($sm) {
    						$model_front_sms_account_admin = new FrontSmsAccountsAdminModel();
    						return $model_front_sms_account_admin;
    					}, //end function
    					
    					/**
    					 * Entities
    					 */
    					"FrontSmsAccountsAdmin\Entities\FrontSmsAccountsAdminSmsAccountEntity" => function ($sm) {
    						$entity_front_sms_account = new FrontSmsAccountsAdminSmsAccountEntity();
    						return $entity_front_sms_account;
    					}, //end function
    					
    					/**
    					 * Events
    					 */
    					'FrontSmsAccountsAdmin\Events\FrontSmsAccountsAdminEvents' => function ($sm) {
    						$events_frontsmsaccountsadmin = new FrontSmsAccountsAdminEvents();
    						return $events_frontsmsaccountsadmin;
    					},
    			),	
    			
    			"shared" => array(
    				"FrontSmsAccountsAdmin\Entities\FrontSmsAccountsAdminSmsAccountEntity" => FALSE,	
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