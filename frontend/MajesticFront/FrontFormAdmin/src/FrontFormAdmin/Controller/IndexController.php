<?php
namespace FrontFormAdmin\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use FrontCore\Adapters\AbstractCoreActionController;

/**
 * Dealing with forms
 * @author ettiene
 *
 */
class IndexController extends AbstractCoreActionController
{
	/**
	 * Container for the Forms admin model
	 * @var \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private $model_forms_admin;

	/**
	 * Container for FrontFormAdminFieldModel
	 * @var \FrontFormAdmin\Models\FrontFieldAdminModel
	 */
	private $model_fields;

	/**
	 * Container for the Front Replace Fields Model
	 * @var \FrontFormAdmin\Models\FrontReplaceFieldsAdminModel
	 */
	private $model_replace_fields;

	/**
	 * Container for the Front Generic Fields Model
	 * @var \FrontFormAdmin\Models\FrontGenericFieldsAdminModel
	 */
	private $model_generic_fields;

	/**
	 * Container for the Front Behaviours Config Model
	 * @var \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
	 */
	private $model_front_behaviours_config;

	/**
	 * Container for the Profilr File Manager
	 * @var \FrontProfileFileManager\Models\FrontProfileFileManagerModel
	 */
	private $model_profile_file_manager;

	/**
	 * Container for the Profile Cache Manager (Default is Redis)
	 * @var \FrontCore\Caches\FrontCachesRedis $model_profile_cache_manager
	 */
	private $model_profile_cache_manager;

	/**
	 * List forms
	 * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
	 */
    public function indexAction()
    {
    	$arr_params = (array) $this->params()->fromQuery();
    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$arr_params = array_merge($arr_params, (array) $request->getPost());
    	}//end foreach

       	$objForms = $this->getFormAdminModel()->fetchForms($arr_params);
       	return array(
       			"objForms" => $objForms,
       			"arr_params" => $arr_params,
       	);
    }//end function

    public function appAction()
    {
    	$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
    	if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['forms-admin'] != true)
    	{
    		$this->flashMessenger()->addInfoMessage('The requested view is not available');
    		return $this->redirect()->toRoute("front-form-admin/form");
    	}//end if

    	$this->layout('layout/angular/app');

    	//check if data is cached
    	$cache_key = __CLASS__ . '-' . __FUNCTION__ . '-data';
    	$arr_data = $this->getProfileCacheManager()->readCacheItem($cache_key, false);
    	if (is_array($arr_data))
    	{
//     		return $arr_data;
    	}//end if

    	//request replace fields
    	$objFields = $this->getReplaceFieldsModel()->fetchReplaceFields(array(), TRUE);
    	if (isset($objFields->hypermedia))
    	{
    		unset($objFields->hypermedia);
    	}//end if

    	//request generic fields
    	$objGenericFields = $this->getGenericFieldsModel()->fetchGenericFields();

    	//categorize the data
    	$arr_replace_fields = array();
    	foreach ($objFields as $objReplaceField)
    	{
    		if (!isset($objReplaceField->category))
    		{
    			continue;
    		}//end if

    		$arr_replace_fields[str_replace(' ', '_', $objReplaceField->category)][] = $objReplaceField;
    	}//end if

    	foreach ($objGenericFields as $objField)
    	{
    		if (!isset($objField->id))
    		{
    			continue;
    		}//end if

    		$arr_replace_fields['Generic_Fields'][] = $objField;
    	}//end foreach

    	//load profile images
     	$objImages = $this->getProfileFileManagerModel()->fetchFiles('images');
     	$arr_images_list = array();
     	foreach ($objImages as $k => $objImage)
     	{
     		if ($objImage->active != '1')
     		{
     			continue;
     		}//end if

     		$arr_images_list[] = $objImage;
     	}//end foreach

     	//cache data
     	$arr_data = array(
			'arr_replace_fields' => json_encode($arr_replace_fields, JSON_FORCE_OBJECT),
    		'arr_profile_images' => json_encode((object) $arr_images_list, JSON_FORCE_OBJECT),
    	);

     	$this->getProfileCacheManager()->setCacheItem($cache_key, $arr_data, array('ttl' => (60 * 10)));

    	return $arr_data;
    }//end function

    public function ajaxRequestAction()
    {
    	$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
    	if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['forms-admin'] != true)
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
    				$objForms = $this->getFormAdminModel()->fetchForms($arr_params);
    				$arr_forms = array();
    				foreach ($objForms as $objForm)
    				{
    					if (!is_numeric($objForm->id))
    					{
    						continue;
    					}//end if

    					$arr_form = array();
    					foreach ($objForm as $field => $value)
    					{
    						if (is_numeric($value))
    						{
    							$value = $value * 1;
    						}//end if

    						$arr_form[$field] = $value;
    					}//end foreach

    					if (count($arr_form) > 0)
    					{
    						$arr_forms[] = $arr_form;
    					}//end if
    				}//end foreach

    				//add hypermedia for pagination
    				$arr_forms['hypermedia'] = $objForms->hypermedia;
    				$objResult = new JsonModel(array(
    					'objData' => (object) $arr_forms,
    				));
    				return $objResult;
    				break;

    			case 'load-admin-form-web':
    				//load form
    				$form = $this->getFormAdminModel()->getFormAdminForm('web');

    				$objForm = $this->renderSystemAngularFormHelper($form, NULL);
    				$objResult = new JsonModel(array(
    						'objData' => $objForm,
    				));
    				return $objResult;
    				break;

    			case 'load-admin-form-viral':
    				//load form
    				$form = $this->getFormAdminModel()->getFormAdminForm('viral');

    				$objForm = $this->renderSystemAngularFormHelper($form, NULL);
    				$objResult = new JsonModel(array(
    						'objData' => $objForm,
    				));
    				return $objResult;
    				break;

    			case 'load-admin-form-cpp':
    			case 'load-admin-form-tracker':
    				switch ($acrq)
    				{
    					case 'load-admin-form-cpp':
    						//load form
    						$form = $this->getFormAdminModel()->getFormAdminForm('cpp');
    						break;

    					case 'load-admin-form-tracker':
    						//load form
    						$form = $this->getFormAdminModel()->getFormAdminForm('tracker');
    						break;
    				}//end switch

    				$objForm = $this->renderSystemAngularFormHelper($form, NULL);
    				$objResult = new JsonModel(array(
    						'objData' => $objForm,
    				));
    				return $objResult;
    				break;

    			case 'load-form-config-data':
    				$objForm = $this->getFormAdminModel()->fetchForm($arr_params['fid']);
    				$arr_form_data = $objForm->getArrayCopy();
    				foreach ($arr_form_data as $key => $value)
    				{
    					if (is_numeric($value))
    					{
    						$arr_form_data[$key] = $value * 1;
    						continue;
    					}//end if
    				}//end foreach

    				$objResult = new JsonModel(array(
    						'objData' => (object) $arr_form_data,
    				));
    				return $objResult;
    				break;

    			case 'update-form-config-data':
					//load admin form
    				$form = $this->getFormAdminModel()->getFormAdminForm($arr_post_data['form_types_behaviour']);

    				//clean some fields
    				foreach ($arr_post_data as $k => $v)
    				{
    					switch ($k)
    					{
    						case 'template_id':
    							if ($v == 0 || $v == '' || $v == '?')
    							{
    								$arr_post_data[$k] = '';
    							}//end if
    							break;

    						case 'viral_populate':
    							if ($v == '?' || $v == '0')
    							{
    								$arr_post_data[$k] = '';
    							}//end if
    							break;
    					}//end switch

    					if (is_null($v) || $v === null)
    					{
    						$arr_post_data[$k] = '';
    					}//end if
    				}//end foreach

    				//load the form
    				$objForm = $this->getFormAdminModel()->fetchForm($arr_post_data['id']);

    				//do some last minute form fields manipulation based on form type
    				switch ($objForm->get('form_types_behaviour'))
    				{
    					case '__viral':

    						break;

    					default:
    						if ($form->has('viral_referrals'))
    						{
    							if (isset($arr_post_data['viral_referrals']) && $arr_post_data['viral_referrals'] == 0)
    							{
    								$form->remove('viral_referrals');
    							}//end if

    							if (!isset($arr_post_data['viral_referrals']))
    							{
    								$form->remove('viral_referrals');
    							}//end if
    						}//end if
    						break;
    				}//end switch

    				$form->bind($objForm);
    				$form->setData($arr_post_data);
    				if ($form->isValid())
    				{
    					try {
							//update the form
	    					$objForm = $form->getData();
	    					$objForm->set("id", $arr_post_data['id']);
	    					$objForm->set("fk_form_type_id", $arr_post_data['fk_form_type_id']);
	    					$objForm = $this->getFormAdminModel()->editForm($objForm);

	    					$objResult = new JsonModel(array(
	    							'error' => 0,
	    							'response' => '',
	    							'objForm' => (object) $objForm->getArrayCopy(),
	    					));
	    					return $objResult;
    					} catch (\Exception $e) {
    						//set error message
    						$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    						$objResult = new JsonModel(array(
    								'error' => 1,
    								'response' => $e->getMessage(),
    								'form_messages' => (object) $form->getMessages(),
    						));
    						return $objResult;
    					}//end catch
    				} else {
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'Form validation failed',
    							'form_messages' => (object) $form->getMessages(),
    					));
    					return $objResult;
    				}//end if
    				break;

    			case 'load-form-statistics':
    				$objForm = $this->getFormAdminModel()->fetchForm($arr_params['fid']);

    				$objData = $this->getFormAdminModel()->fetchFormStatistics($arr_params['fid']);
    				$objResult = new JsonModel(array(
    					'objData' => (object) array(
    							'objForm' => (object) $objForm->getArrayCopy(),
    							'objStats' => $objData,
    					)
    				));
    				return $objResult;
    				break;

    			case 'create-web-form':
    			case 'create-viral-form':
    				$form = $this->getFormAdminModel()->getFormAdminForm('web');

    				//add missing fields to data
    				foreach ($form->getElements() as $objElement)
    				{
    					if (!isset($arr_post_data[$objElement->getName()]))
    					{
    						$arr_post_data[$objElement->getName()] = '';
    					}//end if
    				}//end foreach

    				$form->setData($arr_post_data);
    				if ($form->isValid())
    				{
    					//create the form
    					$arr_data = (array) $form->getData();
    					$objFormData = $this->getFormAdminModel()->createForm($arr_data);
    					$objResult = new JsonModel(array(
    						'objData' => $objFormData,
    					));
    					return $objResult;
    				} else {
    					return new JsonModel($this->formatAngularFormErrors($form));
    				}//end if

    				$objResult = new JsonModel(array(
    					'objData' => $arr_post_data,
    				));
    				return $objResult;
    				break;

    			case 'create-cpp-form':
    			case 'create-tracker-form':
    				switch ($acrq)
    				{
    					case 'create-cpp-form':
    						$form = $this->getFormAdminModel()->getFormAdminForm('cpp');
    						break;

    					case 'create-tracker-form':
    						$form = $this->getFormAdminModel()->getFormAdminForm('tracker');
    						break;
    				}//end switch


    				$form->setData($arr_post_data);
    				if ($form->isValid())
    				{
    					//create the form
    					$arr_data = (array) $form->getData();
    					$objFormData = $this->getFormAdminModel()->createForm($arr_data);
    					$objResult = new JsonModel(array(
    							'objData' => $objFormData,
    					));
    					return $objResult;
    				} else {
    					return new JsonModel($this->formatAngularFormErrors($form));
    				}//end if

    				$objResult = new JsonModel(array(
    						'objData' => $arr_post_data,
    				));
    				return $objResult;

    				break;

    			case 'update-form-status':
    				$objForm = $this->getFormAdminModel()->getForm($arr_post_data['fid']);
					$objForm->set('active', (1 - $objForm->get('active')));
    				$objForm = $this->getFormAdminModel()->updateFormStatus($objForm);

    				$objResult = new JsonModel(array(
    					'objData' => $objForm->getArrayCopy(),
    				));
    				return $objResult;
    				break;

    			case 'delete-form':
    				try {
	    				$objForm = $this->getFormAdminModel()->fetchForm($arr_params['fid']);
	    				$this->getFormAdminModel()->deleteForm($objForm->get('id'));
	    				$objResult = new JsonModel(array(
	    						'objData' => $objForm->getArrayCopy(),
	    				));
    				} catch (\Exception $e) {
    					$objResult = new JsonModel(array(
    						'error' => 1,
    						'response' => $this->frontControllerErrorHelper()->formatErrors($e),
    						'raw_response' => $e->getMessage(),
    					));
    				}//end catch

    				return $objResult;
    				break;

    			case 'clear-form-cache':
    				$objForm = $this->getFormAdminModel()->fetchForm($arr_post_data['fid']);
    				
    				//standard fields
    				$cache_key = __CLASS__ . '-angular-form-all-std-fields-' . $objForm->get('id');
    				$arr_std_fields = $this->getProfileCacheManager()->setCacheItem($cache_key, false);
    				
    				//custom fields
    				$cache_key = __CLASS__ . '-angular-form-all-custom-fields-' . $objForm->get('id');
    				$arr_custom_fields = $this->getProfileCacheManager()->setCacheItem($cache_key, false);
    				
    				//external cached form
    				$cache_key = "external-form-" . md5($_SERVER["HTTP_HOST"]) . "-" . $objForm->get('id');
    				$this->getProfileCacheManager()->setCacheItem($cache_key, false);
    				break;

    			case 'load-form-behaviours':
    				//load form data
    				$objFormData = $this->getFormAdminModel()->fetchForm($arr_params['fid']);
					$arr_form_data = array();
					foreach ($objFormData->getArrayCopy() as $field => $value)
					{
						if (is_numeric($value))
						{
							$value = ($value * 1);
						}//end if

						$arr_form_data[$field] = $value;
					}//end foreach

    				//set data array to collect behaviours and pass url data to view
    				$arr_behaviour_params = array(
    						"form_id" => $arr_params['fid'],
    						"behaviour" => 'form',
    				);

    				//load behaviours form
    				$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm('form', $arr_behaviour_params);
    				$form = $arr_config_form_data["form"];
    				$arr_descriptors = $arr_config_form_data["arr_descriptors"];

    				//load current field behaviours...
    				$arr_behaviours = array();
    				$objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);
    				if (is_object($objBehaviours) || is_array($objBehaviours))
    				{
    					foreach ($objBehaviours as $objBehaviour)
    					{
    						foreach ($objBehaviour as $field => $value)
    						{
    							if (is_numeric($value))
    							{
    								$objBehaviour->$field = ($value * 1);
    							}//end if
    						}//end if

    						$arr_behaviours[] = $objBehaviour;
    					}//end foreach
    				}//end if

    				$objResult = new JsonModel(array(
    					'objData' => (object) array(
		    				//form to add behavours
							"objFormData"      		=> (object) $arr_form_data,
							//existing behaviours
							"objBehaviours" 		=> (object) $arr_behaviours,
							//behaviour params
							"arr_behaviour_params" 	=> $arr_behaviour_params,
							//action descriptions
							"arr_descriptors" 		=> $arr_descriptors

    					)
    				));
    				return $objResult;
    				break;

    			case 'load-form-behaviour-options':
    				//set data array to collect behaviours and pass url data to view
    				$arr_behaviour_params = array(
    						"form_id" => $arr_params['fid'],
    						"behaviour" => $arr_params['behaviour'],
    				);

    				if ($arr_behaviour_params['behaviour'] == '')
    				{
    					$arr_behaviour_params['behaviour'] = 'form';
    				}//end if

    				//load behaviours form
    				$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm($arr_behaviour_params['behaviour'], $arr_behaviour_params);
    				$form = $arr_config_form_data["form"];
    				$config_form_behaviour_options = $form->get('beh_action')->getValueOptions();
    				$arr_descriptors = $arr_config_form_data["arr_descriptors"];

    				//load current form behaviours...
    				$objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);

    				//load form data
    				$objFormData = $this->getFormAdminModel()->fetchForm($arr_params['fid']);

    				$objResult = new JsonModel(array(
    						'objData' => (object) array(
    								'objBehaviours' => $objBehaviours,
    								'arr_descriptors' => $arr_descriptors,
    								'config_form_behaviour_options' => $config_form_behaviour_options,
    								'objFormData' => $objFormData->getArrayCopy(),
    						)
    				));
    				return $objResult;
    				break;

    			case 'load-form-behaviour-config-form':
    				//set data array to collect behaviours and pass url data to view
    				$arr_behaviour_params = array(
    						"form_id" => $arr_params['fid'],
    						"behaviour" => $arr_params['behaviour'],
    						'beh_action' => $arr_params['behaviour_action'],
    				);

    				if ($arr_behaviour_params['behaviour'] == '')
    				{
    					$arr_behaviour_params['behaviour'] = 'form';
    				}//end if

    				//load behaviours form
    				$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_behaviour_params);
    				$objForm = $this->renderSystemAngularFormHelper($form, NULL);

    				$objResult = new JsonModel(array(
    					'objData' => (object) array('objConfigForm' => $objForm),
    				));
    				return $objResult;
    				break;

    			case 'process-form-behaviour-config-form':
    				//set data array to collect behaviours and pass url data to view
    				$arr_behaviour_params = array(
    						"form_id" => (int) $arr_post_data['form_id'],
    						"behaviour" => $arr_post_data['behaviour'],
    				);

    				//load behaviours form
    				$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm($arr_post_data['behaviour'], $arr_behaviour_params);
    				$form = $arr_config_form_data["form"];
    				$arr_descriptors = $arr_config_form_data["arr_descriptors"];

    				//load current form behaviours...
    				$objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);

    				//load the form data
    				$objForm = $this->getFormAdminModel()->getForm($arr_post_data['form_id']);

    				//check if behaviour is being reconfigured
    				if (is_numeric($arr_post_data["behaviour_id"]))
    				{
    					$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviourAction($arr_post_data["behaviour_id"]);
    				} else {
    					$objBehaviour = FALSE;
    				}//end if

    				if (!isset($arr_post_data['content']))
    				{
    					$arr_post_data['content'] = '';
    				}//end if

    				if (!isset($arr_post_data['event_runtime_trigger']))
    				{
    					$arr_post_data['event_runtime_trigger'] = 'post';
    				}//end if

    				$form->setData($arr_post_data);

    				if ($form->isValid())
    				{
    					$arr_params = $form->getData();
    					$arr_params["behaviour"] = $arr_post_data['behaviour'];
    					$arr_behaviour_params["beh_action"] = $arr_params["beh_action"];

    					$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_params);

    					//check if a local defined form exists for the behaviour, sometime needed since the api wont render the form correctly
    					$class = "\\FrontBehavioursConfig\\Forms\\Forms\\Behaviour" . str_replace(" ", "", ucwords(str_replace("_", " ", $arr_params['beh_action']))) . "Form";

    					if (class_exists($class))
    					{
    						$form = new $class($form);
    					}//end if

    					//assign data to form if behaviour is being reconfigured
    					if ($objBehaviour instanceof \FrontBehavioursConfig\Entities\FrontBehavioursBehaviourConfigEntity)
    					{
    						$form->bind($objBehaviour);
    					}//end if

    					//check if submitted form is the complete behaviour config
    					if ($arr_post_data["setup_complete"] == 1)
    					{
    						//revalidate the form
    						$form->setData($arr_post_data);
    						if ($form->isValid())
    						{
    							if ($objBehaviour === FALSE)
    							{
    								//set additional params
    								$arr_form_data = $form->getData();
    								$arr_form_data["form_id"] = $arr_post_data['form_id'];

    								//create the behaviour
    								$objBehaviour = $this->getFrontBehavioursModel()->createBehaviourAction($arr_form_data);
    								if ($objBehaviour instanceof \FrontBehavioursConfig\Entities\FrontBehavioursBehaviourConfigEntity)
    								{
    									$objBehaviour = (object) $objBehaviour->getArrayCopy();
    								}//end if

    								$objResult = new JsonModel(array(
    										'objData' => (object) array(
    												'objBehaviour' => $objBehaviour,
    										),
    								));
    								return $objResult;
    							} else {
    								//set additional params
    								$objBehaviour = $form->getData();
    								$objBehaviour->set("form_id", $arr_post_data['form_id']);
    								$objBehaviour->set('id', $arr_post_data["behaviour_id"]);

    								//update the behaviour
    								$objBehaviour = $this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);
    								if ($objBehaviour instanceof \FrontBehavioursConfig\Entities\FrontBehavioursBehaviourConfigEntity)
    								{
    									$objBehaviour = (object) $objBehaviour->getArrayCopy();
    								}//end if

    								$objResult = new JsonModel(array(
    										'objData' => (object) array(
    												'objBehaviour' => $objBehaviour,
    										),
    								));
    								return $objResult;
    							}//end if
    						}//end if
    					}//end if
    				} else {
    					return new JsonModel($this->formatAngularFormErrors($form));
    				}//end if

    				$objResult = new JsonModel(array(
    						'error' => 1,
    						'response' => 'Behaviour could not be set',
    				));
    				return $objResult;
    				break;

    			case 'delete-form-configured-behaviour':
    				//load behaviour
    				$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviourAction($arr_params['behaviour_id']);
					$this->getFrontBehavioursModel()->deleteBehaviourAction($objBehaviour);

					$objResult = new JsonModel(array(
						'objData' => (object) $objBehaviour->getArrayCopy(),
					));
					return $objResult;
    				break;

    			case 'load-form-allocated-fields':
					//load form details
					$objForm = $this->getFormAdminModel()->getForm($arr_params['fid']);

					//load fields allocated to the form
					$arr_form_fields = array();
					$arr_form_std_fields = array();
					$arr_form_custom_field = array();

					foreach ($objForm->getFormFieldEntities() as $objField)
					{
						if (is_object($objField) && method_exists($objField, 'getArrayCopy'))
						{
							$arr_field = $objField->getArrayCopy();
							if (is_numeric($objField->get("fields_custom_id")))
							{
								$arr_form_custom_field[$objField->get("fields_custom_id")] = TRUE;
								$arr_field['fields_field'] = $objField->get("fields_custom_field");
								$arr_field['fields_description'] = $objField->get("description");
								$arr_field['fields_field_type'] = $objField->get("fields_custom_field_type");
							} else {
								$arr_form_std_fields[$objField->get("fields_std_id")] = TRUE;
								$arr_field['fields_field'] = $objField->get("fields_std_field");
								$arr_field['fields_description'] = $objField->get("description");
								$arr_field['fields_field_type'] = $objField->get("fields_std_field_type");
							}//end if

							$arr_form_fields[] = $arr_field;
						}//end if
					}//end foreach

					//load standard fields
					$cache_key = __CLASS__ . '-angular-form-all-std-fields-' . $objForm->get('id');
					$arr_std_fields = $this->getProfileCacheManager()->readCacheItem($cache_key, false);
					if (!$arr_std_fields)
					{
						$objStandardFields = $this->getFieldsModel()->fetchStandardFields(array("active" => 1));
						$arr_std_fields = array();
						foreach ($objStandardFields as $objField)
						{
							if (is_object($objField) && method_exists($objField, 'getArrayCopy') && !isset($arr_form_std_fields[$objField->get('id')]))
							{
								$arr_std_fields[] = $objField->getArrayCopy();
							}//end if
						}//end foreach

						//cache data
						$this->getProfileCacheManager()->setCacheItem($cache_key, $arr_std_fields, array('ttl' => (60 * 60)));
					}//end if

					//load custom fields
					$cache_key = __CLASS__ . '-angular-form-all-custom-fields-' . $objForm->get('id');
					$arr_custom_fields = $this->getProfileCacheManager()->readCacheItem($cache_key, false);
					if (!$arr_custom_fields)
					{
						$objCustomFields = $this->getFieldsModel()->fetchCustomFields(array('qp_limit' => 'all_force'));
						$arr_custom_fields = array();
						foreach ($objCustomFields as $objField)
						{
							if (is_object($objField) && method_exists($objField, 'getArrayCopy') && !isset($arr_form_custom_field[$objField->get('id')]))
							{
								$arr_custom_fields[] = $objField->getArrayCopy();
							}//end if
						}//end foreach

						//cache data
						$this->getProfileCacheManager()->setCacheItem($cache_key, $arr_custom_fields, array('ttl' => (60 * 10)));
					}//end if

					return new JsonModel(array(
						'objData' => (object) array('objForm' => (object) $objForm->getArrayCopy(), 'objFormFields' => (object) $arr_form_fields, 'objStandardFields' => (object) $arr_std_fields, 'objCustomFields' => (object) $arr_custom_fields),
					));
    				break;

    			case 'load-form-field-admin-form':
					$form = $this->getFormAdminModel()->getFormFieldAdminForm();

					$objForm = $this->renderSystemAngularFormHelper($form, NULL);
					$objResult = new JsonModel(array(
							'objData' => $objForm,
					));
					return $objResult;
    				break;

    			case 'allocate-field-to-form':
    				//gather params
    				$form_id = $arr_post_data['fid'];
    				$field_type = $arr_post_data['field_type'];
    				$field_id = $arr_post_data['field_id'];

    				$objForm = $this->getFormAdminModel()->fetchForm($form_id);
    				$form = $this->getFormAdminModel()->getFormFieldAdminForm();
    				$form->setData($arr_post_data);
    				if ($form->isValid())
    				{
    					switch (strtolower($field_type))
    					{
    						case "standard":
    							$objField = $this->getFieldsModel()->getStandardField($field_id);
    							break;

    						case "custom":
    							$objField = $this->getFieldsModel()->getCustomField($field_id);
    							break;
    					}//end switch

    					//create a new field entity
    					$arr_data = $form->getData();
    					$objFormField = $this->getServiceLocator()->get("FrontFormAdmin\Entities\FrontFormAdminFormFieldEntity");
    					$objField->set($arr_data);
    					$objFormField->set($objField->getArrayCopy());
    					$objFormField = $this->getFormAdminModel()->allocateFieldtoForm($objFormField, $objForm, $field_type);

    					//request the field to display new entry
    					//load field data
    					$objField = $this->getFormAdminModel()->getFormField($form_id, $field_id, $field_type);

    					//add field type to entity
    					$objField->set("url_field_type", strtolower($field_type));

    					$arr_field = $objField->getArrayCopy();
    					if (is_numeric($objField->get("fields_custom_id")))
    					{
    						$arr_field['fields_field'] = $objField->get("fields_custom_field");
    						$arr_field['fields_description'] = $objField->get("description");
    						$arr_field['fields_field_type'] = $objField->get("fields_custom_field_type");
    					} else {
    						$arr_field['fields_field'] = $objField->get("fields_std_field");
    						$arr_field['fields_description'] = $objField->get("description");
    						$arr_field['fields_field_type'] = $objField->get("fields_std_field_type");
    					}//end if

    					return new JsonModel(array(
    						'objData' => $arr_field,
    					));
    				} else {
						return new JsonModel($this->formatAngularFormErrors($form));
    				}//end if
    				break;

    			case 'update-field-allocated-to-form':
    				//load form details
    				$objForm = $this->getFormAdminModel()->getForm($arr_post_data['fid']);

    				//load field data
    				$objField = $this->getFormAdminModel()->getFormField($arr_post_data['fid'], $arr_post_data['field_id'], $arr_post_data['field_type']);

    				//add field type to entity
    				$objField->set("url_field_type", strtolower($arr_post_data['field_type']));

    				//load form
    				$form = $this->getFormAdminModel()->getFormFieldAdminForm();
    				$form->setData($arr_post_data);
    				if ($form->isValid())
    				{
    					$arr_data = $form->getData();
    					$objField->set($arr_data);
    					//update the field
    					$objFormField = $this->getFormAdminModel()->updateFormField($objField);

    					$objResult = new JsonModel(array(
    							'objData' => $objFormField,
    					));
    					return $objResult;
    				} else {
						return new JsonModel($this->formatAngularFormErrors($form));
    				}//end if
    				break;

    			case 'update-form-field-status':
    				//load form details
    				$objForm = $this->getFormAdminModel()->getForm($arr_post_data['fid']);

    				//load field data
    				$objField = $this->getFormAdminModel()->getFormField($arr_post_data['fid'], $arr_post_data['field_id'], $arr_post_data['field_type']);

    				//add field type to entity
    				$objField->set("url_field_type", strtolower($arr_post_data['field_type']));

    				//load form
    				$form = $this->getFormAdminModel()->getFormFieldAdminForm();
    				$form->setData($arr_post_data);
    				if ($form->isValid())
    				{
    					$arr_data = $form->getData();
    					$objField->set($arr_data);

    					//update the field
    					$objField = $this->getFormAdminModel()->updateFormField($objField);
    					$arr_field = $objField->getArrayCopy();
    					if (is_numeric($objField->get("fields_custom_id")))
    					{
    						$arr_field['fields_field'] = $objField->get("fields_custom_field");
    						$arr_field['fields_description'] = $objField->get("description");
    						$arr_field['fields_field_type'] = $objField->get("fields_custom_field_type");
    					} else {
    						$arr_field['fields_field'] = $objField->get("fields_std_field");
    						$arr_field['fields_description'] = $objField->get("description");
    						$arr_field['fields_field_type'] = $objField->get("fields_std_field_type");
    					}//end if

    					$objResult = new JsonModel(array(
    							'objData' => $arr_field,
    					));
    					return $objResult;
    				} else {
    					return new JsonModel($this->formatAngularFormErrors($form));
    				}//end if
    				break;

    			case 'update-form-field-order':
    				//load form details
    				$objForm = $this->getFormAdminModel()->getForm($arr_post_data['fid']);

    				$arr_submit_data = array();
    				foreach ($arr_post_data['data'] as $key => $arr_field)
    				{
    					//create array with data
    					$arr_submit_data['fields'][] = array(
    							'field_id' => $arr_field['id'],
    							'field_order' => ($key + 1),
    					);
    				}//end foreach

    				try {
    					//save the data
    					$objField = $this->getFormAdminModel()->updateFormFieldsOrder($arr_post_data['fid'], $arr_submit_data);

    					$objResult = new JsonModel(array(
    						'objData' => $objField->data
    					));
    					return $objResult;
    				} catch (\Exception $e) {
						$objResult = new JsonModel(array(
							'error' => 1,
							'response' => $this->frontControllerErrorHelper()->formatErrors($e),
							'raw_response' => $e->getMessage(),
						));
						return $objResult;
    				}//end catch
    				break;

    			case 'remove-form-field':
    				//load field data 				
    				$objField = $this->getFormAdminModel()->getFormField($arr_post_data['form_id'], $arr_post_data['field_id'], $arr_post_data['field_type']);

    				//add field type to entity
    				$objField->set("url_field_type", strtolower($arr_post_data['field_type']));

    				try {
	    				//remove the field
	    				$r = $this->getFormAdminModel()->removeFormField($objField);
	
	    				//clear form cached field data
	    				//standard fields
	    				$cache_key = __CLASS__ . '-angular-form-all-std-fields-' . $arr_post_data['form_id'];
	    				$this->getProfileCacheManager()->setCacheItem($cache_key, false);
	    				
	    				//custom fields
	    				$cache_key = __CLASS__ . '-angular-form-all-custom-fields-' . $arr_post_data['form_id'];
	    				$this->getProfileCacheManager()->setCacheItem($cache_key, false);
	    				
	    				$objResult = new JsonModel(array(
	    					'objData' => $r,
	    				));
	    				return $objResult;
    				} catch (\Exception $e) {
    					$objResult = new JsonModel(array(
    						'error' => 1,
    						'response' => $this->frontControllerErrorHelper()->formatErrors($e),
    						'raw_response' => $e->getMessage(),
    					));
    					return $objResult;
    				}//end catch
    				
    				break;

    			case 'load-form-field-behaviours':
    				//set data array to collect behaviours and pass url data to view
    				$arr_behaviour_params = array(
    						"form_id" => $arr_params["form_id"],
    						"field_id" => $arr_params["fields_all_id"],
    						"behaviour" => "form_fields",
    				);

    				//load behaviours form
    				try {
    					$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm("form_fields", $arr_behaviour_params);
    				} catch (\Exception $e) {
						$objResult = new JsonModel(array(
							'objData' => (object) array(),
						));
						return $objResult;
    				}//end catch

    				//load current field behaviours...
    				$objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);

    				//load fields available of form
    				$objForm = $this->getFormAdminModel()->getForm($arr_params['form_id']);

    				//extract field data
    				foreach ($objForm->getFormFieldEntities() as $objElement)
    				{
    					if ($objElement->get("id") == (int) $this->params()->fromQuery("fields_all_id"))
    					{
    						$objFormFieldElement = $objElement;
    						break;
    					}//end if
    				}//end foreach

    				unset($objBehaviours->hypermedia);
					$objResult = new JsonModel(array(
							'objData' => (object) array(
									//existing behaviours
									"objBehaviours" 		=> $objBehaviours,
									//behaviour params
									"arr_behaviour_params" 	=> $arr_behaviour_params,
								)
					));
					return $objResult;
    				break;

    			case 'create-form-field-behaviour-admin-form-actions':
    				//set data array to collect behaviours and pass url data to view
    				$arr_behaviour_params = array(
    						"form_id" => $arr_params["form_id"],
    						"field_id" => $arr_params["fields_all_id"],
    						"behaviour" => "form_fields",
    				);

    				//load behaviours form
    				try {
    					$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm("form_fields", $arr_behaviour_params);
    				} catch (\Exception $e) {
    					$objResult = new JsonModel(array(
    							'objData' => (object) array(),
    					));
    					return $objResult;
    				}//end catch

    				$form = $arr_config_form_data["form"];
    				$arr_actions = $form->get('beh_action')->getValueOptions();

    				$objResult = new JsonModel(array(
    						'objData' => (object) array(
    								'arr_behaviour_actions' => $arr_actions,
    						),
    				));
    				return $objResult;
    				break;

    			case 'create-form-field-behaviour-admin-form':
    				//set data array to collect behaviours and pass url data to view
    				$arr_behaviour_params = array(
    						"form_id" => $arr_params["form_id"],
    						"field_id" => $arr_params["fields_all_id"],
    						"behaviour" => "form_fields",
    						'beh_action' => $arr_params['beh_action'],
    				);

    				//load behaviours form
    				try {
    					$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm("form_fields", $arr_behaviour_params);
    				} catch (\Exception $e) {
    					$objResult = new JsonModel(array(
    							'objData' => (object) array(),
    					));
    					return $objResult;
    				}//end catch

    				$form = $arr_config_form_data["form"];
    				$objForm = $this->renderSystemAngularFormHelper($form, NULL);

    				$objResult = new JsonModel(array(
    					'objData' => (object) array(
    						'objForm' => $objForm,
    					),
    				));
    				return $objResult;
    				break;

    			case 'process-form-field-behaviour-data':
    				//set data array to collect behaviours and pass url data to view
    				$arr_behaviour_params = array(
    						"form_id" => $arr_post_data["form_id"],
    						"field_id" => $arr_post_data["fields_all_id"],
    						"behaviour" => "form_fields",
    						'beh_action' => $arr_post_data['beh_action'],
    				);

    				//load behaviours form
    				try {
    					$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm("form_fields", $arr_behaviour_params);
    				} catch (\Exception $e) {
    					$objResult = new JsonModel(array(
    							'objData' => (object) array(),
    					));
    					return $objResult;
    				}//end catch

    				$form = $arr_config_form_data["form"];
    				$form->setData($arr_post_data);
    				if ($form->isValid())
    				{
    					$arr_form_data = $form->getData();
						$arr_form_data['beh_action'] = $arr_post_data['beh_action'];
						$arr_form_data['behaviour'] = 'form_fields';
						$arr_form_data['form_id'] = (int) $arr_post_data["form_id"];
						$arr_form_data['field_id'] = (int) $arr_post_data['field_id'];
						if (!isset($arr_form_data['active']))
						{
							$arr_form_data['active'] = 0;
						}//end if

						if (isset($arr_post_data['event_runtime_trigger']))
						{
							switch (strtolower($arr_post_data['event_runtime_trigger']))
							{
								default:
									$arr_form_data['event_runtime_trigger'] = 'post';
									break;

								case 'post':
								case 'pre':
									$arr_form_data['event_runtime_trigger'] = strtolower($arr_post_data['event_runtime_trigger']);
									break;
							}//end switch
						} else {
							$arr_form_data['event_runtime_trigger'] = 'post';
						}//end if

						try {
							if (is_numeric($arr_post_data['behaviour_id']))
							{
								//update behaviour
								$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviourAction($arr_post_data['behaviour_id']);
								$objBehaviour->set($arr_form_data);
								$objBehaviour = $this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);

								$objResult = new JsonModel(array(
										'objData' => (object) array(
												'objBehaviour' => (object) $objBehaviour->getArrayCopy()
										),
								));
								return $objResult;
							} else {
								//create behaviour
								$objBehaviour = $this->getFrontBehavioursModel()->createBehaviourAction($arr_form_data);

								$objResult = new JsonModel(array(
									'objData' => (object) array(
										'objBehaviour' => (object) $objBehaviour->getArrayCopy()
									),
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
    				} else {
						return new JsonModel($this->formatAngularFormErrors($form));
    				}//end if
    				break;

    			case 'delete-form-field-behaviour':
    				//load behaviour
    				$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviourAction($arr_params['behaviour_id']);
    				$this->getFrontBehavioursModel()->deleteBehaviourAction($objBehaviour);

    				$objResult = new JsonModel(array(
    						'objData' => (object) $objBehaviour->getArrayCopy(),
    				));
    				return $objResult;
    				break;

    			case 'load-form-summary':
    				//load form data
    				$objFormData = $this->getFormAdminModel()->fetchForm($arr_params['fid']);

					//load fields allocated to the form
					$arr_form_fields = array();
					foreach ($objFormData->getFormFieldEntities() as $objField)
					{
						if (is_object($objField) && method_exists($objField, 'getArrayCopy'))
						{
							$arr_field = $objField->getArrayCopy();
							if (is_numeric($objField->get("fields_custom_id")))
							{
								$arr_field['fields_field'] = $objField->get("fields_custom_field");
								$arr_field['fields_description'] = $objField->get("description");
								$arr_field['fields_field_type'] = $objField->get("fields_custom_field_type");
							} else {
								$arr_field['fields_field'] = $objField->get("fields_std_field");
								$arr_field['fields_description'] = $objField->get("description");
								$arr_field['fields_field_type'] = $objField->get("fields_std_field_type");
							}//end if

							$arr_form_fields[] = $arr_field;
						}//end if
					}//end foreach

					//load form behaviours
					//set data array to collect behaviours and pass url data to view
					$arr_behaviour_params = array(
							"form_id" => $arr_params['fid'],
							"behaviour" => 'form',
					);

					//load current form behaviours...
					$objFormBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);
					if (is_object($objFormBehaviours) && isset($objFormBehaviours->hypermedia))
					{
						unset($objFormBehaviours->hypermedia);
					}//end if

					//load form field behaviours
					$arr_form_field_behaviours = array();
					foreach ($objFormData->getFormFieldEntities() as $objField)
					{
						$arr_field = $objField->getArrayCopy();
						if (is_numeric($objField->get("fields_custom_id")))
						{
							$arr_field['fields_field'] = $objField->get("fields_custom_field");
							$arr_field['fields_description'] = $objField->get("description");
							$arr_field['fields_field_type'] = $objField->get("fields_custom_field_type");
						} else {
							$arr_field['fields_field'] = $objField->get("fields_std_field");
							$arr_field['fields_description'] = $objField->get("description");
							$arr_field['fields_field_type'] = $objField->get("fields_std_field_type");
						}//end if

						//load form field behaviours
						$arr_behaviour_params = array(
								"form_id" => $arr_params["fid"],
								"field_id" => $objField->get('id'),
								"behaviour" => "form_fields",
						);

						//load current field behaviours...
						$objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);

						if (is_object($objBehaviours))
						{
							if (isset($objBehaviours->hypermedia))
							{
								unset($objBehaviours->hypermedia);
							}//end if

 							if (count((array) $objBehaviours) > 0)
 							{
 								foreach ($objBehaviours as $objBehaviour)
 								{
 									$arr_form_field_behaviours[] = (object) array('objField' => (object) $arr_field, 'objBehaviour' => $objBehaviour);
 								}//end foreach
 							}//end if
						}//end if
					}//end foreach

    				$objResult = new JsonModel(array(
    						'objData' => (object) array(
    								'objForm' => (object) $objFormData->getArrayCopy(),
    								'objFormFields' => (object) $arr_form_fields,
    								'objFormBehaviours' => $objFormBehaviours,
    								'objFormFieldBehaviours' => (object) $arr_form_field_behaviours
    						),
    				));
    				return $objResult;
    				break;
    		}//end switch
    	} catch (\Exception $e) {
    		$arr_return = array(
    			'error' => 1,
    			'response' => $e->getMessage(),
    		);

    		if (isset($form) && method_exists($form, 'getMessages'))
    		{
    			$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			$arr_return['form_messages'] = $form->getMessages();
    		}//end if

    		$objResponse = new JsonModel($arr_return);

    		return $objResponse;
    	}//end catch

    	$objResponse = new JsonModel(array(
    			'error' => 1,
    			'response' => 'An invalid request has been received',
    	));

    	return $objResponse;
    }//end function

    public function ajaxSearchValuesAction()
    {
    	try {
    		switch($this->params()->fromQuery("param"))
    		{
    			case "forms_type_id":
					//load form types
    				$form = $this->getFormAdminModel()->getFormAdminForm();

    				$arr_form_types = $form->get("fk_form_type_id")->getValueOptions();
    				foreach ($arr_form_types as $key => $value)
    				{
    					$arr_data[] = array("id" => $key, "val" => $value);
    				}//end foreach
    				break;
    		}//end switch
    	} catch (\Exception $e) {
    		echo json_encode(array(
    				"error" => 1,
    				"response" => $e->getMessage(),
    		));
    		exit;
    	}//end catch

    	echo json_encode(array(
    			"error" => 0,
    			"response" => $arr_data,
    	), JSON_FORCE_OBJECT);
    	exit;
    }//end function

    /**
     * Create a new form
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Zend\Form\Form
     */
    public function createFormAction()
    {
    	$form_type = $this->params()->fromQuery("ftype", "");

		//load form
		$form = $this->getFormAdminModel()->getFormAdminForm($form_type);

		//set default content for submit button
		if ($form->has("submit_button"))
		{
			$form->get("submit_button")->setValue("Submit");
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				try {
					//create the form
					$objForm = $this->getFormAdminModel()->createForm($form->getData());

					//set success message
					$this->flashMessenger()->addSuccessMessage("Form created");

					//redirect to form edit page
					if ($form_type != "")
					{
						return $this->redirect()->toUrl($this->url()->fromRoute("front-form-admin/form", array("action" => "edit-form", "id" => $objForm->get("id"))) . "?ftype=$form_type");
					}//end if

					return $this->redirect()->toRoute("front-form-admin/form", array("action" => "edit-form", "id" => $objForm->get("id")));
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if

		return array("form" => $form);
    }//end function

    /**
     * Update a form
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function editFormAction()
    {
		$id = $this->params()->fromRoute("id", "");
		$form_type = $this->params()->fromQuery("ftype", "");

		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Form could not be loaded. Id is not set");
			//redirect to index page
			return $this->redirect()->toRoute("front-form-admin");
		}//end if

		//load form data
		$objForm = $this->getFormAdminModel()->getForm($id);

		//load form
		$form = $this->getFormAdminModel()->getFormAdminForm($form_type);
		$form->get("fk_form_type_id")->setAttribute("disabled", "disabled");

		//save form type and remove option from form
		$fk_form_type_id = $objForm->get("fk_form_type_id");

		//bind data to form
		$form->bind($objForm);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_data = $request->getPost();
			$arr_data['fk_form_type_id'] = $fk_form_type_id;
			$form->setData($arr_data);

			if ($form->isValid())
			{
				try {
					//update the form
					$objForm = $form->getData();
					$objForm->set("id", $id);
					$objForm->set("fk_form_type_id", $fk_form_type_id);

					$objForm = $this->getFormAdminModel()->editForm($objForm);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Form has been updated");

					//redirect to index page
					return $this->redirect()->toRoute("front-form-admin");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if

		if ($objForm->get("id") == "")
		{
			//reload form data
			$objForm = $this->getFormAdminModel()->getForm($id);
		}//end if

		return array(
				"form" => $form,
				"objForm" => $objForm
		);
    }//end function

    /**
     * Delete an existing form
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function deleteFormAction()
    {
		$id = $this->params()->fromRoute("id");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Form could not be deleted. ID is not set");

			//return to index page
			return $this->redirect()->toRoute("front-form-admin");
		}//end if

		//load data
		try {
			$objForm = $this->getFormAdminModel()->fetchForm($id);

			if (!$objForm)
			{
				$this->flashMessenger()->addErrorMessage("A problem occurred. The requested form could not be laoded");
				//return to index page
				return $this->redirect()->toRoute("front-form-admin");
			}//end if
		} catch (\Exception $e) {
    		//set error message
    		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));

			//return to index page
			return $this->redirect()->toRoute("front-form-admin");
		}//end catch

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{
				try {
					$this->getFormAdminModel()->deleteForm($id);

					//set message
					$this->flashMessenger()->addSuccessMessage("Form deleted successfully");
				} catch (\Exception $e) {
					//set error message
					$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
				}//end catch
			}//end if

			//return to index page
			return $this->redirect()->toRoute("front-form-admin");
		}//end if

		return array(
			"objForm" => $objForm,
		);
    }//end function

    public function formBehavioursAction()
    {
    	//set layout
    	$this->layout("layout/behaviours-view");

    	//set data array to collect behaviours and pass url data to view
    	$arr_behaviour_params = array(
    			"form_id" => $this->params()->fromRoute("id"),
    			"behaviour" => $this->params()->fromQuery("behaviour", "form"),
    	);

    	//load behaviours form
    	$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm($this->params()->fromQuery("behaviour", "form"), $arr_behaviour_params);
    	$form = $arr_config_form_data["form"];
    	$arr_descriptors = $arr_config_form_data["arr_descriptors"];

    	//load current form behaviours...
    	$objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);

    	//load the form data
    	$objForm = $this->getFormAdminModel()->fetchForm($this->params()->fromRoute("id"));

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
    			$arr_params["behaviour"] = $this->params()->fromQuery("behaviour", "form");
    			$arr_behaviour_params["beh_action"] = $arr_params["beh_action"];

    			$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_params);

    			//check if a local defined form exists for the behaviour, sometime needed since the api wont render the form correctly
    			$class = "\\FrontBehavioursConfig\\Forms\\Forms\\Behaviour" . str_replace(" ", "", ucwords(str_replace("_", " ", $arr_params['beh_action']))) . "Form";

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
    						$arr_form_data["form_id"] = $this->params()->fromRoute("id");

    						//create/update the behaviour
    						$objBehaviour = $this->getFrontBehavioursModel()->createBehaviourAction($arr_form_data);

    						//redirect back to the "index" view
    						return $this->redirect()->toUrl($this->url()->fromRoute("front-form-admin/form", array("action" => "form-behaviours", "id" => $this->params()->fromRoute("id"))));
    					} else {
    						//set additional params
    						$objBehaviour = $form->getData();
    						$objBehaviour->set("form_id", $this->params()->fromRoute("id"));

    						//update the behaviour
    						$objBehaviour = $this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);

    						//redirect back to the "index" view
    						return $this->redirect()->toUrl($this->url()->fromRoute("front-form-admin/form", array("action" => "form-behaviours", "id" => $this->params()->fromRoute("id"))));
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
    			"objForm"				=> $objForm,
    			//set header
    			"behaviours_header" 	=> "Behaviours configured for <span class=\"text-info\">" . $objForm->get("form") . "</span>",
    	));
    	$viewModel->setTemplate('front-behaviours-config/index/configure-behaviours.phtml');

    	return $viewModel;
    }//end function

    public function orderFieldsAction()
    {
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Form could not be loaded. Id is not set");
    		//redirect to index page
    		return $this->redirect()->toRoute("front-form-admin");
    	}//end if

    	//load form
    	$objForm = $this->getFormAdminModel()->fetchForm($id);

    	return array(
    		"objForm" => $objForm,
    	);
    }//end function

    /**
     * Request data about a form via ajax
     */
    public function ajaxLoadFormDataAction()
    {
    	//extract information
    	$form_id = $this->params()->fromRoute("id", "");

    	if ($form_id == "")
    	{
    		//set error message
    		return new JsonModel(array("error" => "Field information could not be loaded. Field id or Field Type is not available"));
    	}//end if

    	//load the form
    	$objForm = $this->getFormAdminModel()->getForm($form_id);

    	return new JsonModel($objForm->getArrayCopy());
    }//end function

    /**
     * Handler for anuglar forms to return local form validation failures in same format structure as API
     * @param \Zend\Form\Form $objForm
     */
    private function formatAngularFormErrors(\Zend\Form\Form $objForm)
    {
    	$arr_response = array(
    		'error' => 1,
    		'response' => 'Frontend Form Validation failed',
    		'form_messages' => $objForm->getMessages()
    	);

    	return $arr_response;
    }//end function

    /**
     * Create an instance of the Forms Admin model using the Service Manager
     * @return \FrontFormAdmin\Models\FrontFormAdminModel
     */
    private function getFormAdminModel()
    {
    	if (!$this->model_forms_admin)
    	{
    		$this->model_forms_admin = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontFormAdminModel");
    	}//end function

    	return $this->model_forms_admin;
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

    /**
     * Create an instance of the FrontFieldAdminModel using the Service Manager
     * @return \FrontFormAdmin\Models\FrontFieldAdminModel
     */
    private function getFieldsModel()
    {
    	if (!$this->model_fields)
    	{
    		$this->model_fields = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontFieldAdminModel");
    	}//end if

    	return $this->model_fields;
    }//end function

    /**
     * Create an instance of the Replace Fields Model using the Service Manager
     * @return \FrontFormAdmin\Models\FrontReplaceFieldsAdminModel
     */
    private function getReplaceFieldsModel()
    {
    	if (!$this->model_replace_fields)
    	{
    		$this->model_replace_fields = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontReplaceFieldsAdminModel");
    	}//end if

    	return $this->model_replace_fields;
    }//end if

    /**
     * Create an instance of the Generic Fields Model using the Service Manager
     * @return \FrontFormAdmin\Models\FrontGenericFieldsAdminModel
     */
    private function getGenericFieldsModel()
    {
    	if (!$this->model_generic_fields)
    	{
    		$this->model_generic_fields = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontGenericFieldsAdminModel");
    	}//end if

    	return $this->model_generic_fields;
    }//end if

    /**
     * Create an instance of the Profile File Manager using the Service Manager
     * @return \FrontProfileFileManager\Models\FrontProfileFileManagerModel
     */
    private function getProfileFileManagerModel()
    {
    	if (!$this->model_profile_file_manager)
    	{
    		$this->model_profile_file_manager = $this->getServiceLocator()->get('FrontProfileFileManager\Models\FrontProfileFileManagerModel');
    	}//end if

    	return $this->model_profile_file_manager;
    }//end function

    /**
     * Create an instance of the Profile's cache manager
     * @return \FrontCore\Caches\FrontCachesRedis
     */
    private function getProfileCacheManager()
    {
    	if (!$this->model_profile_cache_manager)
    	{
    		$this->model_profile_cache_manager = $this->getServiceLocator()->get('FrontCore\Caches\FrontCachesRedis');
    	}//end if

    	return $this->model_profile_cache_manager;
    }//end function
}//end class
