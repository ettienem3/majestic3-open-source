<?php
namespace FrontCommsAdmin\Controller;

use FrontCore\Adapters\AbstractCoreActionController;
use Zend\View\Model\JsonModel;

class JourneysTestController extends AbstractCoreActionController
{
	/**
	 * Container for the Journeys Test Model
	 * @var \FrontCommsAdmin\Models\FrontJourneysTestModel
	 */
	private $model_front_journeys_test;
	
	public function appAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['test-journeys'] != true)
		{
			$this->flashMessenger()->addInfoMessage('The requested view is not available');
			return $this->redirect()->toRoute('front-comms-admin/journeys');
		}//end if
		
		$this->layout('layout/angular/app');
		
		return array();
	}//end function
	
	public function ajaxRequestAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['journeys'] != true)
		{
			return new JsonModel(array(
					'error' => 1,
					'response' => 'Requested functionality is not available',
			));
		}//end if
		
		$arr_params = $this->params()->fromQuery();
		if (isset($arr_params['acrq']))
		{
			$acrq = $arr_params['acrq'];
		}//end if
		
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_post_data = json_decode(file_get_contents('php://input'), true);
			if (isset($arr_post_data['acrq']))
			{
				$acrq = $arr_post_data['acrq'];
				unset($arr_post_data['acrq']);
			}//end if
				
			if (isset($arr_post_data['journey_id']))
			{
				$arr_params['journey_id'] = $arr_post_data['journey_id'];
			}//end if
		}//end if
		
		try {
			switch ($acrq)
			{
				case 'load-journeys':
					$arr_search_params = array();
						
					if (isset($arr_params['qp_limit']) && is_numeric($arr_params['qp_limit']))
					{
						$arr_search_params['qp_limit'] = $arr_params['qp_limit'];
					}//end if
						
					if (isset($arr_params['qp_start']) && is_numeric($arr_params['qp_start']))
					{
						$arr_search_params['qp_start'] = $arr_params['qp_start'];
					}//end if
						
					if (isset($arr_params['journeys_journey']) && is_string($arr_params['journeys_journey']))
					{
						$arr_search_params['journeys_journey'] = $arr_params['journeys_journey'];
					}//end if
						
					if (isset($arr_params['journeys_status']) && is_numeric($arr_params['journeys_status']))
					{
						$arr_search_params['journeys_status'] = $arr_params['journeys_status'];
					}//end if
						
					$objJourneys = $this->getJourneysTestModel()->fetchJourneys($arr_search_params, FALSE);
					
					$arr_journeys = array();
					foreach ($objJourneys as $objJourney)
					{
						if (isset($objJourney->id))
						{
							$arr_journeys[] = $objJourney;
						}//end if
					}//end foreach
						
					//add hypermedia
					$arr_journeys['hypermedia'] = $objJourneys->hypermedia;
					$objResult = new JsonModel(array(
							'objData' => (object) $arr_journeys,
							'error' => 0,
					));
					return $objResult;
					break;
					
				case 'load-journey':
					//load the requested journey
					$objJourney = $this->getJourneysTestModel()->fetchJourney($arr_params['journey_id']);
					
					if (is_object($objJourney) && $objJourney->get('id') == $arr_params['journey_id'])
					{
						$objResult = new JsonModel(array(
								'objData' => (object) $objJourney->getArrayCopy(),
								'error' => 0,
						));
					} else {
						$objResult = new JsonModel(array(
								'error' => 1,
								'response' => 'The requested journey could not be located'
						));
					}//end if
					
					return $objResult;
					break;
					
				case 'load-tests':
					$objData = $this->getJourneysTestModel()->fetchJourneyTests($arr_params);
					$objResult = new JsonModel(array(
							'objData' => (object) $objData,
							'error' => 0,
					));
					return $objResult;
					break;
					
				case 'create-test':
					try {
						$objData = $this->getJourneysTestModel()->createJourneyTest($arr_post_data);
						$objResult = new JsonModel(array(
								'objData' => (object) $objData,
								'error' => 0,
						));
						return $objResult;
					} catch (\Exception $e) {
						$objResult = new JsonModel(array(
								'error' => 1,
								'response' => $this->frontControllerErrorHelper()->formatErrors($e),
						));
						return $objResult;
					}//end catch
					break;
					
				case 'delete-test':
					switch ($arr_post_data['operation'])
					{
						case 'delete-test':
							$objData = $this->getJourneysTestModel()->deleteJourneyTest($arr_post_data['id']);
							$objResult = new JsonModel(array(
									'objData' => (object) $objData,
									'error' => 0,
							));
							break;
							
						case 'delete-journey':
							$objData = $this->getJourneysTestModel()->deleteJourneyTestJourney($arr_post_data['id']);
							$objResult = new JsonModel(array(
									'objData' => (object) $objData,
									'error' => 0,
							));
							break;
							
						case 'delete-contact':
							$objData = $this->getJourneysTestModel()->deleteJourneyTestContact($arr_post_data['id']);
							$objResult = new JsonModel(array(
									'objData' => (object) $objData,
									'error' => 0,
							));
							break;
					}//end switch

					return $objResult;
					break;
			}//end switch
		} catch (\Exception $e) {
			$objResult = new JsonModel(array(
					'error' => 1,
					'response' => $e->getMessage(),
			));
			return $objResult;
		}//end catch
				
		$objResult = new JsonModel(array(
				'error' => 1,
				'response' => 'Request type is not specified',
		));
		return $objResult;
	}//end function
	
	/**
	 * Create an instance of the Journey Test Model using the Service Manager
	 * @return \FrontCommsAdmin\Models\FrontJourneysTestModel
	 */
	private function getJourneysTestModel()
	{
		if (!$this->model_front_journeys_test)
		{
			$this->model_front_journeys_test = $this->getServiceLocator()->get('FrontCommsAdmin\Models\FrontJourneysTestModel');
		}//end if
		
		return $this->model_front_journeys_test;
	}//end function
}//end class