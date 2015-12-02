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
			$objFormTemplate = $this->createFormTemplateEntity($objFormTemplate);

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
