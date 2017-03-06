<?php
namespace FrontReports;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use FrontReports\Models\FrontReportsModel;
use FrontReports\Models\FrontReportParametersModel;
use FrontReports\Models\FrontReportSettingsModel;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
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
    					'FrontReports\Models\FrontReportsModel' => function ($sm) {
							$model_reports = new FrontReportsModel();
							return $model_reports;
    					}, //end function

    					'FrontReports\Models\FrontReportParametersModel' => function ($sm) {
    						$model_report_params = new FrontReportParametersModel();
    						return $model_report_params;
    					}, //end function
    					
    					'FrontReports\Models\FrontReportSettingsModel' => function ($sm) {
    						$model = new FrontReportSettingsModel();
    						return $model;
    					}, //end function
    			),

    			'shared' => array(

    			),

    	);
    }//end function

    public function getViewHelperConfig()
    {
    	return array(
    			'factories' => array(

    			),
    	);
    }//end function
}//end class