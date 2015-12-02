<?php 
namespace FrontUserLogin;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontUserLogin\Models\FrontUserLoginModel;
use FrontUserLogin\Models\FrontUserSession;
use FrontUserLogin\Tables\UserLoginTable;
use FrontUserLogin\Events\FrontUserLoginEvents;	

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
        $eventsFrontUserLogin = $e->getApplication()->getServiceManager()->get("FrontUserLogin\Events\FrontUserLoginEvents");
        $eventsFrontUserLogin->registerEvents();
        
        /**
         * Set toolkit layout
         */
        $sharedEvents = $eventManager->getSharedManager();
        $sharedEvents->attach(
        		"FrontUserLogin",
        		'dispatch',
        		function($e) {
        			// fired when an ActionController under the namespace is dispatched.
        			$controller = $e->getTarget();
					$routeMatch = $e->getRouteMatch();
					$routeName = $routeMatch->getMatchedRouteName(); 

        			if (strtolower($routeName) == "front-user-login")
        			{
        				$controller->layout('layout/login');
        			}//end if
        		},
        		100);
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
    					'FrontUserlogin\Models\FrontUserLoginModel' => function ($sm) {
    						$model_userlogin = new FrontUserLoginModel();
    						return $model_userlogin;
    					}, //end function
    						
    					"FrontUserLogin\Models\FrontUserSession" => function ($sm) {
    						$model_user_session = new FrontUserSession;
    						return $model_user_session;
    					}, //end function
    					
    					/**
    					 * Entities
    					*/
    					'FrontUserLogin\Entities\UserLoginEntity' => function ($sm) {
    						$entity_userlogin = new UserLoginEntity();
    						return $entity_userlogin;
    					}, //end function
    						
    					/**
    					 * Events
    					*/
    					'FrontUserLogin\Events\FrontUserLoginEvents' => function ($sm) {
    						$events_frontuserlogin = new FrontUserLoginEvents();
    						return $events_frontuserlogin;
    					},
    			),
    	);   	
    }//end function
        
}//end class