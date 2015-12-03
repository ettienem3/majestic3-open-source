<?php
//Added  comment
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
// unset($_GET["XDEBUG_PROFILE"]);
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

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
	$path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
	if (__FILE__ !== $path && is_file($path)) {
		return false;
	}
	unset($path);
}

define('ZF_CLASS_CACHE', './data_local/cache/classes.php.cache');
if (file_exists(ZF_CLASS_CACHE))
{
 	require_once ZF_CLASS_CACHE;
}//end if

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
