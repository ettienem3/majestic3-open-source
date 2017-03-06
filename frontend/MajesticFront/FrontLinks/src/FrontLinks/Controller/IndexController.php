<?php
namespace FrontLinks\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use FrontCore\Adapters\AbstractCoreActionController;

class IndexController extends AbstractCoreActionController
{
	/**
	 * Container for Links Model instance
	 * @var \FrontLinks\Models\FrontLinksModel
	 */
	private $model_links;

	/**
	 * Container for the Front Behaviours Config Model
	 * @var \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
	 */
	private $model_front_behaviours_config;

    public function indexAction()
    {
     	//load links
     	try {
     		$objLinks = $this->getLinksModel()->fetchLinks($this->params()->fromQuery());
     	} catch (\Exception $e) {
     		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
     		return $this->redirect()->toRoute('home');
     	}//end catch 
     	
     	return array("objLinks" => $objLinks);
    }//end function

    public function appAction()
    {
    	$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
    	if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['links'] != true)
    	{
    		$this->flashMessenger()->addInfoMessage('The requested view is not available');
    		return $this->redirect()->toRoute('front-links');
    	}//end if

     	$this->layout('layout/angular/app');

    	//load the form
    	$form = $this->getLinksModel()->getLinksForm();
    	$objLinks = $this->getLinksModel()->fetchLinks();

    	return array(
    			'form' => $form,
    			'objLinks' => $objLinks,
    	);
    }//end function

    public function ajaxRequestAction()
    {
    	$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
    	if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['links'] != true)
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
	    	switch ($acrq)
	    	{
	    		case 'index':
	    			//load links
	    			$objLinks = $this->getLinksModel()->fetchLinks($arr_params);
	    			$objResult = new JsonModel(array(
	    					'objData' => $objLinks,
	    			));
	    			return $objResult;
	    			break;

	    		case 'get':
	    			$objLink = $this->getLinksModel()->fetchLink((int) $arr_params['id']);
	    			$objResult = new JsonModel(array(
	    					'error' => 0,
	    					'response' => 'Data loaded',
	    					'objData' => $objLink,
	    			));
	    			return $objResult;
	    			break;

	    		case 'toggleStatus':
	    			$objLink = $this->getLinksModel()->fetchLink((int) $arr_params['id']);
	    			$objLink->set("active", (1 - $objLink->get("active")));
	    			$objLink = $this->getLinksModel()->updateLink($objLink);

	    			$objResult = new JsonModel(array(
	    					'error' => 0,
	    					'response' => 'Data saved',
	    					'objData' => $objLink,
	    			));
	    			return $objResult;
	    			break;

	    		case 'create':
	    		case 'edit':
	    			if ($request->isPost())
	    			{
	    				//load the form
	    				$form = $this->getLinksModel()->getLinksForm();
	    				$form->setData($arr_post_data);
	    				if ($form->isValid())
	    				{
	    					if (isset($arr_post_data['id']))
	    					{
	    						$objLink = $this->getLinksModel()->fetchLink($arr_post_data['id']);
								$objLink->set((array) $form->getData());
								$objLink->set('id', $arr_post_data['id']);
								$objLink = $this->getLinksModel()->updateLink($objLink);
	    					} else {
	    						//create link
	    						$arr_data = $form->getData();
	    						$objLink = $this->getLinksModel()->createLink($arr_data);
	    					}//end if

	    					//form is valid
	    					$objResult = new JsonModel(array(
	    							'error' => 0,
	    							'response' => 'Data saved',
	    							'objData' => $objLink,
	    					));
	    					return $objResult;
	    				} else {
	    					//form is invalid
	    					$objResult = new JsonModel(array(
	    							'error' => 1,
	    							'response' => 'Data could not be validated',
	    							'form_errors' => $form->getMessages(),
	    					));
	    					return $objResult;
	    				}//end if
	    			} else {
	    				$objResult = new JsonModel(array(
	    						'error' => 1,
	    						'response' => 'Request type is invalid for operation requested',
	    				));
	    				return $objResult;
	    			}//end if
	    			break;

	    		case 'delete':
	    			$objLink = $this->getLinksModel()->fetchLink((int) $arr_params['id']);
	    			$this->getLinksModel()->deleteLink($objLink->get('id'));
	    			$objResult = new JsonModel(array(
	    					'error' => 0,
	    					'response' => 'Data removed',
	    					'objData' => $objLink,
	    			));
	    			return $objResult;
	    			break;
	    			
	    		case 'load-link-behaviours':
	    			$objLink = $this->getLinksModel()->fetchLink((int) $arr_params['id']);
	    			if ($objLink->get('id') != (int) $arr_params['id'])
	    			{
	    				$objResult = new JsonModel(array(
	    					'error' => 1,
	    					'response' => 'The requested link could not be located',
	    				));
	    				return $objResult;
	    			}//end if
	    			
	    			//set data array to collect behaviours and pass url data to view
	    			$arr_behaviour_params = array(
	    					"link_id" => $arr_params['id'],
	    					"behaviour" => "links",
	    			);
	    			
	    			//load current link behaviours...
	    			$objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);
	    			$arr_behaviours = array();
	    			foreach($objBehaviours as $objBehaviour)
	    			{
	    				if (isset($objBehaviour->id))
	    				{
	    					if (isset($objBehaviour->hypermedia))
	    					{
	    						unset($objBehaviour->hypermedia);	
	    					}//end if
			
	    					$objBehaviour->active = $objBehaviour->active * 1;
	    					$objBehaviour->generic1 = $objBehaviour->generic1 * 1;
	    					
	    					$arr_behaviours[] = $objBehaviour;
	    				}//end if
	    			}//end foreach
	    			
	    			$objResult = new JsonModel(array(
	    				'error' => 0,
	    				'objData' => (object) $arr_behaviours,
	    			));
	    			return $objResult;
	    			break;
	    			
	    		case 'load-link-available-behaviour-actions':
	    			//load behaviours form
	    			$arr_behaviour_params = array(
	    					"link_id" => $arr_params['id'],
	    					"behaviour" => "links",
	    			);
	    			$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm('links', $arr_behaviour_params);
					$objForm = $arr_config_form_data['form'];
					
					$arr_actions = array();
					foreach ($objForm->get('beh_action')->getValueOptions() as $k => $v)
					{
						$arr_actions[] = array('action' => $k, 'label' => $v);	
					}//end foreach
					
					$objResult = new JsonModel(array(
						'error' => 0,
						'objData' => (object) $arr_actions,
					));
					return $objResult;
	    			break;
	    			
	    		case 'load-link-behaviours-additional-data':
	    			//load journeys
	    			$objJourneys = $this->getLinksModel()->fetchAvailableJourneys();
	    			$objStatuses = $this->getLinksModel()->fetchAvailableStatuses();
	    			
	    			$objResult = new JsonModel(array(
	    				'error' => 0,
	    				'objData' => (object) array(
	    					'objJourneys' => $objJourneys,
	    					'objStatuses' => $objStatuses,
	    				),
	    			));
	    			return $objResult;
	    			break;
	    			
	    		case 'create-link-behaviour':    	
	    			try {
		    			//load behaviours form
		    			$arr_behaviour_params = array(
		    					"behaviour" => "__links",
		    					'beh_action' => $arr_post_data['beh_action'],
		    			);
						$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_behaviour_params);

						//populate journeys
						if ($form->has('fk_journey_id'))
						{
							$objJourneys = $this->getLinksModel()->fetchAvailableJourneys();
							$arr_journeys = array();
							foreach ($objJourneys as $objJourney)
							{
								$arr_journeys[$objJourney->id] = $objJourney->journey;
							}//end foreach
							$form->get('fk_journey_id')->setValueOptions($arr_journeys);
						}//end if
						
						//populate statues
						if ($form->has('fk_reg_status_id'))
						{
							$objStatuses = $this->getLinksModel()->fetchAvailableStatuses();
							$arr_statuses = array();
							foreach($objStatuses as $objStatus)
							{
								$arr_statuses[$objStatus->id] = $objStatus->status;	
							}//end foreach
							$form->get('fk_reg_status_id')->setValueOptions($arr_statuses);
						}//end if
						
						$form->setData($arr_post_data);
		    			if ($form->isValid())
		    			{
		    				$arr_form_data = (array) $form->getData();
		    				$arr_form_data['fk_links_id'] = $arr_post_data['fk_links_id'];
		    				$arr_form_data['behaviour'] = '__links';
							$arr_form_data['event_runtime_trigger'] = 'post';
							
		    				$objBehaviour = $this->getFrontBehavioursModel()->createBehaviourAction($arr_form_data);
		    				
		    				$objResult = new JsonModel(array(
		    					'error' => 0,
		    					'objData' => (object) $objBehaviour->getArrayCopy(),
		    				));
		    				return $objResult;
		    			} else {
		    				$objResult = new JsonModel(array(
		    					'error' => 1,
		    					'response' => 'Form could not be validated, ' . print_r($form->getMessages(), TRUE),
		    				));
		    				return $objResult;
		    			}//end if
	    			} catch (\Exception $e) {
	    				$objResult = new JsonModel(array(
	    					'error' => 1,
	    					'response' => $e->getMessage(),
	    				));
	    				return $objResult;
	    			}//end catch
	    			break;
	    			
	    		case 'update-link-behaviour':
	    			try {
	    				$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviourAction($arr_post_data['id']);
	    				
	    				//load behaviours form
	    				$arr_behaviour_params = array(
	    						"behaviour" => "__links",
	    						'beh_action' => $objBehaviour->get('action'),
	    				);
	    				$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_behaviour_params);
	    			
	    				//populate journeys
	    				if ($form->has('fk_journey_id'))
	    				{
	    					$objJourneys = $this->getLinksModel()->fetchAvailableJourneys();
	    					$arr_journeys = array();
	    					foreach ($objJourneys as $objJourney)
	    					{
	    						$arr_journeys[$objJourney->id] = $objJourney->journey;
	    					}//end foreach
	    					$form->get('fk_journey_id')->setValueOptions($arr_journeys);
	    				}//end if
	    			
	    				//populate statues
	    				if ($form->has('fk_reg_status_id'))
	    				{
	    					$objStatuses = $this->getLinksModel()->fetchAvailableStatuses();
	    					$arr_statuses = array();
	    					foreach($objStatuses as $objStatus)
	    					{
	    						$arr_statuses[$objStatus->id] = $objStatus->status;
	    					}//end foreach
	    					$form->get('fk_reg_status_id')->setValueOptions($arr_statuses);
	    				}//end if
	    			
	    				$form->setData($arr_post_data);
	    				if ($form->isValid())
	    				{
	    					$arr_form_data = (array) $form->getData();
							$objBehaviour->set($arr_form_data);
	    						
	    					$objBehaviour = $this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);
	    			
	    					$objResult = new JsonModel(array(
	    							'error' => 0,
	    							'objData' => (object) $objBehaviour->getArrayCopy(),
	    					));
	    					return $objResult;
	    				} else {
	    					$objResult = new JsonModel(array(
	    							'error' => 1,
	    							'response' => 'Form could not be validated, ' . print_r($form->getMessages(), TRUE),
	    					));
	    					return $objResult;
	    				}//end if
	    			} catch (\Exception $e) {
	    				$objResult = new JsonModel(array(
	    						'error' => 1,
	    						'response' => $e->getMessage(),
	    				));
	    				return $objResult;
	    			}//end catch
	    			break;
	    			
	    		case 'toggle-link-behaviour-status':
	    			try {
	    				$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviourAction($arr_post_data['id']);
	    				$objBehaviour->set('active', (1 - $objBehaviour->get('active')));
	    				$this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);
	    				$objResult = new JsonModel(array(
	    					'error' => 0,
	    					'objData' => (object) $objBehaviour->getArrayCopy(),
	    				));
	    				return $objResult;
	    			} catch (\Exception $e) {
	    				$objResult = new JsonModel(array(
	    						'error' => 1,
	    						'response' => 'Behaviour could not be removed',
	    						'raw_response' => $e->getMessage(),
	    				));
	    				return $objResult;
	    			}//end catch
	    			break;
	    			
	    		case 'delete-link-behaviour':
	    			try {
		    			$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviourAction($arr_post_data['id']);
		    			$this->getFrontBehavioursModel()->deleteBehaviourAction($objBehaviour);
		    			
		    			$objResult = new JsonModel(array(
		    				'error' => 0,
		    				'objData' => (object) array(),
		    			));
	    				return $objResult;
	    			} catch (\Exception $e) {
	    				$objResult = new JsonModel(array(
	    					'error' => 1,
	    					'response' => 'Behaviour could not be removed',
	    					'raw_response' => $e->getMessage(),
	    				));
	    				return $objResult;
	    			}//end catch
	    			break;
	    	}//end function
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
     * Create a new link
     * @return multitype:\Zend\Form\Form
     */
    public function createAction()
    {
		$form = $this->getLinksModel()->getLinksForm();
		$request = $this->getRequest();
		if ($request->isPost())
		{
			//set form data
			$form->setData($request->getPost());

			if ($form->isValid())
			{
				try {
					//create the link
					$objLink = $this->getLinksModel()->createLink($form->getData());

					//set success message
					$this->flashMessenger()->addSuccessMessage("Link Created");

					//redirect to index page
					return $this->redirect()->toRoute("front-links");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end function
		}//end if

		return array("form" => $form);
    }//end function

    /**
     * Update an existing link
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Zend\Form\Form
     */
    public function editAction()
    {
    	//get id from route
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set message
    		$this->flashMessenger()->addErrorMessage("Link could not be loaded. Id is not set");
    		return $this->redirect()->toRoute("front-links");
    	}//end if

    	//load the link details
    	$objLink = $this->getLinksModel()->fetchLink($id);

    	//load the form
    	$form = $this->getLinksModel()->getLinksForm();
		//bind data
		$form->bind($objLink);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			//set form data
			$form->setData($request->getPost());

			if ($form->isValid())
			{
				try {
					$objLink = $form->getData();
					$objLink->set("id", $id);
// $this->getLinksModel()->setDelayedProcessingFlag(TRUE);
					$objLink = $this->getLinksModel()->updateLink($objLink);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Link Updated");
					return $this->redirect()->toRoute("front-links");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end if
			}//end if
		}//end if

    	return array(
    			"form" => $form,
    			"objLink" => $objLink,
    	);
    }//end function

    /**
     * Delete and existing link
     */
    public function deleteAction()
    {
		$id = $this->params()->fromRoute("id", "");

		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Link could not be deleted. Id is not set");
			//return to index page
			return $this->redirect()->toRoute("front-links");
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{
				//delete the link
				try {
					$objLink = $this->getLinksModel()->deleteLink($id);

					//set message
					$this->flashMessenger()->addSuccessMessage("Link deleted");
				} catch (\Exception $e) {
					//set error message
    				$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
				}//end catch
			}//end if

			//redirect to index page
			return $this->redirect()->toRoute("front-links");
		}//end if

		//load data
		$objLink = $this->getLinksModel()->fetchLink($id);

		return array(
			"objLink" => $objLink,
		);
    }//end function

    /**
     * Activate or deactivate a link
     */
    public function statusAction()
    {
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Link could not be updated. Id is not set");
    		//return to index page
    		return $this->redirect()->toRoute("front-links");
    	}//end if

    	try {
    		//load the link details
    		$objLink = $this->getLinksModel()->fetchLink($id);
    		$objLink->set("active", (1 - $objLink->get("active")));

    		//update the link
    		$objLink = $this->getLinksModel()->updateLink($objLink);

    		//set success message
    		$this->flashMessenger()->addSuccessMessage("Link Status Updated");
    	} catch ( \Exception $e) {
    		//set message
    		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
    	}//end if

    	//redirect to index page
    	return $this->redirect()->toRoute("front-links");
    }//end function

    public function linksBehavioursAction()
    {
    	//set layout
    	$this->layout("layout/behaviours-view");

    	//set data array to collect behaviours and pass url data to view
    	$arr_behaviour_params = array(
    			"link_id" => $this->params()->fromRoute("id"),
    			"behaviour" => $this->params()->fromQuery("behaviour", "links"),
    	);

    	//load behaviours form
    	$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm($this->params()->fromQuery("behaviour", "links"), $arr_behaviour_params);
    	$form = $arr_config_form_data["form"];
    	$arr_descriptors = $arr_config_form_data["arr_descriptors"];

    	//load current field behaviours...
    	$objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);

    	//load the link details
    	$objLink = $this->getLinksModel()->fetchLink($this->params()->fromRoute("id"));

    	//check if behaviour is being reconfigured
    	if (is_numeric($this->params()->fromQuery("behaviour_id", "")))
    	{
    		$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviour($this->params()->fromQuery("behaviour_id"));
    	} else {
    		$objBehaviour = FALSE;
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			$arr_params = $form->getData();
    			$arr_params["behaviour"] = $this->params()->fromQuery("behaviour", "links");
    			$arr_behaviour_params["beh_action"] = $arr_params["beh_action"];

    			$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_params);

    			//check if a local defined form exists for the behaviour, sometime needed since the api wont render the form correctly
    			$class = "\\FrontBehavioursConfig\\Forms\\Links\\Behaviour" . str_replace(" ", "", ucwords(str_replace("_", " ", $arr_params['beh_action']))) . "Form";

    			if (class_exists($class))
    			{
    				$form = new $class($form);
    			}//end if

    			//assign data to form is behaviour is being reconfigured
    			if ($objBehaviour instanceof \FrontBehaviours\Entities\FrontBehavioursBehaviourConfigEntity)
    			{
    				$form->bind($objBehaviour);
    			}//end if

    			//check if submitted form is the complete behaviour config
    			if ($this->params()->fromPost("setup_complete", 0) == 1)
    			{
    				//revalidate the form
    				$form->setData($request->getPost());
    				if ($form->isValid())
    				{
    					if ($objBehaviour === FALSE)
    					{
    						//set additional params
    						$arr_form_data = $form->getData();
    						$arr_form_data["link_id"] = $objLink->get("id");

    						//create/update the behaviour
    						$objBehaviour = $this->getFrontBehavioursModel()->createBehaviourAction($arr_form_data);

    						//redirect back to the "index" view
    						return $this->redirect()->toUrl($this->url()->fromRoute("front-links", array("action" => "links-behaviours", "id" => $objLink->get("id"))));
    					} else {
    						//set additional params
    						$objBehaviour = $form->getData();
    						$objBehaviour->set("form_id", $this->params()->fromRoute("id"));

    						//update the behaviour
    						$objBehaviour = $this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);

    						//redirect back to the "index" view
    						return $this->redirect()->toUrl($this->url()->fromRoute("front-links", array("action" => "links-behaviours", "id" => $objLink->get("id"))));
    					}//end if
    				}//end if
    			}//end if
    		}//end if
    	}//end if

    	$viewModel = new ViewModel(array(
				//form to add behavours
				"form"      			=> $form,
				//existing behaviours
				"objBehaviours" 		=> $objBehaviours,
				//behaviour params
				"arr_behaviour_params" 	=> $arr_behaviour_params,
				//action descriptions
				"arr_descriptors" 		=> $arr_descriptors,
    			//load form data
    			"objLink"				=> $objLink,
    			//set header
    			"behaviours_header" 	=> "Behaviours configured for <span class=\"text-info\">" . $objLink->get("link") . "</span>",
    	));
    	$viewModel->setTemplate('front-behaviours-config/index/configure-behaviours.phtml');

    	return $viewModel;
    }//end function

    /**
     * Create an instance of the links model using the service manager
     * @return \FrontLinks\Models\FrontLinksModel
     */
    private function getLinksModel()
    {
    	if (!$this->model_links)
    	{
    		$this->model_links = $this->getServiceLocator()->get("FrontLinks\Models\FrontLinksModel");
    	}//end if

    	return $this->model_links;
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
