<?php
namespace FrontCommsAdmin;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontCommsAdmin\Events\FrontCommsAdminEvents;
use FrontCommsAdmin\Events\FrontCommDatesEvents;
use FrontCommsAdmin\Models\FrontCommDatesModel;
use FrontCommsAdmin\Models\FrontCommsAdminModel;
use FrontCommsAdmin\Models\FrontJourneysModel;
use FrontCommsAdmin\Models\FrontCommsAdminCommAttachmentsModel;
use FrontCommsAdmin\Entities\FrontCommAdminEntity;
use FrontCommsAdmin\Entities\FrontCommDateEntity;
use FrontCommsAdmin\Entities\FrontJourneysEntity;
use FrontCommsAdmin\Models\FrontCommsAdminCommEmbeddedImagesModel;
use FrontCommsAdmin\Entities\FrontCommEmbeddedImageEntity;
use FrontCommsAdmin\Caches\FrontCommsJourneyCache;
use FrontCommsAdmin\Models\FrontJourneysTestModel;

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
        $eventsFrontCommsAdmin = $e->getApplication()->getServiceManager()->get("FrontCommsAdmin\Events\FrontCommsAdminEvents");
        $eventsFrontCommsAdmin->registerEvents();

        /**
         * Register event listeners
         */
        $eventsFrontCommDates = $e->getApplication()->getServiceManager()->get("FrontCommsAdmin\Events\FrontCommDatesEvents");
        $eventsFrontCommDates->registerEvents();
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
    					"FrontCommsAdmin\Models\FrontCommDatesModel" =>  function ($sm) {
    						$model_comm_dates = new FrontCommDatesModel();
    						return $model_comm_dates;
    					}, //end function

    					"FrontCommsAdmin\Models\FrontCommsAdminModel" => function ($sm) {
    						$model_comms_admin_model = new FrontCommsAdminModel();
    						return $model_comms_admin_model;
    					}, //end function

    					'FrontCommsAdmin\Models\FrontJourneysModel'	=> function ($sm) {
    						$model_journeys = new FrontJourneysModel();
    						return $model_journeys;
    					}, //end function

    					'FrontCommsAdmin\Models\FrontCommsAdminCommAttachmentsModel' => function ($sm) {
    						$model_comm_attachments = new FrontCommsAdminCommAttachmentsModel();
    						return $model_comm_attachments;
    					}, //end function

    					'FrontCommsAdmin\Models\FrontCommsAdminCommEmbeddedImagesModel' => function ($sm) {
    						$model_comm_embedded_images = new FrontCommsAdminCommEmbeddedImagesModel();
    						return $model_comm_embedded_images;
    					}, //end function

    					'FrontCommsAdmin\Models\FrontJourneysTestModel' => function ($sm) {
    						$model = new FrontJourneysTestModel();
    						return $model;
    					}, //end function
    					
    					/**
    					 * Entities
    					 */
    					"FrontCommsAdmin\Entities\FrontCommAdminEntity" => function ($sm) {
    						$entity_comm = new FrontCommAdminEntity();
    						return $entity_comm;
    					}, //end function

    					"FrontCommsAdmin\Entities\FrontCommDateEntity" => function($sm) {
    						$entity_comm_date = new FrontCommDateEntity();
    						return $entity_comm_date;
    					}, //end function

    					"FrontCommsAdmin\Entities\FrontJourneysEntity" => function ($sm) {
    						$entity_journeys = new FrontJourneysEntity();
    						return $entity_journeys;
    					}, //end function

    					'FrontCommsAdmin\Entities\FrontCommEmbeddedImageEntity' => function ($sm) {
    						$entity_embedded_image = new FrontCommEmbeddedImageEntity();
    						return $entity_embedded_image;
    					}, //end function

    					/**
    					 * Events
    					 */
    					'FrontCommsAdmin\Events\FrontCommsAdminEvents' => function ($sm) {
    						$events_frontcommsadmin = new FrontCommsAdminEvents();
    						return $events_frontcommsadmin;
    					}, //end function

    					"FrontCommsAdmin\Events\FrontCommDatesEvents" => function ($sm) {
    						$events_comm_dates = new FrontCommDatesEvents();
    						return $events_comm_dates;
    					}, //end function

    					"FrontCommsAdmin\Events\FrontJourneyEvents"	=> function ($sm) {
    						$events_front_journeys = new FrontJourneyEvents();
    						return $events_front_journeys;
    					}, //end function

    					/**
    					 * Caches
    					 */
    					'FrontCommsAdmin\Caches\FrontCommsJourneyCache' => function ($sm) {
    						$cache = new FrontCommsJourneyCache();
    						return $cache;
    					}//end function
    			),

    			"shared" => array(
    					"FrontCommsAdmin\Entities\FrontCommAdminEntity" 			=> FALSE,
    					"FrontCommsAdmin\Entities\FrontCommDateEntity" 				=> FALSE,
    					"FrontCommsAdmin\Entities\FrontJourneysEntity" 				=> FALSE,
    					'FrontCommsAdmin\Entities\FrontCommEmbeddedImageEntity' 	=> FALSE,
    			),

    	);
    }//end function
}//end class