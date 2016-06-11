<?php
namespace MajesticExternalForms;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\AbstractPluginManager;

use MajesticExternalForms\Models\MajesticExternalFormsModel;
use MajesticExternalForms\Events\MajesticExternalFormsEvents;
use MajesticExternalForms\Models\MajesticExternalFormsCacheModel;
use MajesticExternalForms\ViewHelpers\ExternalFormRenderHelper;
use MajesticExternalForms\ControllerHelpers\ExternalFormsControllerErrorHelper;
use FrontCore\Caches\FrontCachesFileSystem;
use MajesticExternalForms\Models\MajesticExternalLocations;

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
        $eventsMajesticExternalForms = $e->getApplication()->getServiceManager()->get("MajesticExternalForms\Events\MajesticExternalFormsEvents");
        $eventsMajesticExternalForms->registerEvents();

        //set layout for forms being displayed
        /**
         * Set layout
         */
        $sharedEvents = $eventManager->getSharedManager();
        $sharedEvents->attach(
	        		__NAMESPACE__,
	        		'dispatch',
	        		function($e) {
	        			// fired when an ActionController under the namespace is dispatched.
	        			$controller = $e->getTarget();
	        			$controller->layout('layout/external/forms');
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
    				'MajesticExternalForms\Models\MajesticExternalFormsModel' => function ($sm) {
    					$model_forms = new MajesticExternalFormsModel();
    					return $model_forms;
    				}, //end function
    				
    				'MajesticExternalForms\Models\MajesticExternalLocations' => function ($sm) {
    					$model = new MajesticExternalLocations();
    					return $model;
    				}, //end function


    				/**
    				 * Events
    				 */
    				'MajesticExternalForms\Events\MajesticExternalFormsEvents' => function ($sm) {
    					$events_external_forms = new MajesticExternalFormsEvents();
    					return $events_external_forms;
    				}, //end function

    				/**
    				 * Caches
    				 */
    				'MajesticExternalForms\Models\MajesticExternalFormsCacheModel' => function ($sm) {
    					$arr_config = $sm->get("config");
    					
    					//set ttl
    					$ttl = (86400 * 2); //48 hours
    					
    					//override ttl
    					$arr_config["cache_redis_config_common"]['adapter']['options']['ttl'] = $ttl;
    					$arr_config["cache_filesystem_config_common"]['adapter']['options']['ttl'] = $ttl;
    					try{
    						$cache = StorageFactory::factory($arr_config["cache_redis_config_common"]);
    					}catch (\Exception $e) {
    						$dir = "./data/cache/external_forms";
    						if (!is_dir("./data/cache/external_forms"))
    						{
    							mkdir($dir, 0777, TRUE);
    						}//end if

    						//try local file system
    						try {
    							$arr_cache_config = $arr_config["cache_filesystem_config_common"];
    							$arr_cache_config["adapter"]["options"]["cache_dir"] = $dir;
    							$cache = StorageFactory::factory($arr_cache_config);
    						} catch (\Exception $e) {
    							throw new \Exception(__CLASS__ . " Line " . __LINE__ . " : External Form Cache could not create Redis of Filesystem cache", 500);
    						}//end catch
    					}//end catch

    					$model_core_forms_cache = new MajesticExternalFormsCacheModel($cache);
    					return $model_core_forms_cache;
    				}, //end function
    			),
    	);
    }//end function

    public function getControllerPluginConfig()
    {
    	return array(
    			"factories" => array(
    				"externalFormErrorHelper" => function ($sm) {
    					$plugin_external_forms_error_helper = new ExternalFormsControllerErrorHelper();
    					return $plugin_external_forms_error_helper;
    				}, // end function
    		),
    	);
    }//end function

    public function getViewHelperConfig()
    {
    	return array(
    		"factories" => array(
    			/**
    			 * Renders an external form layout
    			 */
    			"renderExternalFormHelper" => function (AbstractPluginManager $pluginManager) {
    				return new ExternalFormRenderHelper();
    			}, //end function
    		),
    	);
    }//end function
}//end class