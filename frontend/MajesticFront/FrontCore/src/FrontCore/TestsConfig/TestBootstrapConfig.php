<?php
namespace FrontCore\TestsConfig;

use Zend\Loader\AutoloaderFactory;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;
use RuntimeException;

// error_reporting(E_ALL | E_STRICT);
chdir(TEST_WORKING_DIR);

//load additional required classes
require "TestCaseAdapter.php";
require "TestCaseAdapterInterface.php";

abstract class TestBootstrapConfig
{
    protected static $serviceManager;
    protected static $config;
    protected static $bootstrap;

    public static function init()
    {
         // Load the user-defined test configuration file, if it exists; otherwise, load
        if (is_readable(TEST_WORKING_DIR . '/TestConfig.php')) {
            $testConfig = include TEST_WORKING_DIR . '/TestConfig.php';
            
            //add default required modules to make tests work
            $testConfig["modules"][] = "FrontCore";
            $testConfig["modules"][] = "FrontUserLogin";
    		
    		//load required vendor modules
    		//$testConfig["modules"][] = "HTMLPurifier";

    		$arr_default_module_listner_optons = array(
							    					'config_glob_paths'    => array(
							    							'../../../config/autoload/{,*.}{fronttest}.php',
							    					),
											        'module_paths' => array(
											            'module',
											            'vendor',										        	
											            'MajesticFront',
											        ),
											    );
    		
    		//set module listener options
    		if (array_key_exists("module_listener_options", $testConfig))
    		{
    			 //merge arrays
    			$testConfig['module_listener_options'] = array_merge($testConfig['module_listener_options'], $arr_default_module_listner_optons);
    		} else {
    			$testConfig['module_listener_options'] = $arr_default_module_listner_optons;
    		}//end if
        } else {
            throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Test cannot be executed. Module Test Config file is not available.", 500);
        }//end if

        $zf2ModulePaths = array();

        if (isset($testConfig['module_listener_options']['module_paths'])) {
            $modulePaths = $testConfig['module_listener_options']['module_paths'];
            foreach ($modulePaths as $modulePath) {
                if (($path = static::findParentPath($modulePath)) ) {
                    $zf2ModulePaths[] = $path;
                }
            }
        }

        $zf2ModulePaths  = implode(PATH_SEPARATOR, $zf2ModulePaths) . PATH_SEPARATOR;
        $zf2ModulePaths .= getenv('ZF2_MODULES_TEST_PATHS') ?: (defined('ZF2_MODULES_TEST_PATHS') ? ZF2_MODULES_TEST_PATHS : '');

        static::initAutoloader();

        // use ModuleManager to load this module and it's dependencies
        $baseConfig = array(
            'module_listener_options' => array(
                'module_paths' => explode(PATH_SEPARATOR, $zf2ModulePaths),
            ),
        );

        $config = ArrayUtils::merge($baseConfig, $testConfig);

        $serviceManager = new ServiceManager(new ServiceManagerConfig());
        $serviceManager->setService('ApplicationConfig', $config);
        $serviceManager->get('ModuleManager')->loadModules();

        static::$serviceManager = $serviceManager;
        static::$config = $config;
    }

    public static function getServiceManager()
    {
        return static::$serviceManager;
    }

    public static function getConfig()
    {
        return static::$config;
    }

    protected static function initAutoloader()
    {
        $vendorPath = static::findParentPath('vendor');

        if (is_readable($vendorPath . '/autoload.php')) {
            $loader = include $vendorPath . '/autoload.php';
        } else {
            $zf2Path = getenv('ZF2_PATH') ?: (defined('ZF2_PATH') ? ZF2_PATH : (is_dir($vendorPath . '/ZF2/library') ? $vendorPath . '/ZF2/library' : false));

            if (!$zf2Path) {
                throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.');
            }

            include $zf2Path . '/Zend/Loader/AutoloaderFactory.php';

        }

        AutoloaderFactory::factory(array(
            'Zend\Loader\StandardAutoloader' => array(
                'autoregister_zf' => true,
                'namespaces' => array(
                    __NAMESPACE__ => TEST_WORKING_DIR . '/' . __NAMESPACE__,
                ),
            ),
        ));
    }

    protected static function findParentPath($path)
    {
        $dir = TEST_WORKING_DIR;
        $previousDir = '.';
        while (!is_dir($dir . '/' . $path)) {
            $dir = dirname($dir);
            if ($previousDir === $dir) return false;
            $previousDir = $dir;
        }
        return $dir . '/' . $path;
    }
}//end class