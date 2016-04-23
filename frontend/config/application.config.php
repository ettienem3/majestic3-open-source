<?php
/**
 * Make sure requested domain has a config file attached to it
 */
if (!is_file("./config/autoload/domains/" . $_SERVER["HTTP_HOST"] . ".php"))
{
	die("Configuration file does not exist");
}//end if

return array(
    // This should be an array of module namespaces used in the application.
    'modules' => array(
    	/**
    	 * General Modules
    	 */
    	//'EdpSuperluminal',

    	/**
    	 * Majestic FrontEnd
    	 */
    	'FrontCore',
    	'FrontBehavioursConfig',
    	'FrontCommsTemplates',
    	'FrontCommsAdmin',
    	'FrontCommsBulkSend',
    	'FrontCommsSmsCampaigns',
    	'FrontContacts',
    	'FrontFormAdmin',
    	'FrontFormsTemplates',
    	'FrontInboxManager',
    	'FrontLinks',
    	'FrontLocations',
    	'FrontPanels',
    	'FrontPowerTools',
    	'FrontProfileFileManager',
    	'FrontProfileSettings',
    	'FrontReports',
    	'FrontSalesFunnels',
    	'FrontSmsAccountsAdmin',
    	'FrontStatuses',
    	'FrontUsers',
    	'FrontUserLogin',

    	/**
    	* Majestic External Components
    	*/
    	'MajesticExternalContacts',
    	'MajesticExternalForms',
    	'MajesticExternalUtilities',

    	/**
    	 * Majestic Interactive Components
    	 */

    	/**
    	 * Majestic Vendors Modules
    	 */

    	/**
    	 * Custom Modules
    	 */
    ),

    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => array(
        // This should be an array of paths in which modules reside.
        // If a string key is provided, the listener will consider that a module
        // namespace, the value of that key the specific path to that module's
        // Module class.
        'module_paths' => array(
            './vendor',
        	'./MajesticExternal',			//Modules that face the external world not governed by frontend rules
        	'./MajesticFront',				//Front end modules
        ),

        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => array(
        	'config/autoload/{,*.}{global,local}.php',
        	'config/autoload/domains/' . strtolower($_SERVER["HTTP_HOST"]) . '.php', //load config file for the specific domain request is received from
        ),

        // Whether or not to enable a configuration cache.
        // If enabled, the merged configuration will be cached and used in
        // subsequent requests.
        'config_cache_enabled' => FALSE,

        // The key used to create the configuration cache file name.
        'config_cache_key' => str_replace(".", "", $_SERVER["HTTP_HOST"]),

        // Whether or not to enable a module class map cache.
        // If enabled, creates a module class map cache which will be used
        // by in future requests, to reduce the autoloading process.
        //'module_map_cache_enabled' => $booleanValue,

        // The key used to create the class map cache file name.
        //'module_map_cache_key' => $stringKey,

        // The path in which to cache merged configuration.
        'cache_dir' => "data/cache/frontend_config",

        // Whether or not to enable modules dependency checking.
        // Enabled by default, prevents usage of modules that depend on other modules
        // that weren't loaded.
        // 'check_dependencies' => true,
    ),

    // Used to create an own service manager. May contain one or more child arrays.
    //'service_listener_options' => array(
    //     array(
    //         'service_manager' => $stringServiceManagerName,
    //         'config_key'      => $stringConfigKey,
    //         'interface'       => $stringOptionalInterface,
    //         'method'          => $stringRequiredMethodName,
    //     ),
    // )

   // Initial configuration with which to seed the ServiceManager.
   // Should be compatible with Zend\ServiceManager\Config.
   // 'service_manager' => array(),
);
