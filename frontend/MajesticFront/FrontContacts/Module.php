<?php
namespace FrontContacts;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontContacts\Events\FrontContactsEvents;
use FrontContacts\Models\FrontContactsModel;
use FrontContacts\Models\FrontContactsFormsModel;
use FrontContacts\Models\FrontContactsStatusesModel;
use FrontContacts\Models\FrontContactsJourneysModel;
use FrontContacts\Models\FrontContactCommsTemplatesModel;
use FrontContacts\Entities\FrontContactsContactEntity;
use FrontContacts\Entities\FrontContactsFormsEntity;
use FrontContacts\Entities\FrontContactsContactStatusEntity;
use FrontContacts\Entities\FrontContactsJourneyEntity;
use FrontContacts\Entities\FrontContactsCommTemplateEntity;
use FrontContacts\Models\FrontContactsSystemFieldsModel;
use FrontContacts\Models\FrontContactsLinkedContactsModel;

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
        $eventsFrontContacts = $e->getApplication()->getServiceManager()->get("FrontContacts\Events\FrontContactsEvents");
        $eventsFrontContacts->registerEvents();
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
    					'FrontContacts\Models\FrontContactsModel' => function ($sm) {
    						$model_contacts = new FrontContactsModel();
    						return $model_contacts;
    					}, //end function
    					
    					'FrontContacts\Models\FrontContactsFormsModel' => function ($sm) {
    						$model_contact_forms = new FrontContactsFormsModel();
    						return $model_contact_forms;
    					}, //end function
    					
    					'FrontContacts\Models\FrontContactsStatusesModel' => function ($sm) {
    						$model_contact_status = new FrontContactsStatusesModel();
    						return $model_contact_status;
    					}, //end function
    					
    					'FrontContacts\Models\FrontContactsJourneysModel' => function($sm) {
    						$model_contact_journeys = new FrontContactsJourneysModel();
    						return $model_contact_journeys;
    					}, //end function

    					"FrontContacts\Models\FrontContactCommsTemplatesModel" => function ($sm) {
    						$model_contact_comm_templates = new FrontContactCommsTemplatesModel();
    						return $model_contact_comm_templates;	
    					}, //end function
    					
    					"FrontContacts\Models\FrontContactsSystemFieldsModel" => function ($sm) {
    						$model_contact_system_fields = new FrontContactsSystemFieldsModel();
    						return $model_contact_system_fields;
    					}, //end function
    					
    					'FrontContacts\Models\FrontContactsLinkedContactsModel' => function ($sm) {
    						$model = new FrontContactsLinkedContactsModel();
    						return $model;
    					}, //end function
    					
    					/**
    					 * Entities
    					 */
    					'FrontContacts\Entities\FrontContactsContactEntity' => function ($sm) {
    						$entity_contact = new FrontContactsContactEntity();
    						return $entity_contact;
    					}, //end function
    					
    					'FrontContacts\Entities\FrontContactsFormsEntity' => function ($sm) {
    						$entity_contact_forms = new FrontContactsFormsEntity();
    						return $entity_contact_forms;
    					}, //end function
    					
    					'FrontContacts\Entities\FrontContactsContactStatusEntity' => function ($sm) {
    						$entity_contact_status = new FrontContactsContactStatusEntity();
    						return $entity_contact_status;
    					}, //end function
    					
    					'FrontContacts\Entities\FrontContactsJourneyEntity' => function ($sm) {
    						$entity_contact_journey = new FrontContactsJourneyEntity();
    						return $entity_contact_journey;
    					}, //end function
    					
    					'FrontContacts\Entities\FrontContactsCommTemplateEntity' => function ($sm) {
    						$entity_contact_comm_template = new FrontContactsCommTemplateEntity();
    						return $entity_contact_comm_template;
    					}, //end function
    					
    					/**
    					 * Events
    					 */
    					'FrontContacts\Events\FrontContactsEvents' => function ($sm) {
    						$events_frontcontacts = new FrontContactsEvents();
    						return $events_frontcontacts;
    					},
    			),
		
    			"shared" => array(
    					'FrontContacts\Entities\FrontContactsContactEntity' 		=> FALSE,
    					'FrontContacts\Entities\FrontContactsFormsEntity' 			=> FALSE,
    					'FrontContacts\Entities\FrontContactsContactStatusEntity' 	=> FALSE,
    					'FrontContacts\Entities\FrontContactsJourneyEntity'			=> FALSE,
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