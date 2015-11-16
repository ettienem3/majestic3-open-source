<?php
namespace FrontCore\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCoreSystemFormEvents extends AbstractCoreAdapter
{
	/**
	 * Container for the System Form Manager
	 * @var \FrontCore\Models\SystemFormsModel
	 */
	private $model_system_form;

	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();

		/**
		 * Clear Web forms system forms cache
		 */
		$eventManager->attach(
				"FrontFormsTemplates\Models\FrontFormsTemplatesModel",
				array(
						'createFormTemplate.post',
						'deleteTemplate.post',
				),
				function ($event) use ($serviceManager) {
					return $serviceManager->get(__CLASS__)->clearWebAdminSystemFormCache($event);
				}//end function
		);
	}//end function

	/**
	 * Clear Web admin form where specific actions are taken:
	 * Create look and feel
	 * Delete look and feel
	 * @param $event
	 */
	private function clearWebAdminSystemFormCache($event)
	{
		//clear web form system form cache
		$this->getSystemFormManager()->clearFormCache("Core\Forms\SystemForms\Forms\FormsForm");
	}//end function

	/**
	 * Create an instance of the System Form Model using the Service Manager
	 * @return \FrontCore\Models\SystemFormsModel
	 */
	private function getSystemFormManager()
	{
		if (!$this->model_system_form)
		{
			$this->model_system_form = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel");
		}//end if

		return $this->model_system_form;
	}//end function
}//end class