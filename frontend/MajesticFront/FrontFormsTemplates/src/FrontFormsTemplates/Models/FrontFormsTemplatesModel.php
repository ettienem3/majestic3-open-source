<?php
namespace FrontFormsTemplates\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontLinks\Entities\LinkEntity;
use FrontFormsTemplates\Entities\FormTemplateEntity;

class FrontFormsTemplatesModel extends AbstractCoreAdapter
{
		/**
		 * Loads the Form Template admin area from Core System Forms
		 * @return \Zend\Form\Form
		 */
		public function getAdminFormsTemplates()
		{
			$objFormsTemplates = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
					->getSystemForm("Core\Forms\SystemForms\Templates\FormTemplatesForm");

			return $objFormsTemplates;
		}// end function

		/**
		 * @param array $arr_where - Optional
		 * @return object
		 */

		public function getFormsTemplates($arr_where = array())
		{
			//create the request object
			$objApiRequest = $this->getApiRequestModel();

			//setup the object and specify the action
			$objApiRequest->setApiAction("html/templates/forms");

			//execute
			$objFormsTemplates = $objApiRequest->performGETRequest($arr_where)->getBody();

			return $objFormsTemplates->data;
		}//end function

		/**
		 * Request details about a specific Form template
		 * @param mixed $id
		 * @return \FrontFromsTemplates\Entities\FormEntity
		 */
		public function getFormTemplate($id)
		{
			//create the request object
			$objApiRequest = $this->getApiRequestModel();

			//setup the object and specify the action
			$objApiRequest->setApiAction("html/templates/forms/$id");

			//execute
			$objFormTemplate = $objApiRequest->performGETRequest(array("id" => $id))->getBody();

			//create form entity
			$entity_form_template = $this->createFormTemplateEntity($objFormTemplate->data);

			//return
			return $entity_form_template;
		}// end function

		/**
		 * Create the form Template
		 * @param array $arr_data
		 * @return \FrontFormsTemplates\Entities\FormEntity
		 */
		public function createFormTemplate($arr_data)
		{
			//create FromTemplate entity
			$objFormTemplate = $this->createFormTemplateEntity($arr_data);

			// trigger pre event
			$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objFormTemplate" => $objFormTemplate));

			//create the request object
			$objApiRequest = $this->getApiRequestModel();
			//setup the object and specify the action
			$objApiRequest->setApiAction("html/templates/forms");
			//execute
			$objFormTemplate = $objApiRequest->performPOSTRequest($objFormTemplate->getArrayCopy())->getBody();

			//recreate the Form Template entity.
			$objFormTemplate = $this->createFormTemplateEntity($objFormTemplate->data);

			//trigger post event
			$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objFormTemplate" => $objFormTemplate));

			return $objFormTemplate;
		}// end function

		/**
		 * Update a Form  Template
		 * @trigger updateTemplate.pre, updateTemplate.post
		 * @param LinkEntity $objFormTemplate
		 * @return \FrontFormsTemplates\Entities\FormEntity
		 */
		public function updateTemplate(FormTemplateEntity $objFormTemplate)
		{
			//trigger pre event
			$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objFormTemplate" => $objFormTemplate));

			//create the request object
			$objApiRequest = $this->getApiRequestModel();

			//setup the object and specify the action
			$objApiRequest->setApiAction($objFormTemplate->getHyperMedia("edit-form-html-template")->url);
			$objApiRequest->setApiModule(NULL);

			//execute
			$objFormTemplate = $objApiRequest->performPUTRequest($objFormTemplate->getArrayCopy())->getBody();

			//recreate the form template entity
			$objFormTemplate = $this->createFormTemplateEntity($objFormTemplate->data);

			//trigger post event
			$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objFormTemplate" => $objFormTemplate));

			return $objFormTemplate;
		}//end function

		/**
		 *	Delete an existing Template
		 *@param mixed $id
		 *@return \FrontFormTemplates\Entities\FormTemplateEntity
		 */

		public function deleteTemplate($id)
		{
			//create the form template entity
			$objFormTemplate = $this->getFormTemplate($id);

			//trigger pre event
			$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objFormTemplate" => $objFormTemplate));

			// create the request object
			$objApiRequest = $this->getApiRequestModel();

			//setup the object and specify the action.
			$objApiRequest->setApiAction($objFormTemplate->getHyperMedia("edit-form-html-template")->url);
			$objApiRequest->setApiModule(NULL);

			//execute
			$objFormTemplate = $objApiRequest->performDELETERequest(array())->getBody();

			//trigger post event
			$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objFormTemplate" => $objFormTemplate));

			return $objFormTemplate;
		}// end function

		/**
		 * Request a list of files available for attaching to templates
		 * @return stdClass
		 */
		public function fetchAvailableTemplateFiles() 
		{
			//create the request object
			$objApiRequest = $this->getApiRequestModel();
				
			//setup the object and specify the action
			$objApiRequest->setApiAction("html/templates/forms/files");
				
			//execute
			$objFormTemplateFiles = $objApiRequest->performGETRequest(array('callback' => 'loadAvailableTemplateFiles'))->getBody();
			return $objFormTemplateFiles->data;
		}//end function
		
		/**
		 * Load files attached to Template
		 * @param integer $id
		 * @return stdClass
		 */
		public function fetchTemplateAttachedFiles($id)
		{
			//create the request object
			$objApiRequest = $this->getApiRequestModel();
			
			//setup the object and specify the action
			$objApiRequest->setApiAction("html/templates/forms/files");
			
			//execute
			$objFormTemplateFiles = $objApiRequest->performGETRequest(array('template_id' => $id))->getBody();
			return $objFormTemplateFiles->data;
		}//end function
		
		/**
		 * Load a specific file attached to Template
		 * @param integer $id
		 * @return stdClass
		 */
		public function fetchTemplateAttachedFile($id)
		{
			//create the request object
			$objApiRequest = $this->getApiRequestModel();
				
			//setup the object and specify the action
			$objApiRequest->setApiAction("html/templates/forms/files/" . $id);
				
			//execute
			$objFormTemplateFiles = $objApiRequest->performGETRequest(array())->getBody();
			return $objFormTemplateFiles->data;
		}//end function
		
		/**
		 * Attach a file to a template
		 * @param array $arr_data
		 * @return stdClass
		 */
		public function attachTemplateFile($arr_data)
		{
			//create the request object
			$objApiRequest = $this->getApiRequestModel();
				
			//setup the object and specify the action
			$objApiRequest->setApiAction("html/templates/forms/files");
				
			//execute
			$objFormTemplateFile = $objApiRequest->performPOSTRequest($arr_data)->getBody();
			return $objFormTemplateFile->data;
		}//end function
		
		/**
		 * Update attached file
		 * @param int $id
		 * @param array $arr_data
		 * @return stdClass
		 */
		public function updateAttachedFile($id, $arr_data)
		{
			//create the request object
			$objApiRequest = $this->getApiRequestModel();
			
			//setup the object and specify the action
			$objApiRequest->setApiAction("html/templates/forms/files/" . $id);
			
			//execute
			$objFormTemplateFile = $objApiRequest->performPUTRequest($arr_data)->getBody();

			return $objFormTemplateFile->data;
		}//end function
		
		/**
		 * Attach a file to a template
		 * @param array $arr_data
		 * @return stdClass
		 */
		public function detachTemplateFile($id)
		{
			//create the request object
			$objApiRequest = $this->getApiRequestModel();
		
			//setup the object and specify the action
			$objApiRequest->setApiAction("html/templates/forms/files/" . $id);
		
			//execute
			$objFormTemplateFile = $objApiRequest->performDELETERequest(array())->getBody();
			return $objFormTemplateFile->data;
		}//end function
		
		/**
		 * Detach all files attached to a template
		 * @param int $id - Pass the template id and not the file id
		 * @return stdClass
		 */
		public function detachAllTemplateFiles($id)
		{
			//create the request object
			$objApiRequest = $this->getApiRequestModel();
		
			//setup the object and specify the action
			$objApiRequest->setApiAction("html/templates/forms/files/" . $id);
		
			//execute
			$objFormTemplateFile = $objApiRequest->performDELETERequest(array('template_id' => $id))->getBody();
			return $objFormTemplateFile->data;
		}//end function

		/**
		 * Create the FormTemplateEntity
		 * @param object $objFormTemplate
		 * @return \FrontFormsTemplates\Entities\FormTemplateEntity
		 */
		private function createFormTemplateEntity($objData)
		{
			$entity_form_template = $this->getServiceLocator()->get("FrontFormsTemplates\Entities\FormTemplateEntity");

			//populate the data
			$entity_form_template->set($objData);

			return $entity_form_template;
		}//end function

}//end class
