<?php
namespace FrontCommsSmsCampaigns;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontCommsSmsCampaigns\Events\FrontCommsSmsCampaignsEvents;
use FrontCommsSmsCampaigns\Models\FrontCommsSmsCampaignsModel;
use FrontCommsSmsCampaigns\Entities\CommsSmsCampaignEntity;
use FrontCommsSmsCampaigns\Models\FrontCommsSmsCampaignsRepliesModel;
use FrontCommsSmsCampaigns\Entities\FrontCommsSmsCampaignReplyEntity;

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
        $events_front_comms_sms_campaigns = $e->getApplication()->getServiceManager()->get("FrontCommsSmsCampaigns\Events\FrontCommsSmsCampaignsEvents");
        $events_front_comms_sms_campaigns->registerEvents();
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
    					'FrontCommsSmsCampaigns\Models\FrontCommsSmsCampaignsModel' => function ($sm) {
    						$model_comms_sms_campaigns = new FrontCommsSmsCampaignsModel();
    						return $model_comms_sms_campaigns;
    					}, //end function

    					"FrontCommsSmsCampaigns\Models\FrontCommsSmsCampaignsRepliesModel" => function ($sm) {
    						$model_comms_sms_campaign_replies = new FrontCommsSmsCampaignsRepliesModel();
    						return $model_comms_sms_campaign_replies;	
    					}, //end function
    					
    					/**
    					 * Entities
    					 */
    					'FrontCommsSmsCampaigns\Entities\CommsSmsCampaignEntity' => function ($sm) {
    						$entity_comms_sms_campaign = new CommsSmsCampaignEntity();
    						return $entity_comms_sms_campaign;
    					}, //end function

    					"FrontCommsSmsCampaigns\Entities\FrontCommsSmsCampaignReplyEntity" => function ($sm) {
    						$entity_comms_sms_campaign_reply = new FrontCommsSmsCampaignReplyEntity();
    						return $entity_comms_sms_campaign_reply;	
    					}, //end function
    					
    					/**
    					 * Events
    					 */
    					'FrontCommsSmsCampaigns\Events\FrontCommsSmsCampaignsEvents' => function ($sm) {
    						$events_front_comms_sms_campaigns = new FrontCommsSmsCampaignsEvents();
    						return $events_front_comms_sms_campaigns;
    					},
    			),
    	);
    }//end function
}//end class