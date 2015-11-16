<?php
namespace FrontLinks\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontLinks\Entities\LinkEntity;

class FrontLinksModel extends AbstractCoreAdapter
{
	/**
	 * Load the admin form for links from Core System Forms
	 * @return \Zend\Form\Form
	 */
	public function getLinksForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
						->getSystemForm("Core\Forms\SystemForms\Links\LinksForm");

		return $objForm;
	}//end function

	/**
	 * Load a list of links available for profile
	 * @param array $arr_where - Optional
	 * @return object
	 */
	public function fetchLinks($arr_where = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("links/admin");

		//execute
		$objLinks = $objApiRequest->performGETRequest($arr_where)->getBody();

		return $objLinks->data;
	}//end function

	/**
	 * Request details about a specfic link
	 * @param mixed $id
	 * @return \FrontLinks\Entities\LinkEntity
	 */
	public function fetchLink($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("links/admin/$id");

		//execute
		$objLink = $objApiRequest->performGETRequest(array())->getBody();

		//create link entity
		$objLink = $this->createLinkEntity($objLink->data);

		return $objLink;
	}//end function

	/**
	 * Create a link
	 * @trigger createLink.pre, createLink.post
	 * @param array $arr_data
	 * @return \FrontLinks\Entities\LinkEntity
	 */
	public function createLink($arr_data)
	{
		//create link entity
		$objLink = $this->createLinkEntity($arr_data);

		//enable delayed api request
		if ($this->getDelayedProcessingFlag() === TRUE)
		{
			$this->getServiceLocator()->get("FrontCLI\Models\FrontCLIControllerModel")->requestCLIAction(
					"CoreModelAction",
					(object) array(
							"model" => __CLASS__,
							"function" => __FUNCTION__,
							"data" => array(
									array(
											"name" => "objLink",
											"type" => get_class($objLink),
											"data" => $objLink->getArrayCopy(),
									),
							)
					)
			);
			return $objLink;
		}//end if

		//trigger pre event
		$this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objLink" => $objLink));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("links/admin");

		//execute
		$objLink = $objApiRequest->performPOSTRequest($objLink->getArrayCopy())->getBody();

		//recreate link entity
		$objLink = $this->createLinkEntity($objLink);

		//trigger post event
		$this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objLink" => $objLink));

		return $objLink;
	}//end function

	/**
	 * Update a link
	 * @trigger updateLink.pre, updateLink.post
	 * @param LinkEntity $objLink
	 * @return \FrontLinks\Entities\LinkEntity
	 */
	public function updateLink(LinkEntity $objLink)
	{
		//enable delayed api request
		if ($this->getDelayedProcessingFlag() === TRUE)
		{
			$this->getServiceLocator()->get("FrontCLI\Models\FrontCLIControllerModel")->requestCLIAction(
						"CoreModelAction",
						(object) array(
										"model" => __CLASS__,
										"function" => __FUNCTION__,
										"data" => array(
											array(
													"name" => "objLink",
													"type" => get_class($objLink),
													"data" => $objLink->getArrayCopy(),
											),
										)
									)
								);
			return $objLink;
		}//end if

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objLink" => $objLink));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("links/admin/" . $objLink->get("id"));

		//execute
		$objLink = $objApiRequest->performPUTRequest($objLink->getArrayCopy())->getBody();

		//recreate link entity
		$objLink = $this->createLinkEntity($objLink->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objLink" => $objLink));

		return $objLink;
	}//end function

	/**
	 * Delete an existing link
	 * @param mixed $id
	 * @return \FrontLinks\Entities\LinkEntity
	 */
	public function deleteLink($id)
	{
		//enable delayed api request
		if ($this->getDelayedProcessingFlag() === TRUE)
		{
			$this->getServiceLocator()->get("FrontCLI\Models\FrontCLIControllerModel")->requestCLIAction(
					"CoreModelAction",
					(object) array(
							"model" => __CLASS__,
							"function" => __FUNCTION__,
							"data" => array(
									array(
											"name" => "id",
											"type" => "int",
											"data" => $id,
									),
							)
					)
			);
			return $objLink;
		}//end if

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array());

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("links/admin/" . $id);

		//execute
		$objLink = $objApiRequest->performDELETERequest(array())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objLink" => $objLink));

		return $objLink;
	}//end function

	/**
	 * Create a link entity object
	 * @param object $objData
	 * @return \FrontLinks\Entities\LinkEntity
	 */
	private function createLinkEntity($objData)
	{
		$entity_link = $this->getServiceLocator()->get("FrontLinks\Entities\LinkEntity");

		//populate the data
		$entity_link->set($objData);

		return $entity_link;
	}//end function
}//end class
