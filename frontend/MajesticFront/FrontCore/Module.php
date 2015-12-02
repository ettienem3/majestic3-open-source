<?php
namespace FrontCore;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\Session\SessionManager;
use Zend\Session\Container;

use FrontCore\Events\FrontCoreEvents;
use FrontCore\Models\ApiRequestModel;
use FrontCore\Models\ApiRequestModelComplete;
use FrontCore\Models\ApiRequestFormsModel;
use FrontCore\Models\SystemFormsModel;
use FrontCore\Models\FrontCoreNavigation;
use FrontCore\Entities\FrontCoreSystemFormEntity;
use FrontCore\ViewHelpers\FrontAdminFormRenderHelper;
use FrontCore\ViewHelpers\FrontRenderDatatable;
use FrontCore\ViewHelpers\FrontRenderSimpleHtmlTable;
use FrontCore\ViewHelpers\FrontRenderNotificationHelper;
use FrontCore\Factories\FrontNavigationFactory;
use FrontUserLogin\Models\FrontUserSession;
use FrontCore\ControllerHelpers\FrontControllerFormErrorHelper;
use FrontCore\ViewHelpers\FrontRenderPaginatorHelper;
use FrontCore\ViewHelpers\FrontStandardViewHeaderHelper;
use FrontCore\Caches\FrontCoreCacheRedis;
use FrontCore\Caches\FrontCachesRedis;
use FrontCore\Caches\FrontCachesFileSystem;
use Zend\Cache\StorageFactory;
use FrontCore\Factories\FrontCoreServiceProviderFactory;
use FrontCore\ViewHelpers\FrontStandardViewFormHelpButtonHelper;
use FrontCore\Models\FrontCoreSecurityModel;
use FrontCore\Events\FrontCoreSystemFormEvents;
use FrontCore\Models\Security\CryptoModel;

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
        $eventsFrontCore = $e->getApplication()->getServiceManager()->get("FrontCore\Events\FrontCoreEvents");
        $eventsFrontCore->registerEvents();

        $eventsSystemForms = $e->getApplication()->getServiceManager()->get("FrontCore\Events\FrontCoreSystemFormEvents");
        $eventsSystemForms->registerEvents();

        //append app config to layout
        //load config
        $arr_config = $e->getApplication()->getServiceManager()->get("config");
        $e->getViewModel()->setVariable("app_config", $arr_config);
		$e->getViewModel()->setVariable("cdn_url", $arr_config["cdn_config"]["url"]);

        //preload the Service Manager instance to the Service Manager Factory
        FrontCoreServiceProviderFactory::setInstance($e->getApplication()->getServiceManager());

        /**
         * Check if user is logged in
         */
        $sharedEvents = $eventManager->getSharedManager();
        $sharedEvents->attach(
	       		"*",
        		'dispatch',
        		function($e) {
	        			// fired when an ActionController under the namespace is dispatched.
	        			$controller = $e->getTarget();

        				//first check if user needs to be logged in
	        			if ($e->getRouteMatch()->getParam("user-bypass-login") === TRUE)
	        			{
	        				return;
	        			}//end if

	        			$arr_exclude_controllers = array(
	        						"FrontUserLogin\\Controller\\IndexController",
	        					);

	        			//check for cli requests
	        			if (get_class($controller) == "FrontCLI\\Controller\\IndexController")
	        			{
	        				//check if module is activated
	        				$objModuleManager = $e->getApplication()->getServiceManager()->get('ModuleManager');
	        				$arr_modules = $objModuleManager->getLoadedModules();
	        				if (array_key_exists("FrontCLI", $arr_modules))
	        				{
	        					return;
	        				}//end if
	        			}//end if

	        			if ((strtolower(substr(get_class($controller), 0, 5)) == "front" && !in_array(get_class($controller), $arr_exclude_controllers)) || (strtolower($e->getRouteMatch()->getMatchedRouteName()) == "home"))
	        			{
							//check if user is logged in
							if (!FrontUserSession::isLoggedIn())
							{
								//redirect to login screen and set message
								$flashMessenger = new \Zend\Mvc\Controller\Plugin\FlashMessenger();
								$flashMessenger->addInfoMessage("Please login to continue");

								//redirect back to login page
								$target = $e->getTarget();
								if (strtolower($e->getRouteMatch()->getMatchedRouteName()) == "home")
								{
									//home page, access service manager differently from event
									$serviceLocator = $target->getServiceManager();
								} else {
									$serviceLocator = $target->getServiceLocator();
								}//end if

								$url = $e->getRouter()->assemble(array(
																	"controller" => "FrontUserLogin\\Controller\\IndexController"
																	),
																array (
																		'name' => 'front-user-login'
																));
								$response = $e->getResponse();
								$response->setHeaders($response->getHeaders()->addHeaderLine( 'Location', $url ));
								$response->setStatusCode(302);
								$response->sendHeaders();
								exit();
							}//end if
	        			}//end if
	        		},
        		110);

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

        /**
         * Log api calls
         */
        if ((isset($_GET["debug_display_errors"]) && $_GET["debug_display_errors"] == 1) || (isset($_GET["debug_display_queries"]) && $_GET["debug_display_queries"] == 1))
        {
	        //get shared event manager
	        $sem = $e->getApplication()->getEventManager()->getSharedManager();
	        $sem->attach("*", "apiCallExecuted", function ($event) use ($e) {
	        	$objApiData = $event->getParam("objApiData");
	        	$objResponse = $event->getParam("objResponse");
	        	$objApiData->rawResponse = $objResponse->getBody();

	        	if (isset($_GET["debug_display_errors"]) && $_GET["debug_display_errors"] == 1)
	        	{
		        	$url = $objApiData->url;
		        	$response = $objApiData->rawResponse;

		        	$arr = $e->getViewModel()->getVariable("api_logs");
		        	$arr[] = $objApiData;
		        	$arr[] = $response;
		        	$e->getViewModel()->setVariable("api_logs", $arr);
	        	}//end if
	        });
        }//end if

        /**
         * Start session
         */
		session_start();

		//load icon packs
		$this->setIconPacks();
    }//end function

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
    	return array();
    }//end function

    public function getControllerPluginConfig()
    {
    	return array(
    				'factories' => array(
						'frontFormHelper' => function ($sm) {
							$plugin_front_controller_form_helper = new FrontControllerFormErrorHelper();
							return $plugin_front_controller_form_helper;
						}, //end function
    				),
    	);
    }//end function

    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(
    					/**
    					 * Databases
    					 */
    					'db_frontend' => function ($sm) {
    						$arr = $sm->get("config");
    						//retrieved from you domain config file, or global if you moved it there
    						$arr_dbParams = $arr["frontend_db_config"];

    						$dbParams = array(
    								/**
    								 * Implement decryption where you need to
    								 */
//     								'database'  => self::decryptConfigValues($sm,  $arr_dbParams['database']),
//     								'username'  => self::decryptConfigValues($sm,  $arr_dbParams['username']),
//     								'password'  => self::decryptConfigValues($sm,  $arr_dbParams['password']),
//     								'hostname'  => self::decryptConfigValues($sm,  $arr_dbParams['hostname']),
    								'database'  => $arr_dbParams['database'],
    								'username'  => $arr_dbParams['username'],
    								'password'  => $arr_dbParams['password'],
    								'hostname'  => $arr_dbParams['hostname'],
    								// buffer_results - only for mysqli buffered queries, skip for others
    								'options' => array('buffer_results' => true)
    						);

    						$adapter = new \Zend\Db\Adapter\Adapter(array(
    								'driver'    => 'pdo',
    								'dsn'       => 'mysql:dbname='.$dbParams['database'].';host='.$dbParams['hostname'],
    								'database'  => $dbParams['database'],
    								'username'  => $dbParams['username'],
    								'password'  => $dbParams['password'],
    								'hostname'  => $dbParams['hostname'],
    						));
    						return $adapter;
    					},

    					/**
    					 * Models
    					 */
    					'FrontCore\Models\ApiRequestModel' => function ($sm) {
    						if (class_exists('\FrontCore\Models\ApiRequestModelComplete'))
    						{
								$model_api_request = new ApiRequestModelComplete();
    						} else {
    							$model_api_request = new ApiRequestModel();
    						}//end if

    						//load config
    						$arr_config = $sm->get("config");

    						//set the api url
    						$model_api_request->setApiUrl($arr_config["profile_config"]["api_request_location"]);

    						return $model_api_request;
    					}, //end function

    					'FrontCore\Models\SystemFormsModel' => function ($sm) {
    						$model_system_forms = new SystemFormsModel();
    						return $model_system_forms;
    					}, //end function

    					'FrontCore\Models\ApiRequestFormsModel' => function ($sm) {
    						$model_api_form_request = new ApiRequestFormsModel();
    						return $model_api_form_request;
    					}, //end function

    					'FrontCore\Models\FrontCoreSecurityModel' => function ($sm) {
    						$model = new FrontCoreSecurityModel();
    						return $model;
    					}, //end function

    					'FrontCore\Models\Security\CryptoModel' => function ($sm) {
    						$model = new CryptoModel();
    						return $model;
    					}, //end function

    					/**
    					 * Entities
    					 */
						'FrontCore\Entities\FrontCoreSystemFormEntity' => function ($sm) {
							$entity_system_form = new FrontCoreSystemFormEntity();
							return $entity_system_form;
						}, //end function

    					/**
    					 * Events
    					 */
    					'FrontCore\Events\FrontCoreEvents' => function ($sm) {
    						$events_frontcore = new FrontCoreEvents();
    						return $events_frontcore;
    					},

    					'FrontCore\Events\FrontCoreSystemFormEvents' => function ($sm) {
    						$events = new FrontCoreSystemFormEvents();
    						return $events;
    					}, //end function

    					/**
    					 * Navigation
    					 */
    					'FrontCore\Factories\FrontNavigationFactory' => function ($sm) {
    						$objNavigation = new FrontNavigationFactory();
    						return $objNavigation;
    					},

    					/**
    					 * Caches
    					 */
    					//auto loaded for cache instance, prefers redis over file system
    					'FrontCore\Caches\Cache' => function ($sm) {
    						$arr_config = $sm->get("config");
    						try{
    							$cache = StorageFactory::factory($arr_config["cache_redis_config_common"]);
    							$objCache = new FrontCachesRedis($cache);
    							return $objCache;
    						}catch (\Exception $e) {
    							//log redis error
    							trigger_error("Redis cache connection failed to start: " . $e->getMessage() . " " . $e->getPrevious(), E_USER_WARNING);

    							$dir = "./data/cache/system_forms";
    							if (!is_dir("./data/cache/system_forms"))
    							{
    								mkdir($dir, 0777, TRUE);
    							}//end if

    							//try local file system
    							try {
    								$arr_cache_config = $arr_config["cache_filesystem_config_common"];
    								$arr_cache_config["adapter"]["options"]["cache_dir"] = $dir;
    								$cache = StorageFactory::factory($arr_cache_config);
    								$objCache = new FrontCachesFileSystem($cache);
    								return $objCache;
    							} catch (\Exception $e) {
    								throw new \Exception(__CLASS__ . " Line " . __LINE__ . " : System Form Cache could not create Redis of Filesystem cache", 500);
    							}//end catch
    						}//end catch

    						return FALSE;
    					}, //end function

    					'FrontCore\Caches\FrontCachesFileSystem' => function ($sm) {
    						$arr_config = $sm->get("config");
    						$dir = "./data/cache/system_forms";
    						if (!is_dir("./data/cache/system_forms"))
    						{
    							mkdir($dir, 0777, TRUE);
    						}//end if

    						$arr_cache_config = $arr_config["cache_filesystem_config_common"];
    						$arr_cache_config["adapter"]["options"]["cache_dir"] = $dir;
    						$cache = StorageFactory::factory($arr_cache_config);
							$objCache = new FrontCachesFileSystem($cache);
							return $objCache;
    					}, //end function

    					'FrontCore\Caches\FrontCachesRedis' => function ($sm) {
    						$arr_config = $sm->get("config");

    						try {
    							if (isset($arr_config["cache_redis_config_common"]))
    							{
    								$cache = StorageFactory::factory($arr_config["cache_redis_config_common"]);
    								$objCache = new FrontCachesRedis($cache);
    								return $objCache;
    							}//end if

    							//default to file system cache
    							return $sm->get('FrontCore\Caches\FrontCachesFileSystem');
    						} catch (\Exception $e) {
    							//log redis error
//uncomment the line below to enable error reporting on failure to connect to redis
//     							trigger_error('Redis cache connection failed to start: ' . $e->getMessage() . " " . $e->getPrevious(), E_USER_WARNING);

    							//default to file system cache
    							return $sm->get('FrontCore\Caches\FrontCachesFileSystem');
    						}//end catch
    					}, //end function
    			),

    			"shared" => array(
    					'FrontCore\Entities\FrontCoreSystemFormEntity' => FALSE,
    			),
    	);
    }//end function

    public function getViewHelperConfig()
    {
    	return array(
    		"factories" => array(
    			/**
    			 * Renders a system form layout
    			 */
    			"renderSystemFormHelper" => function (AbstractPluginManager $pluginManager) {
    				return new FrontAdminFormRenderHelper();
    			}, //end function

    			"renderSystemFormHelpButtonHelper" => function (AbstractPluginManager $pluginManager) {
    				return new FrontStandardViewFormHelpButtonHelper();
    			}, //end function

    			/**
    			 * Create a Datatable using javascript
    			 */
    			"renderDataTableHelper" => function (AbstractPluginManager $pluginManager) {
    				return new FrontRenderDatatable();
    			}, //end function

    			/**
    			 * Creates a standard HTML table compatible with DataTable
    			 */
    			"renderSimpleHTMLTable" => function (AbstractPluginManager $pluginManager) {
    				return new FrontRenderSimpleHtmlTable();
    			}, //end function

    			/**
    			 * Creates a flash message
    			 */
    			"renderNotificationHelper" => function (AbstractPluginManager $pluginManager) {

    				return new FrontRenderNotificationHelper();
    			}, // end function

    			/**
    			 * Paginator Helper
    			 */
    			"renderPaginationHelper" => function (AbstractPluginManager $pluginManager) {

    				return new FrontRenderPaginatorHelper();
    			}, // end function

    			/**
    			 * Standard View Header
    			 */
    			"renderStandardViewHeader" => function (AbstractPluginManager $pluginManager) {
    				return new FrontStandardViewHeaderHelper();
    			}, //end function
    		),
    	);
    }//end function

    /**
     * Create Icon Packs
     */
    private function setIconPacks()
    {
    	$arr_icons = array(
			"active" 					=> "glyphicon glyphicon-ok",
			"add" 						=> "glyphicon glyphicon-plus",
    		"aim"						=> "glyphicon glyphicon-screenshot",
    		"announcement"				=> "glyphicon glyphicon-earphone",
			"attachment" 				=> "glyphicon glyphicon-paperclip",
			"back_old" 					=> "glyphicon glyphicon-arrow-left",
			"back"						=> "glyphicon glyphicon-arrow-left",
    		"back_left"					=> "glyphicon glyphicon-arrow-left",
			"bar-chart" 				=> "glyphicon glyphicon-signal",
			"behaviours" 				=> "glyphicon glyphicon-pushpin",
			"bulk"						=> "glyphicon glyphicon-bullhorn",
    		"campaigns"					=> "glyphicon glyphicon-magnet",
			"calendar" 					=> "glyphicon glyphicon-calendar",
    		"city"						=> "glyphicon glyphicon-king",
			"comment" 					=> "glyphicon glyphicon-paperclip",
			"comms" 					=> "glyphicon glyphicon-volume-down",
			"contacts"					=> 'glyphicon glyphicon-user',
    		"contact_status"			=> "glyphicon glyphicon-record",
			"database" 					=> "glyphicon glyphicon-oil",
			"delete" 					=> "glyphicon glyphicon-remove",
			"duplicate" 				=> "glyphicon glyphicon-floppy-disk",
    		"edit"						=> "glyphicon glyphicon-wrench",
			"email" 					=> "glyphicon glyphicon-envelope",
			"exit" 						=> "glyphicon glyphicon-off",
			"fax" 						=> "glyphicon glyphicon-phone-alt",
			"fields" 					=> "glyphicon glyphicon-th-list",
            "inbox"                     => "glyphicon glyphicon-inbox",
			"file_manager" 				=> "glyphicon glyphicon-file",
			"files" 					=> "glyphicon glyphicon-folder-close",
			"forms" 					=> "glyphicon glyphicon-list-alt",
			"groups" 					=> "glyphicon glyphicon-globe",
    		"help"						=> "glyphicon glyphicon-question-sign",
			"html_templates_comms" 		=> "glyphicon glyphicon-th",
			"html_templates_forms" 		=> "glyphicon glyphicon-th",
			"inactive" 					=> "glyphicon glyphicon-remove-circle",
			"info" 						=> "glyphicon glyphicon-option-horizontal",
    		"layout"					=> "glyphicon glyphicon-blackboard",
			"line-chart" 				=> "glyphicon glyphicon-stats",
    		"link"						=> "glyphicon glyphicon-resize-small",
    		"links"						=> "glyphicon glyphicon-resize-small",
    		"list"						=> "glyphicon glyphicon-list",
			"load" 						=> "glyphicon glyphicon-floppy-open",
			"loading" 					=> "glyphicon glyphicon-transfer",
    		"location"					=> "glyphicon glyphicon-cloud-upload",
    		"look_feel"					=> "glyphicon glyphicon-refresh",
			"modify" 					=> "glyphicon glyphicon-wrench",
			"next" 						=> "glyphicon glyphicon-arrow-right",
			"order"						=> "glyphicon glyphicon-sort-by-order",
    		"panels"					=> "glyphicon glyphicon-flag",
			"picture" 					=> "glyphicon glyphicon-picture",
			"pie-chart" 				=> "glyphicon glyphicon-adjust",
    		"preview"					=> "glyphicon glyphicon-eye-open",
			"print" 					=> "glyphicon glyphicon-print",
			"profile" 					=> "glyphicon glyphicon-eye-open",
    		"profile-admin"				=> "glyphicon glyphicon-folder-close",
    		"profiles"					=> "glyphicon glyphicon-folder-close",
			"refresh" 					=> "glyphicon glyphicon-refresh",
			"report" 					=> "glyphicon glyphicon-stats",
			"roles" 					=> "glyphicon glyphicon-link",
			"save" 						=> "glyphicon glyphicon-floppy-save",
			"search" 					=> "glyphicon glyphicon-zoom-in",
			"secure" 					=> "",
			"settings" 					=> "glyphicon glyphicon-cog",
			"sms" 						=> "glyphicon glyphicon-phone-alt",
			"status-error" 				=> "glyphicon glyphicon-remove",
			"status-info" 				=> "glyphicon glyphicon-eye-open",
			"status-success" 			=> "glyphicon glyphicon-ok",
    		"sync"						=> "glyphicon glyphicon-retweet",
			"tables" 					=> "glyphicon glyphicon-th-large",
			"tasks" 					=> "glyphicon glyphicon-tasks",
			"unsecure" 					=> "",
			"tracker"					=> "glyphicon glyphicon-filter",
			"upload" 					=> "glyphicon glyphicon-cloup-upload",
    		"user-data-acl"				=> "glyphicon glyphicon-bishop",
    		"user"						=> "glyphicon glyphicon-user",
    		"users" 					=> "glyphicon glyphicon-user",
			"warning" 					=> "glyphicon glyphicon-warning-sign",
    		"webhook"					=> "glyphicon glyphicon-globe",
    	);

    	foreach ($arr_icons as $icon => $css) {

            $additional_style = "";

            //set sepcific icon additional styles
    		switch ($icon)
    		{

                case "active":
					$additional_style = "color: green;";
    				break;

    			case "inactive":
    			case "delete":
    				$additional_style = "color: red;";
    				break;
    		} //end switch

    		$icon = str_replace("-", "_", strtoupper($icon));
    		if (!defined("ICON_SMALL_{$icon}_HTML"))
    		{
    			define("ICON_SMALL_{$icon}_HTML", "<span style=\"$additional_style\"><span class=\"$css\"></span></span>");
    			define("ICON_MEDIUM_{$icon}_HTML", "<span style=\"$additional_style\"><span class=\"$css\"></span></span>");
    			define("ICON_LARGE_{$icon}_HTML", "<span style=\"$additional_style\"><span class=\"$css\"></span></span>");
    			define("ICON_XLARGE_{$icon}_HTML", "<span style=\"$additional_style\"><span class=\"$css\"></span></span>");
    			define("ICON_CSS_{$icon}_CLASS", $css);
    		}//end if
    	}//end foreach
    }//end function

    private function decryptConfigValues($sm, $string)
    {
    	$objCoreCrypto = $sm->get("FrontCore\Models\Security\CryptoModel");
    	$arr_config = $sm->get("config");
    	$secret_key = $arr_config['front_end_application_config']['security']['secret_key'];
    	$secret_iv = $arr_config['front_end_application_config']['security']['secret_iv'];

    	return $objCoreCrypto->sha1EncryptDecryptValue("decrypt", $string, array(
    			"secret_key" => $secret_key,
    			"secret_iv" => $secret_iv,
    	));
    }//end function
}//end class
