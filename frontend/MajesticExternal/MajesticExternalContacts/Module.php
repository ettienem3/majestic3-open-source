<?php 
namespace MajesticExternalContacts;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use MajesticExternalContacts\Events\MajesticExternalContactsEvents;

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
        $eventsMajesticExternalContacts = $e->getApplication()->getServiceManager()->get("MajesticExternalContacts\Events\MajesticExternalContactsEvents");
        $eventsMajesticExternalContacts->registerEvents();
		
		// set layout for forms being displayed
		/**
		 * Set layout
		 */
		$sharedEvents = $eventManager->getSharedManager ();
		$sharedEvents->attach ( __NAMESPACE__, 'dispatch', function ($e) {
				// fired when an ActionController under the namespace is dispatched.
				$controller = $e->getTarget();
	        	$controller->layout('layout/external/contacts');
	        },
	        100);
		

		/**
		 * Deal with flash messages that needs to be passed to the layout view
		 */
		$eventManager->attach(\Zend\Mvc\MvcEvent::EVENT_RENDER, function($e) {
			$flashMessenger = new \Zend\Mvc\Controller\Plugin\FlashMessenger();
			if ($flashMessenger->hasMessages()) {
				$e->getViewModel()->setVariable('flashMessages', $flashMessenger->getMessages());
			}//end if
			if ($flashMessenger->hasErrorMessages()) {
				$e->getViewModel()->setVariable('flashMessages_errors', $flashMessenger->getErrorMessages());
			}//end if
			if ($flashMessenger->hasInfoMessages()) {
				$e->getViewModel()->setVariable('flashMessages_info', $flashMessenger->getInfoMessages());
			}//end if
			if ($flashMessenger->hasSuccessMessages()) {
				$e->getViewModel()->setVariable('flashMessages_success', $flashMessenger->getSuccessMessages());
			}//end if
		});
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
    					 * Events
    					 */
    					'MajesticExternalContacts\Events\MajesticExternalContactsEvents' => function ($sm) {
    						$events_majesticexternalcontacts = new MajesticExternalContactsEvents();
    						return $events_majesticexternalcontacts;
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