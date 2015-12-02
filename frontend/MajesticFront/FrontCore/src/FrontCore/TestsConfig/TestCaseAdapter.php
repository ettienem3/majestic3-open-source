<?php
namespace FrontCore\TestsConfig;

use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use PHPUnit_Framework_TestCase;
use FrontCore\TestsConfig\TestCaseAdapterInterface;

abstract class TestCaseAdapter extends \PHPUnit_Framework_TestCase
{
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;
	protected $serviceManager;
	protected $model_core_profile_settings;
	protected $model_core_user_profile_settings;
	
	protected function setUp()
	{		
		//check if child class implements the correct interface
		if (!$this instanceof TestCaseAdapterInterface)
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Test Case could not executed. It must implement FrontCore\\TestsConfig\\TestCaseAdapterInterface", 500);	
		}//end if
		
		//check if bootstrap instance has been set
		if (!$this->bootstrap)
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Test Case could not executed. Bootstrap instance is not set", 500);
		}//end if
		
		$this->serviceManager = $this->bootstrap->getServiceManager();
		//$this->controller = new IndexController();
		$this->request    = new Request();
		$this->routeMatch = new RouteMatch(array('controller' => 'index'));
		$this->event      = new MvcEvent();
		$config = $this->serviceManager->get('Config');
		$routerConfig = isset($config['router']) ? $config['router'] : array();
		$router = HttpRouter::factory($routerConfig);
	
		$this->event->setRouter($router);
		$this->event->setRouteMatch($this->routeMatch);
		//$this->controller->setEvent($this->event);
		//$this->controller->setServiceLocator($serviceManager);

		//create logged in user
		$arr_user_data = array(
				"id"			=> "1",
				"uname" 		=> "user",
				"pword" 		=> "5f4dcc3b5aa765d61d8327deb882cf99",
				"api_key" 		=> "2c0f-828c-b184-f33f-2944-ad2d-f51c-e17a-7a13-ef2a-f581-8e2b",
				"phpunit"		=> TRUE,	//set phpunit flag to indicate testing is in progress.
		);

		$objUserSession = $this->serviceManager->get("FrontUserLogin\Models\FrontUserSession")->createUserSession((object) $arr_user_data);
	}//end function
	
	/**
	 * Generic function to check api responses
	 * @param object $objResult
	 * @param int $expected_HTTP_RESPONSE_CODE
	 */
	protected function checkAPIResponseParams($objResult, $expected_HTTP_RESPONSE_CODE = 200)
	{
		/**
		 * Test - Check HTTP_RESPONSE_CODE matches the expected result code
		 */
		$this->assertEquals($objResult->HTTP_RESPONSE_CODE, $expected_HTTP_RESPONSE_CODE);
		
		/**
		 * Test - Check HTTP_RESPONSE_MESSAGE isset
		 */
		$this->assertTrue(is_string($objResult->HTTP_RESPONSE_MESSAGE));
		
		/**
		 * Test - Check URL_REQUESTED isset
		 */
		$this->assertTrue(is_string($objResult->URL_REQUESTED));
	}//end function
	
	/**
	 * Create an instance of the Api Request Model using the Service Manager
	 * @return \FrontCore\Models\ApiRequestModel
	 */
	protected function getApiRequestModel()
	{
		$objApiRequestModel = $this->serviceManager->get("FrontCore\Models\ApiRequestModel");
		$objApiRequestModel->resetApiModule();
		return $objApiRequestModel;
	}//end function
}//end class