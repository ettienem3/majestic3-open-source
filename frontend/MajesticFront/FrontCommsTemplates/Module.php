<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace FrontCommsTemplates;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontCommsTemplates\Events\FrontCommsTemplatesEvents;	//	process actions
use FrontCommsTemplates\Models\FrontCommsTemplatesModel;	//	declares actions
use FrontCommsTemplates\Entities\CommTemplateEntity;				//	transport data objects

/**
 * FormCommsTemplates deals with creating, updating and adminstration HTML Comm Templates used wihtin email comms
 * Module is the subprogram of the application.
 * This module initialize variables that are going to used in the application. 
 * 
 * The ModuleManager will call getAutoloaderConfig() and getConfig() automatically for us.
 */
class Module
{
	/**
	 * onBootstrap() method initializes variables for EventManager.
	 * @param MvcEvent $e
	 */
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        /**
         * Register event listeners
         */
        $eventsFrontCommsTemplates = $e->getApplication()->getServiceManager()->get("FrontCommsTemplates\Events\FrontCommsTemplatesEvents");
        $eventsFrontCommsTemplates->registerEvents();
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
    }//end function

    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(
    					/**
    					 * Models - processes the business logic of the application.
    					 */
    					'FrontCommsTemplates\Models\FrontCommsTemplatesModel' => function ($sm) {
    						$model_commstemplates = new FrontCommsTemplatesModel();
    						return $model_commstemplates;
    					}, //end function

    					/**
    					 * Entities -  This provides a common setup for Entity objects.
    					 */
    					'FrontCommsTemplates\Entities\CommTemplateEntity' => function ($sm) {
    						$entity_commtemplate = new CommTemplateEntity();
    						return $entity_commtemplate;
    					}, //end function

    					/**
    					 * Events - accepts input (i.e. keyboard, mouse, touchecreen etc) processes of the front-end the application.
    					 */
    					'FrontCommsTemplates\Events\FrontCommsTemplatesEvents' => function ($sm) {
    						$events_frontcommstemplates = new FrontCommsTemplatesEvents();
    						return $events_frontcommstemplates;
    					},
    			),

    	);
    }//end function

}//end class