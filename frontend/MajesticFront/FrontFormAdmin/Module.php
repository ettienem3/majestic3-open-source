<?php
namespace FrontFormAdmin;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity;
use FrontFormAdmin\Entities\FrontFormAdminFormEntity;
use FrontFormAdmin\Entities\FrontFormAdminFieldEntity;
use FrontFormAdmin\Entities\FrontFormAdminReplaceFieldEntity;
use FrontFormAdmin\Models\FrontFieldAdminModel;
use FrontFormAdmin\Models\FrontFormAdminModel;
use FrontFormAdmin\Models\FrontGenericFieldsAdminModel;
use FrontFormAdmin\Models\FrontReplaceFieldsAdminModel;
use FrontFormAdmin\Events\FrontFormAdminEvents;

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
        $eventsFrontFormAdmin = $e->getApplication()->getServiceManager()->get("FrontFormAdmin\Events\FrontFormAdminEvents");
        $eventsFrontFormAdmin->registerEvents();
    }//end fucntion

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
    					'FrontFormAdmin\Models\FrontFormAdminModel' => function ($sm) {
    						$model_form_admin = new FrontFormAdminModel();
    						return $model_form_admin;
    					}, //end function

    					'FrontFormAdmin\Models\FrontFieldAdminModel' => function ($sm) {
    						$model_field_admin = new FrontFieldAdminModel();
    						return $model_field_admin;
    					}, //end function

    					'FrontFormAdmin\Models\FrontGenericFieldsAdminModel' => function ($sm) {
    						$model_generic_fields = new FrontGenericFieldsAdminModel();
    						return $model_generic_fields;
    					}, //end function
    					
    					'FrontFormAdmin\Models\FrontReplaceFieldsAdminModel' => function ($sm) {
    						$model_replace_fields = new FrontReplaceFieldsAdminModel();
    						return $model_replace_fields;
    					}, //end function

    					/**
    					 * Entities
    					 */
						'FrontFormAdmin\Entities\FrontFormAdminFieldEntity' => function ($sm) {
							$entity_field = new FrontFormAdminFieldEntity();
							return $entity_field;
						}, //end function

						'FrontFormAdmin\Entities\FrontFormAdminFormEntity' => function ($sm) {
							$entity_form = new FrontFormAdminFormEntity();
							return $entity_form;
						}, //end function

						'FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity' => function ($sm) {
							$entity_form_field = new FrontFormAdminFormFieldEntity();
							return $entity_form_field;
						}, //end function

						'FrontFormAdmin\Entities\FrontFormAdminReplaceFieldEntity' => function ($sm) {
							$entity_replace_field = new FrontFormAdminReplaceFieldEntity();
							return $entity_replace_field;
						}, //end function
						
    					/**
    					 * Events
    					 */
    					'FrontFormAdmin\Events\FrontFormAdminEvents' => function ($sm) {
    						$events_frontformadmin = new FrontFormAdminEvents();
    						return $events_frontformadmin;
    					},
    			),

    			'shared' => array(
    					//disable sharing on entities, ie create a new instance every time
    					'FrontFormAdmin\Entities\FrontFormAdminFieldEntity' => FALSE,
    					'FrontFormAdmin\Entities\FrontFormAdminFormEntity' => FALSE,
    					'FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity' => FALSE,
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