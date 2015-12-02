<?php 
namespace FrontPanels;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use FrontPanels\Events\FrontPanelsEvents;
use FrontPanels\Models\FrontPanelsModel;
use FrontPanels\Entities\FrontPanelsPanelEntity;
use FrontPanels\Panels;

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
        $eventsFrontPanels = $e->getApplication()->getServiceManager()->get("FrontPanels\Events\FrontPanelsEvents");
        $eventsFrontPanels->registerEvents();
    }//end if

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }//end if

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
    }//end if
    
    public function getControllerConfig()
    {
    	return array(
    				'factories' => array(
    					
    				),	
    	);	
    	
    }//end function
    
    public function getServiceConfig()
    {
    	$arr_config = array(
    			'factories' => array(
    					/**
    					 * Models
    					 */
    					"FrontPanels\Models\FrontPanelsModel" => function ($sm) {
    						$model_front_panels = new FrontPanelsModel();
    						return $model_front_panels;	
    					}, //end function
    					
    					/**
    					 * Entities
    					 */
    					"FrontPanels\Entities\FrontPanelsPanelEntity" => function ($sm) {
    						$entity_panel = new FrontPanelsPanelEntity();
    						return $entity_panel;	
    					}, //end function
    					
    					/**
    					 * Events
    					 */
    					'FrontPanels\Events\FrontPanelsEvents' => function ($sm) {
    						$events_frontpanels = new FrontPanelsEvents();
    						return $events_frontpanels;
    					},
    			),	
    			
    			"shared" => array(
    					"FrontPanels\Entities\FrontPanelsPanelEntity" => FALSE,
    			),
    			
    	);
    	
    	//register panel processors
    	$arr_processors = scandir(__DIR__ . "/src/FrontPanels/Panels");
		foreach ($arr_processors as $k => $file)
		{
			if (substr($file, 0, strlen("panel")) == "Panel" && substr($file, -(strlen("model.php"))) == "Model.php")
			{
				$class = "\FrontPanels\Panels\\" . str_replace(".php", "", $file);
				$arr_config["factories"][substr($class, 1)] = function ($sm) use ($class) {
					$panel = new $class;
					return $panel;
				};//end function
			}//end if	
		}//end foreach
		
    	return $arr_config;
    }//end function
    
    public function getViewHelperConfig()
    {
    	return array(
    			'factories' => array(
    			
    			),	
    	);
    }//end function
}//end class