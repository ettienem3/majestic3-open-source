<?php
namespace FrontProfileFileManager;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontProfileFileManager\Events\FrontProfileFileManagerEvents;
use FrontProfileFileManager\Models\FrontProfileFileManagerModel;
use FrontProfileFileManager\Forms\FrontProfileFileManagerForm;

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
        $eventsFrontProfileFileManager = $e->getApplication()->getServiceManager()->get("FrontProfileFileManager\Events\FrontProfileFileManagerEvents");
        $eventsFrontProfileFileManager->registerEvents();
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
    					"FrontProfileFileManager\Models\FrontProfileFileManagerModel" => function ($sm) {
    						$model_front_file_manager = new FrontProfileFileManagerModel();
    						return $model_front_file_manager;
    					}, //end function

    					/**
    					 * Entities
    					 */

    					/**
    					 * Events
    					 */
    					'FrontProfileFileManager\Events\FrontProfileFileManagerEvents' => function ($sm) {
    						$events_frontprofilefilemanager = new FrontProfileFileManagerEvents();
    						return $events_frontprofilefilemanager;
    					},

    					/**
    					 * Form
    					 */
    					'FrontProfileFileManager\Forms\FrontProfileFileManagerForm' => function ($sm) {
    						$form = new FrontProfileFileManagerForm();
    						return $form;
    					}, //end function
    			),

    			"shared" => array(
    				'FrontProfileFileManager\Forms\FrontProfileFileManagerForm' => FALSE,
    			),
    	);
    }//end function
}//end class