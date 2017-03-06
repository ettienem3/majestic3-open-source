<?php
namespace FrontFormsTemplates\Controller;

use FrontCore\Adapters\AbstractCoreActionController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractCoreActionController
{
	/**
	 *
	 * Container for the FormsTemplates Model instance
	 * @var \FrontFormsTemplates\Models\FrontFormsTemplatesModel
	 *
	 */
	private $model_forms_templates;
	
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
	 * Container for the Profilr File Manager
	 * @var \FrontProfileFileManager\Models\FrontProfileFileManagerModel
	 */
	private $model_profile_file_manager;
	
	/**
	 * Container for the Profile Cache Manager (Default is Redis)
	 * @var \FrontCore\Caches\FrontCachesRedis $model_profile_cache_manager
	 */
	private $model_profile_cache_manager;

    public function indexAction()
    {
    	//load form templates
    	try {
    		$objFormsTemplates = $this->getFormsTemplatesModel()->getFormsTemplates($this->params()->fromQuery());
    	} catch (\Exception $e) {
    		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
    		return $this->redirect()->toRoute('home');
    	}//end catch

    	return array("objFormsTemplates" => $objFormsTemplates);
    }// end function
    
    public function appAction()
    {
    	$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
    	if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['form-look-and-feel'] != true)
    	{
    		$this->flashMessenger()->addInfoMessage('The requested view is not available');
    		return $this->redirect()->toRoute("front-form-templates");
    	}//end if
    	
    	$this->layout('layout/angular/app');

    	//check if data is cached
    	$cache_key = __CLASS__ . '-' . __FUNCTION__ . '-data';
    	$arr_data = $this->getProfileCacheManager()->readCacheItem($cache_key, false);
    	if (is_array($arr_data))
    	{
    		return $arr_data;
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
    	if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['form-look-and-feel'] != true)
    	{
    		$this->flashMessenger()->addInfoMessage('The requested view is not available');
    		return $this->redirect()->toRoute("front-form-templates");
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
    			case 'load-templates':
    				$objTemplates = $this->getFormsTemplatesModel()->getFormsTemplates();
    				$arr_templates = array();
    				foreach ($objTemplates as $objTemplate)
    				{
    					if (isset($objTemplate->id))
    					{
    						$objTemplate->active = $objTemplate->active * 1;
    						unset($objTemplate->hypermedia);
    						$arr_templates[] = $objTemplate;
    					}//end if
    				}//end foreach
    				
    				$objResult = new JsonModel(array(
    					'error' => 0,
    					'objData' => (object) $arr_templates,
    				));
    				return $objResult;
    				break;
    				
    			case 'load-template':
    				$objTemplate = $this->getFormsTemplatesModel()->getFormTemplate($arr_params['id']);
    				$arr_data = $objTemplate->getArrayCopy();
    				$arr_data['active'] = $arr_data['active'] * 1;
    				if (isset($arr_data['hypermedia']))
    				{
    					unset($arr_data['hypermedia']);	
    				}//end if
    				
    				$objResult = new JsonModel(array(
    						'error' => 0,
    						'objData' => (object) $arr_data,
    				));
    				return $objResult;
    				break;
    				
    			case 'create-template':
    				try {
    					$form = $this->getFormsTemplatesModel()->getAdminFormsTemplates();
    					$form->setData($arr_post_data);
    					 
    					if ($form->isValid())
    					{
    						$arr_data = (array) $form->getData();
    						$arr_data['css_file'] = '';
    						$objTemplate = $this->getFormsTemplatesModel()->createFormTemplate($arr_data);
    				
    						$objResult = new JsonModel(array(
    								'error' => 0,
    								'objData' => (object) $objTemplate->getArrayCopy(),
    						));
    						return $objResult;
    					} else {
    						$objResult = new JsonModel(array(
    								'error' => 1,
    								'response' => 'Form values could not be validated',
    						));
    						return $objResult;
    					}//end if
    				} catch (\Exception $e) {
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => $this->frontControllerErrorHelper()->formatErrors($e),
    							'raw_response' => $e->getMessage(),
    					));
    					return $objResult;
    				}//end catch
    				break;
    				
    			case 'update-template':
    				try {
	    				$objTemplate = $this->getFormsTemplatesModel()->getFormTemplate($arr_post_data['id']);
	    				if ($objTemplate->get('id') != $arr_post_data['id'])
	    				{
	    					$objResult = new JsonModel(array(
	    							'error' => 1,
	    							'response' => 'The requested Look and Feel could not be located',
	    					));
	    					return $objResult;
	    				}//end if
	    				
	    				$form = $this->getFormsTemplatesModel()->getAdminFormsTemplates();
	    				$form->bind($objTemplate);
	    				$form->setData($arr_post_data);
	    				
	    				if ($form->isValid())
	    				{
	    					$objT = $form->getData();
	    					$objT->set('id', $objTemplate->get('id'));
	    					$objT->set('css_file', $objTemplate->get('css_file'));
	    					$objTemplate = $this->getFormsTemplatesModel()->updateTemplate($objT);
	    					
	    					$objResult = new JsonModel(array(
	    						'error' => 0,
	    						'objData' => (object) $objTemplate->getArrayCopy(),
	    					));
	    					return $objResult;
	    				} else {
	    					$objResult = new JsonModel(array(
	    						'error' => 1,
	    						'response' => 'Form values could not be validated',
	    					));
	    					return $objResult;
	    				}//end if
    				} catch (\Exception $e) {
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => $this->frontControllerErrorHelper()->formatErrors($e),
    							'raw_response' => $e->getMessage(),
    					));
    					return $objResult;
    				}//end catch
    				break;

    			case 'toggle-template-status':
    				$objTemplate = $this->getFormsTemplatesModel()->getFormTemplate($arr_post_data['id']);
    				$objTemplate->set('active', (1 - $objTemplate->get('active')));
    				$this->getFormsTemplatesModel()->updateTemplate($objTemplate);
    			
    				$objResult = new JsonModel(array(
    						'error' => 0,
    						'objData' => (object) $objTemplate->getArrayCopy(),
    				));
    				return $objResult;
    				break;
    					
    			case 'load-forms-using-template':
    				$objData = $this->getFormsTemplatesModel()->getFormsTemplates(array(
    					'id' => $arr_params['id'],
    					'callback' => 'loadFormsUsingTemplate',
    				));
    				
    				$objResult = new JsonModel(array(
    					'error' => 0,
    					'objData' => $objData,
    				));
    				return $objResult;
    				break;
    				
    			case 'load-available-files':
    				$objFiles = $this->getFormsTemplatesModel()->fetchAvailableTemplateFiles();
    				$objResult = new JsonModel(array(
    						'error' => 0,
    						'objData' => $objFiles,
    				));
    				return $objResult;
    				break;
    				
    			case 'load-template-files':
    				$objTemplate = $this->getFormsTemplatesModel()->getFormTemplate($arr_params['id']);
    				if ($objTemplate->get('id') != $arr_params['id'])
    				{
    					$objResult = new JsonModel(array(
    						'error' => 1,
    						'response' => 'The requested Look and Feel could not be located',
    					));
    					return $objResult;
    				}//end if
    				
    				$objFiles = $this->getFormsTemplatesModel()->fetchTemplateAttachedFiles($objTemplate->get('id'));
    				$objResult = new JsonModel(array(
    					'error' => 0,
    					'objData' => $objFiles,
    				));
    				return $objResult;
    				break;
    				
    			case 'attach-template-file':
    				$objTemplate = $this->getFormsTemplatesModel()->getFormTemplate($arr_post_data['id']);
    				if ($objTemplate->get('id') != $arr_post_data['id'])
    				{
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'The requested Look and Feel could not be located',
    					));
    					return $objResult;
    				}//end if
    				
    				unset($arr_post_data['id']);
    				$objData = $this->getFormsTemplatesModel()->attachTemplateFile($arr_post_data);
    				$objResult = new JsonModel(array(
    					'error' => 0,
    					'objData' => $objData,
    				));
    				return $objResult;
    				break;
    				
    			case 'detach-template-file':
    				$objTemplate = $this->getFormsTemplatesModel()->getFormTemplate($arr_post_data['fk_id_form_templates']);
    				if ($objTemplate->get('id') != $arr_post_data['fk_id_form_templates'])
    				{
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'The requested Look and Feel could not be located',
    					));
    					return $objResult;
    				}//end if
    				
    				$objData = $this->getFormsTemplatesModel()->detachTemplateFile($arr_post_data['id']);
    				$objResult = new JsonModel(array(
    						'error' => 0,
    						'objData' => $objData,
    				));
    				return $objResult;
    				break;
    				
    			case 'update-attached-template-file':
    				$objTemplate = $this->getFormsTemplatesModel()->getFormTemplate($arr_post_data['fk_id_form_templates']);
    				if ($objTemplate->get('id') != $arr_post_data['fk_id_form_templates'])
    				{
    					$objResult = new JsonModel(array(
    							'error' => 1,
    							'response' => 'The requested Look and Feel could not be located',
    					));
    					return $objResult;
    				}//end if
    				
    				$objData = $this->getFormsTemplatesModel()->updateAttachedFile($arr_post_data['id'], $arr_post_data);
    				$objResult = new JsonModel(array(
    						'error' => 0,
    						'objData' => $objData,
    				));
    				return $objResult;
    				break;
    				
    			case 'toggle-attached-file-status':
    				$objFile = $this->getFormsTemplatesModel()->fetchTemplateAttachedFile($arr_post_data['id']);
    				$objFile->active = (1 - $objFile->active);
    				
    				$objData = $this->getFormsTemplatesModel()->updateAttachedFile($objFile->id, (array) $objFile);
    				$objResult = new JsonModel(array(
    						'error' => 0,
    						'objData' => $objData,
    				));
    				return $objResult;
    				break;
    				
    			case 'read-template-attached-file':
    				$path = $arr_params['filemanager_content_http_path'];
    				$c = file_get_contents($path);
    				
    				$objResult = new JsonModel(array(
    					'error' => 0,
    					'objData' => $c,
    				));
    				return $objResult;
    				break;
    		}//end switch
    	} catch (\Exception $e) {
    		$arr_return = array(
    				'error' => 1,
    				'response' => $this->frontControllerErrorHelper()->formatErrors($e),
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
    
    public function ajaxIndexAction()
    {
    	//load form templates
    	try {
    		$objFormsTemplates = $this->getFormsTemplatesModel()->getFormsTemplates($this->params()->fromQuery());
    		if (isset($objFormsTemplates->hypermedia))
    		{
    			unset($objFormsTemplates->hypermedia);	
    		}//end if
    		
    		$arr_templates = array();
    		foreach ($objFormsTemplates as $objTemplate)
    		{
    			if (is_numeric($objTemplate->id))
    			{
    				$arr_templates[] = array(
    					'id' => $objTemplate->id * 1,
    					'template' => str_replace(array("'", '-', '"', '\\', '/'), '', $objTemplate->template),
    				);
    			}//end if
    		}//end if
    		
    		return new JsonModel(array(
    			'objData' => (object) $arr_templates
    		));
    	} catch (\Exception $e) {
			return new JsonModel(array(
				'error' => 1,
				'response' => $this->frontControllerErrorHelper()->formatErrors($e),
				'raw_response' => $e->getMessage()
			));
    	}//end catch
    }//end function

    	/**
		 *
		 * Create new form template
		 * @return multitype:\Zend\Form\Form
		 */
		public function createAction()
		{
			$form = $this->getFormsTemplatesModel()->getAdminFormsTemplates();
			$request = $this->getRequest();

			//set default content
			$form->get("content")->setValue("#content");

			if ($request->isPost())
			{
				//set form template data
				$form->setData($request->getPost());

				if ($form->isValid())
				{
					try {
						// create the template
						$objFormTemplate = $this->getFormsTemplatesModel()->createFormTemplate($form->getData());

						//set success message
						$this->flashMessenger()->addSuccessMessage("Look and Feel has been created");

						// redirect to the index page
						return $this->redirect()->toRoute("front-form-templates");
					}	catch (\Exception $e) {
    					//set error message
    					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
					}// end catch
				}//end function
			}//end if

			return array("form" => $form);
		}// end function

		/**
		 *
		 * Update an existing form template
		 * @return Ambiguous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface> |multitype:\Zend\Form\Form
		 */

		public function editAction()
		{
			// get id from route
			$id = $this->params()->fromRoute("id", "");

			if ($id == "")
			{
				//set error message
				$this->flashMessenger()->addErrorMessage("Template could not be loaded. Id is not set");
				return $this->redirect()->toRoute("front-form-templates");

			}//end if

			//load Template details
			try {
				$objFormTemplate = $this->getFormsTemplatesModel()->getFormTemplate($id);
			} catch (\Exception $e) {
				$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
				return $this->redirect()->toRoute('home');
			}//end catch

			//load the system form
			$form = $this->getFormsTemplatesModel()->getAdminFormsTemplates();

			//bind the data
			$form->bind($objFormTemplate);

			$request = $this->getRequest();
			if ($request->isPost())
			{
				// set form data
				$form->setData($request->getPost());

					if ($form->isValid())
					{
						try {
							$objFormTemplate = $form->getData();
							$objFormTemplate->set("id", $id);
							$objFormTemplate = $this->getFormsTemplatesModel()->updateTemplate($objFormTemplate);

							// set success message
							$this->flashMessenger()->addSuccessMessage("Template Updated");
							return $this->redirect()->toRoute("front-form-templates");
						} catch (\Exception $e) {
							//set message
							$this->flashMessenger()->addErrorMessage($e->getMessage());
						}// end if
					}//end if
			}// end if

			return array(
					"form" => $form,
					"objFormTemplate" => $objFormTemplate,
			);
		}//end function

		/**
		 * Delete the existing form template
		 */
		public function deleteAction()
		{
			$id = $this->params()->fromRoute("id", "");

			if ($id == "")
			{
				//set error message
				$this->flashMessenger()->addErrorMessage("The Template could not be dleted. Id is not set");
				//return to index page
				return $this->redirect()->toRoute("front-form-templates");
			}//end if

			$request = $this->getRequest();
			if ($request->isPost())
			{
				if (strtolower($request->getPost("delete")) == "yes")
				{
					//delete the link
					try {
						$objFormsTemplates = $this->getFormsTemplatesModel()->deleteTemplate($id);

						//set message
						$this->flashMessenger()->addSuccessMessage("Template Deleted");
			     	} catch (\Exception $e) {
			     		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
			     	}//end catch
				}//end if

				//redirect to index page
				return $this->redirect()->toRoute("front-form-templates");
			}//end if
		}// end function

		/**
		 *
		 * Activate or deactivate a form template
		 */
		public function statusAction()
		{
			$id = $this->params()->fromRoute("id", "");

			if ($id == "")
			{
				//set error message
				$this->flashMessenger()->addErrorMessage("Template could not be Activated. Id is not set");
				//return to index page
				return $this->redirect()->toRoute("front-form-templates");
			}//end if

			try {
				//load the template details
				$objFormTemplate = $this->getFormsTemplatesModel()->getFormTemplate($id);
				$objFormTemplate->set("active", (1 - $objFormTemplate->get("active")));
				//update the template
				$objFormTemplate = $this->getFormsTemplatesModel()->updateTemplate($objFormTemplate);

				//set success message
				$this->flashMessenger()->addSuccessMessage("Template Status Updated");
			} catch (\Exception $e) {
				//set message
				$this->flashMessenger()->addErrorMessage($e->getMessage());
			}//end if

			//redirect to index page
			return $this->redirect()->toRoute("front-form-templates");
		}// end function

	    /**
	     * Creates an instance of the Form Template model using the service manager
	     * @return \FrontFormsTemplates\Models\FrontFormsTemplatesModel
	     */
	    private function getFormsTemplatesModel()
	    {
	    	if (!$this->model_forms_templates)
	    	{
	    		$this->model_forms_templates = $this->getServiceLocator()->get("FrontFormsTemplates\Models\FrontFormsTemplatesModel");
	    	}// end if

	    	return $this->model_forms_templates;
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
}// end class
