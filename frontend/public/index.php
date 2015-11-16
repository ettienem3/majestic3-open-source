<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
$base_path = "#ZS_APPLICATION_BASE_DIR"; //this line is replaced by the deployment process in cluster...
if ($base_path != "#" . "ZS_APPLICATION_BASE_DIR")
{
	//cluster environment
	chdir($base_path);
} else {
	//normal enviroments
	chdir(dirname(__DIR__));
}//end if

ini_set("memory_limit", "256m");
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 0);

//@TODO remove condition
if(isset($_GET["debug_display_errors"]) && $_GET["debug_display_errors"] == 1)
{
	ini_set("display_errors", 1);
// 	ini_set('xdebug.var_display_max_depth', 5);
// 	ini_set('xdebug.var_display_max_children', 256);
// 	ini_set('xdebug.var_display_max_data', 1024);
}//end if

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server')
{
	$path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
	if (__FILE__ !== $path && is_file($path)) {
		return false;
	}
	unset($path);
}//end if

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
