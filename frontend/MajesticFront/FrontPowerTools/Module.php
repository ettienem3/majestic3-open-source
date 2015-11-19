<?php
namespace FrontPowerTools;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontPowerTools\Models\FrontAnnouncementsModel;
use FrontPowerTools\Entities\FrontAnnouncementEntity;
use FrontPowerTools\Events\FrontPowerToolsEvents;
use FrontPowerTools\Entities\FrontPowerToolsWebhookEntity;
use FrontPowerTools\Entities\FrontPowerToolsWebhookHeaderEntity;
use FrontPowerTools\Entities\FrontPowerToolsWebhookUrlEntity;
use FrontPowerTools\Models\FrontPowerToolsWebhookHeadersModel;
use FrontPowerTools\Models\FrontPowerToolsWebhookUrlsModel;
use FrontPowerTools\Models\FrontPowerToolsWebhooksModel;
use FrontPowerTools\Models\FrontPowerToolsCommsAutomationModel;
use FrontPowerTools\Models\FrontPowerToolsNewsFeedModel;

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
        $eventsFrontPowerTools = $e->getApplication()->getServiceManager()->get("FrontPowerTools\Events\FrontPowerToolsEvents");
        $eventsFrontPowerTools->registerEvents();
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
    					'FrontPowerTools\Models\FrontAnnouncementsModel' => function ($sm) {
    						$model_announcements = new FrontAnnouncementsModel();
    						return $model_announcements;
    					}, //end function

    					"FrontPowerTools\Models\FrontPowerToolsWebhooksModel" => function ($sm) {
    						$model_webhooks = new FrontPowerToolsWebhooksModel();
    						return $model_webhooks;
    					}, //end function

    					"FrontPowerTools\Models\FrontPowerToolsWebhookHeadersModel" => function ($sm) {
    						$model_webhook_headers = new FrontPowerToolsWebhookHeadersModel();
    						return $model_webhook_headers;
    					}, //end function

    					"FrontPowerTools\Models\FrontPowerToolsWebhookUrlsModel" => function ($sm) {
    						$model_webhook_urls = new FrontPowerToolsWebhookUrlsModel();
    						return $model_webhook_urls;
    					}, //end function

    					"FrontPowerTools\Models\FrontPowerToolsCommsAutomationModel" => function ($sm) {
    						$model_comms_automation = new FrontPowerToolsCommsAutomationModel();
    						return $model_comms_automation;
    					}, //end function

    					'FrontPowerTools\Models\FrontPowerToolsNewsFeedModel' => function ($sm) {
    						$model_newsfeed = new FrontPowerToolsNewsFeedModel();
    						return $model_newsfeed;
    					}, //end function

    					/**
    					 * Entities
    					*/
    					'FrontPowerTools\Entities\FrontAnnouncementEntity' => function ($sm) {
    						$entity_announcement = new FrontAnnouncementEntity();
    						return $entity_announcement;
    					},//end function

    					"FrontPowerTools\Entities\FrontPowerToolsWebhookEntity" => function ($sm) {
    						$entity_webhook = new FrontPowerToolsWebhookEntity();
    						return $entity_webhook;
    					}, //end function

    					"FrontPowerTools\Entities\FrontPowerToolsWebhookHeaderEntity" => function ($sm) {
    						$entity_webhook_header = new FrontPowerToolsWebhookHeaderEntity();
    						return $entity_webhook_header;
    					}, //end function

    					"FrontPowerTools\Entities\FrontPowerToolsWebhookUrlEntity" => function ($sm) {
    						$entity_webhook_url = new FrontPowerToolsWebhookUrlEntity();
    						return $entity_webhook_url;
    					}, //end function

    					/**
    					 * Events
    					 */
    					'FrontPowerTools\Events\FrontPowerToolsEvents' => function ($sm) {
    						$events_frontpowertools = new FrontPowerToolsEvents();
    						return $events_frontpowertools;
    					},

    					"FrontPowerTools\Events\FrontPowerToolsWebhooksEvents" => function ($sm) {
    						$events_webhooks = new FrontPowerToolsEvents();
    						return $events_webhooks;
    					}, //end function
    				),

    			'shared' => array(
    						'FrontPowerTools\Entities\FrontAnnouncementEntity' 				=> FALSE,
    						"FrontPowerTools\Entities\FrontPowerToolsWebhookEntity" 		=> FALSE,
    						"FrontPowerTools\Entities\FrontPowerToolsWebhookHeaderEntity" 	=> FALSE,
    						"FrontPowerTools\Entities\FrontPowerToolsWebhookUrlEntity" 		=> FALSE,
    				),
    		);
    }//end function
}//end class