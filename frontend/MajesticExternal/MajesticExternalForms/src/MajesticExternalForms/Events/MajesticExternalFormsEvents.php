<?php
namespace MajesticExternalForms\Events;

use FrontCore\Adapters\AbstractCoreAdapter;

class MajesticExternalFormsEvents extends AbstractCoreAdapter
{
	public function registerEvents()
	{
		$eventManager = $this->getEventManager()->getSharedManager();
		$serviceManager = $this->getServiceLocator();

		/**
		 * Cache events
		 */
		//read cache
		$eventManager->attach(
				"MajesticExternalForms\Models\MajesticExternalFormsModel",
				"loadForm.cache.get",
				function ($event) use ($serviceManager) {
					return $serviceManager->get(__CLASS__)->cacheGetForm($event);
				}//end function
		);

		//set cache
		$eventManager->attach(
				"MajesticExternalForms\Models\MajesticExternalFormsModel",
				"loadForm.cache.set",
				function ($event) use ($serviceManager) {
					return $serviceManager->get(__CLASS__)->cacheSetForm($event);
				}//end function
		);

		//clear cache
		$eventManager->attach(
				"MajesticExternalForms\Models\MajesticExternalFormsModel",
				"loadForm.cache.clear",
				function ($event) use ($serviceManager) {
					return $serviceManager->get(__CLASS__)->cacheClearForm($event);
				}//end function
		);

		//listen to form administration events and clear cache where triggered
		$eventManager->attach(
				"FrontFormAdmin\Models\FrontFormAdminModel",
				array(
					"editForm.post",
					"deleteForm.post",
					"allocateFieldtoForm.post",
					"updateFormField.post",
					"removeFormField.post",
					'updateFormFieldsOrder.pre',
				),
				function ($event) use ($serviceManager) {
					return $serviceManager->get(__CLASS__)->cacheClearForm($event);
				}//end function
		);

		//flush caches
		$eventManager->attach(
				"*",
				array(
						"cache.flush.all"
						),
				function ($event) use ($serviceManager) {
					return $serviceManager->get("MajesticExternalForms\Events\MajesticExternalFormsEvents")->cacheFlushForm($event);
				}//end function
		);
	}//end function

	private function cacheGetForm($event)
	{
		$form_id = $event->getParam("form_id");

		//load the cache data
		$cache = $this->getFormsCacheModel()->readFormCache($form_id);

		//if no result has been found, run course
		if (is_null($cache))
		{
			return FALSE;
		}//end if

		//stop propagation, field loaded from cache
		$event->stopPropagation(TRUE);
		return $cache;
	}//end function

	private function cacheSetForm($event)
	{
		$form_id = $event->getParam("form_id");
		$arr_data = $event->getParam("arr_data");

		$this->getFormsCacheModel()->setFormCache($form_id, $arr_data);
	}//end function

	private function cacheClearForm($event)
	{
		$form_id = $event->getParam("form_id");

		//try form object
		if (is_null($form_id) || $form_id == "")
		{
			$objForm = $event->getParam("objForm");
			try {
				if (is_object($objForm))
				{
					$form_id = $objForm->get("id");
				}//end if
			} catch (\Exception $e) {
				//log error
				trigger_error($e->getMessage(), E_USER_NOTICE);
			}//end catch
		}//end if

		//try field object
		if (is_null($form_id) || $form_id == "")
		{
			$objField = $event->getParam("objField");
			try {
				if (is_object($objField))
				{
					$form_id = $objField->get("fk_form_id");
				}//end if
			} catch (\Exception $e) {
				trigger_error($e->getMessage(), E_USER_NOTICE);
			}//end catch
		}//end if

		if ($form_id != "")
		{
			$this->getFormsCacheModel()->clearFormCache($form_id);
		}//end if
	}//end function

	private function cacheFlushForm($event)
	{
		$form_id = $event->getParam("form_id");

		$this->getFormsCacheModel()->clearEntireFormCache();
	}//end function

	/**
	 * Create instance of Majestic External Forms Cache Model using the Service Manager
	 * @return \MajesticExternalForms\Models\MajesticExternalFormsCacheModel
	 */
	private function getFormsCacheModel()
	{
		return $this->getServiceLocator()->get("MajesticExternalForms\Models\MajesticExternalFormsCacheModel");
	}//end function
}//end class