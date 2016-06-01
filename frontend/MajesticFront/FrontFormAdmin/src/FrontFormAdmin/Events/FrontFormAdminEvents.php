<?php
namespace FrontFormAdmin\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontFormAdminEvents extends AbstractCoreAdapter
{
	/**
	 * Container for the replace fields model
	 * @var \FrontFormAdmin\Models\FrontReplaceFieldsAdminModel
	 */
	private $model_replace_fields;

	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();

		/**
		 * Clear replace fields cache
		 */
		$eventManager->attach(
				array(
						"FrontFormsTemplates\Models\FrontFormsTemplatesModel",
						"FrontCommsTemplates\Models\FrontCommsTemplatesModel",
						"FrontFormAdmin\Models\FrontFormAdminModel",
						"FrontFormAdmin\Models\FrontFieldAdminModel",
						"FrontFormAdmin\Models\FrontGenericFieldsAdminModel",
						"FrontLinks\Models\FrontLinksModel",
				),
				array(
						'createFormTemplate.post',
						'deleteTemplate.post',
						'createCommTemplate.post',
						'updateCommTemplate.post',
						'createForm.post',
						'editForm.post',
						'deleteForm.post',
						'createCustomField.post',
						'updateCustomField.post',
						'deleteCustomField.post',
						'createGenericField.post',
						'updateGenericField.post',
						'deleteGenericField.post',
						'createLink.post',
						'updateLink.post',
						'deleteLink.post',
				),
				function ($event) use ($serviceManager) {
					return $serviceManager->get(__CLASS__)->clearReplaceFieldsCacheData($event);
				}//end function
			);
	}//end function


	private function clearReplaceFieldsCacheData()
	{
		$this->getReplaceFieldsModel()->fetchReplaceFields(array(), FALSE);
	}//end function

	/**
	 * Create an instance of the Replace Fields Model using the Service Manager
	 * @return \FrontFormAdmin\Models\FrontReplaceFieldsAdminModel
	 */
	private function getReplaceFieldsModel()
	{
		if (!$this->model_replace_fields)
		{
			$this->model_replace_fields = $this->getServiceLocator()->get('FrontFormAdmin\Models\FrontReplaceFieldsAdminModel');
		}//end if

		return $this->model_replace_fields;
	}//end function
}//end class
