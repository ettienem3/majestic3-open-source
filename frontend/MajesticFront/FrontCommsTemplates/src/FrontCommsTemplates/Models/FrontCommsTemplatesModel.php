<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace FrontCommsTemplates\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontCommsTemplates\Entities\CommTemplateEntity; 	//	This provides a common setup for Entity objects.

/**
 * FrontCommstemplates{} creates business logic for the application.
 */
class FrontCommsTemplatesModel extends AbstractCoreAdapter
{
	/**
	 * Initializes/Instantiates/Gets/Retrieves/Loads Comms Templates form from Core System Forms
	 * using Service Manager\Locator (SM\L) from FrontCore Model.
	 * @return \FrontCore\Forms\FrontCoreSystemFormBase
	 */
	public function getCommsTemplatesForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm("Core\Forms\SystemForms\Templates\CommTemplatesForm");

		return $objForm;
	} // end getCommsTemplatesForm()

	/**
	 * Gets the list of Comms Templates rows of data.
	 * @param string $arr_where
	 */
	public function getCommsTemplates($arr_where = array())
	{
		// Creates the API Model HTTP request object
		$objApiRequest = $this->getApiRequestModel();

		// Setup the object and specify the action for the form
		$objApiRequest->setApiAction("html/templates/comms");

		// Gets HTTP request METHOD=GET and return the form body
		// getBody() method - converts JSON object format to array data format.
		// Tranferring data from DB to ZF environment.
		$objCommsTemplates = $objApiRequest->performGETRequest($arr_where)->getBody();

		return $objCommsTemplates->data;

	} // end getComms($arr_where = NULL)


	/**
	 * Gets specific Comm Template row of data.
	 * @param mixed $id
	 * @return \FrontCommsTemplates\Entities\FrontCommTemplateEntity
	 */
	public function getCommTemplate($id)
	{
		// Creates the API Model HTTP request object
		$objApiRequest = $this->getApiRequestModel();

		// Setup the object and specify the action for the form
		// Connecting the FRONT-END to API
		$objApiRequest->setApiAction("html/templates/comms/$id");

		// Sets HTTP request METHOD=GET and return the form body
		// getBody() method - converts JSON object format to array data format.
		$objCommTemplate = $objApiRequest->performGETRequest(array("id" => $id))->getBody();

		// Creates Comm Template entity. Tranferring data from DB to ZF environment.
		$entity_commtemplate = $this->createCommTemplateEntity($objCommTemplate->data);

		return $entity_commtemplate;
	}// end getCommTemplate($id)



	/**
	 * Create Comm Template
	 * @triggers createCommTemplate.pre, createCommTemplate.post
	 * @param array $arr_data
	 * @return \FrontCommsTemplates\Entities\CommTemplateEntity
	 */
	public function createCommTemplate($arr_data)
	{
		// Creates Comm Template entity
		$objCommTemplate = $this->createCommTemplateEntity($arr_data);

		// 	trigger pre event. "PRE" - Initializes the event.
		//	$em = new EventManager();
		//	$em->listener(trigger/attach)->($event->getName(), $target->getTarget(), $params->getParams(), $priority);
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objCommTemplate" => $objCommTemplate));

		//create the HTTP request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("html/templates/comms");

		// $_POST data in array format from JSON format
		$objCommTemplate = $objApiRequest->performPOSTRequest($objCommTemplate->getArrayCopy())->getBody();

		//recreate commtemplate entity
		$objCommTemplate = $this->createCommTemplateEntity($objCommTemplate);

		//trigger post event. "POST" - Confirm to execution of the event.
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objCommTemplate" => $objCommTemplate));

		return $objCommTemplate;
	} //end createCommTemplates($arr_data)


	/**
	 * Update Comm Template
	 * @trigger updateCommTemplate.pre, updatecommTemplate.post
	 * @param CommTemplateEntity $objCommTmplate
	 * @return \FrontCommsTemplates\Entities\CommTemplateEntity
	 */
	public function updateCommTemplate(CommTemplateEntity $objCommTemplate)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objCommTemplate" => $objCommTemplate));

		//create the HTTP request object
		$objApiRequest = $this->getApiRequestModel();

		// Setup the object and specify the action
		// getHyperMedia() -
		$objApiRequest->setApiAction($objCommTemplate->getHyperMedia("edit-comm-html-template")->url);
		$objApiRequest->setApiModule(NULL);

		//data in array format from JSON format
		$objCommTemplate = $objApiRequest->performPUTRequest($objCommTemplate->getArrayCopy())->getBody();

		// recreate comm template entity
		$objCommTemplate = $this->createCommTemplateEntity($objCommTemplate->data);

		// trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objCommTemplate" => $objCommTemplate));

		return $objCommTemplate;
	} // end function


	/**
	 * Delete Comm Template specific row of data.
	 * @param mixed $id
	 * @return \FrontCommsTemplate\Entities\CommTemplateEntity
	 */
	public function deleteCommTemplate($id)
	{
		// Instatiates Comm Template Entity
		$objCommTemplate = $this->getCommTemplate($id);

		// trigger pre event
		$result = $this->getEventManager()
				->trigger(__FUNCTION__ . ".pre", $this, array("objCommTemplate" => $objCommTemplate));

		// create the request object
		$objApiRequest = $this->getApiRequestModel();

		// setup the object and specify the action
		$objApiRequest->setApiAction($objCommTemplate->getHyperMedia("delete-comm-html-template")->url);
		$objApiRequest->setApiModule(NULL);

		// execute
		$objCommTemplate = $objApiRequest->performDELETERequest(array())->getBody();

		//trigger post event
		$result = $this->getEventManager()
				->trigger(__FUNCTION__ . ".post", $this, array("objCommTemplate" => $objCommTemplate));

		return $objCommTemplate;
	}// end deleteCommTemplate($id)


	/**
	 * Creates CommTemplate entity object
	 * @param $objData
	 * @return \FrontCommsTemplates\Entities\CommEntity
	 */
	public function createCommTemplateEntity($objData)
	{
		//	Stores data in the comms entity object
		$entity_commtemplate = $this->getServiceLocator()->get("FrontCommsTemplates\Entities\CommTemplateEntity");
		//	populates data.
		$entity_commtemplate->set($objData);
		return $entity_commtemplate;
	} // end createCommTemplateEntity($objData)

} // end FrontCommsTemplates{}