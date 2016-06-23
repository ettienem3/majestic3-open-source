<?php
namespace FrontFormsTemplates;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\TableGateway\TableGateway;

use FrontFormsTemplates\Events\FrontFormsTemplatesEvents;
use FrontFormsTemplates\Models\FrontFormsTemplatesModel;
use FrontFormsTemplates\Entities\FormTemplateEntity;

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
        $eventsFrontFormsTemplates = $e->getApplication()->getServiceManager()->get("FrontFormsTemplates\Events\FrontFormsTemplatesEvents");
        $eventsFrontFormsTemplates->registerEvents();
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

    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(

    					/**
    					 * Models
    					 */
    					'FrontFormsTemplates\Models\FrontFormsTemplatesModel' => function ($sm) {
    						$model_front_forms_templates = new FrontFormsTemplatesModel();
    						return $model_front_forms_templates;
    					},//end function

    					/**
    					 * Entities
    					 */
    					'FrontFormsTemplates\Entities\FormTemplateEntity' => function ($sm) {
    						$entity_forms = new FormTemplateEntity();
    						return $entity_forms;
    					},// end function

    					/**
    					 * Events
    					 */
    					'FrontFormsTemplates\Events\FrontFormsTemplatesEvents' => function ($sm) {
    						$events_frontformstemplates = new FrontFormsTemplatesEvents();
    						return $events_frontformstemplates;
    					},
    			),

    	);
    }//end function

}//end class