<?php
namespace FrontBehavioursConfig\Controller;

use FrontCore\Adapters\AbstractCoreActionController;
use Zend\View\Model\JsonModel;

class ProfileSummaryController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Behaviours Config Model
	 * @var \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
	 */
	private $model_front_behaviours_config;

	public function appAction()
	{
		//load a behaviour form to get all the descriptions
		$objForm = $this->getFrontBehavioursModel()->getBehaviourActionsForm('form');
		$this->layout('layout/angular/app');

		$obj_action_descriptors = array();
		foreach ($objForm['arr_descriptors'] as $k => $v)
		{
			$obj_action_descriptors[str_replace('_', '', $k)] = (object) array(
				'key' => $k,
				'description' => str_replace(array("'", '"', ',', '{', '}'), '', $v),
			);	
		}//end foreach
		
		return array(
				'obj_action_descriptors' => $obj_action_descriptors,
		);
	}//end function
	
	public function ajaxRequestAction()
	{
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
		}//end if
		
		try {
			switch ($acrq)
			{
				case 'load-overall-summary':
					$objData = $this->getFrontBehavioursModel()->fetchProfileBehaviourSummary(array('callback' => 'loadProfileBehavioursConfigSummary'));
					
					$objResult = new JsonModel(array(
						'error' => 0,
						'objData' => $objData,
					));
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
     * Create an instance of the Front Behaviours Config Model using the Service Manager
     * @return \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
     */
    private function getFrontBehavioursModel()
    {
    	if (!$this->model_front_behaviours_config)
    	{
    		$this->model_front_behaviours_config = $this->getServiceLocator()->get("FrontBehavioursConfig\Models\FrontBehavioursConfigModel");
    	}//end if

    	return $this->model_front_behaviours_config;
    }//end function
}//end class
