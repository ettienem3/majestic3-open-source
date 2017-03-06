<?php
namespace FrontCommsAdmin\Controller;

use FrontCore\Adapters\AbstractCoreActionController;
use Zend\View\Model\JsonModel;

class CommDatesController extends AbstractCoreActionController
{
	/**
	 * Container for the Commdates Model instance
	 * @var \FrontCommsAdmin\Models\FrontCommDatesModel
	 */
	private $model_commdates;
    
    public function appAction()
    {
    	$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
    	if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['journey-dates'] != true)
    	{
    		$this->flashMessenger()->addInfoMessage('The requested view is not available');
    		return $this->redirect()->toRoute('home');
    	}//end if
    	
    	$this->layout('layout/angular/app');
    	    	
    	return array();
    }//end function
    
    public function ajaxRequestAction()
    {
    	$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
    	if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['journey-dates'] != true)
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
    	}//end if
    	
    	try {
    		//map request to the correct function
    		switch ($acrq)
    		{
    			case 'list-records':
    				$objCommDates = $this->getCommDatesModel()->fetchCommDates();
    				$arr_comm_dates = array();
    				foreach ($objCommDates as $objDate)
    				{
    					if (is_numeric($objDate->id))
    					{
    						
    						$arr_comm_dates[] = $objDate;
    					}//end if
    				}//end foreach
    				//load admin form
    				$form = $this->getCommDatesModel()->getCommDatesForm();
    				$objResult = new JsonModel(array(
    					'objData' => (object) $arr_comm_dates,	
    				));
    				break;
    				
    			case 'create-record':
    				//load admin form
    				$form = $this->getCommDatesModel()->getCommDatesForm();
    				$form->setData($arr_post_data);
    				
    				if ($form->isValid())
    				{
    					//create the trigger
    					$arr_data = (array) $form->getData();
    					$objRecord = $this->getCommDatesModel()->createCommDate($arr_data);
    					
    					$objResult = new JsonModel(array(
    						'objData' => (object) $objRecord->getArrayCopy(),	
    					));
    				} else {
    					//return form errors
    					$objResult = new JsonModel(array(
    						'error' => 1,
    						'response' => 'Form could not be validated',
    						'form_messages' => $form->getMessages()
    					));
    				}//end if
    				break;
    				
    			case 'edit-record':
    				//load the record
    				$objRecord = $this->getCommDatesModel()->fetchCommDate($arr_post_data['id']);
    				if (!$objRecord)
    				{
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'The requested record could not be located',
    					));
    					return $objResult;
    				}//end if
    				
    				$form = $this->getCommDatesModel()->getCommDatesForm();
    				$form->bind($objRecord);
    				$form->setData($arr_post_data);
    				
    				if ($form->isValid())
    				{
    					$objData = $form->getData();
    					$objData->set('id', $arr_post_data['id']);
    					$objData = $this->getCommDatesModel()->updateCommDate($objData);
    					
    					$objResult = new JsonModel(array(
    						'objData' => (object) $objData->getArrayCopy()	
    					));
    				} else {
    					$objResult = new JsonModel(array(
    						'error' => 1,
    						'response' => 'Form could be validated',
    						'form_messages' => $form->getMessages()
    					));
    				}//end if
    				break;
    				
    			case 'delete-record':
    				//load the record
    				$objRecord = $this->getCommDatesModel()->fetchCommDate($arr_post_data['id']);
    				if (!$objRecord)
    				{
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'The requested record could not be located',
    					));
    					return $objResult;
    				}//end if
    				
    				$this->getCommDatesModel()->deleteCommDate($objRecord->get('id'));
    				$objResult = new JsonModel(array(
    					'objData' => (object) $objRecord->getArrayCopy(),	
    				));
    				break;
    				
    			case 'toggle-record-status':
    				//load the record
    				$objRecord = $this->getCommDatesModel()->fetchCommDate($arr_post_data['id']);
    				if (!$objRecord)
    				{
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'The requested record could not be located',
    					));
    					return $objResult;
    				}//end if
    				
    				$objRecord->set('active', (1 - $objRecord->get('active')));
    				$objRecord = $this->getCommDatesModel()->updateCommDate($objRecord);
    				$objResult = new JsonModel(array(
    					'objData' => (object) $objRecord->getArrayCopy(),	
    				));
    				break;
    				
    			case 'load-admin-form':
    				//load admin form
    				$form = $this->getCommDatesModel()->getCommDatesForm();
    				$objForm = $this->renderSystemAngularFormHelper($form, NULL);
    				
    				$objResult = new JsonModel(array(
    					'objForm' => $objForm,	
    				));
    				break;
    		}//end switch
    	
    		if (!$objResult instanceof \Zend\View\Model\JsonModel)
    		{
    			$objResult = new JsonModel(array(
    					'error' => 1,
    					'response' => 'Data could not be loaded, an unknown problem has occurred',
    			));
    		}//end if
    	
    		return $objResult;
    	} catch (\Exception $e) {
    		$objResult = new JsonModel(array(
    				'error' => 1,
    				'response' => $this->frontControllerErrorHelper()->formatErrors($e),
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
     * Create an instance of the commdates model using the service manager
     * @return \FrontCommsAdmin\Models\FrontCommDatesModel
     */
	private function getCommDatesModel()
	{
		if (!$this->model_commdates)
		{
			$this->model_commdates = $this->getServiceLocator()->get("FrontCommsAdmin\Models\FrontCommDatesModel");
		}//end if

		return $this->model_commdates;
	}//end function

}//end class
