<?php
namespace FrontUsers\Models;

use FrontCore\Adapters\AbstractCoreAdapter;
use FrontUsers\Entities\FrontUserRoleAclLinkEntity;

class FrontUsersRolesAclLinksModel extends AbstractCoreAdapter
{
	public function getRoleAclResourceAllocateSystemForm()
	{
		$objForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel")
											->getSystemForm("Core\Forms\SystemForms\AccessControl\RoleAclLinkForm");

		return $objForm;
	}//end function

	public function fetchApiAclResources($role_id = "")
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("admin/access-control/fetch-resources?type=api&role_id=$role_id");

		//execute
		$objApiAclResources = $objApiRequest->performGETRequest()->getBody();

		return $objApiAclResources->data;
	}//end function

	public function fetchCoreAclResources($role_id = "")
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("admin/access-control/fetch-resources?type=core&role_id=$role_id");

		//execute
		$objCoreAclResources = $objApiRequest->performGETRequest()->getBody();

		return $objCoreAclResources->data;
	}//end function

	public function fetchApiAclResource($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("admin/access-control/fetch-resources/$id?type=api");

		//execute
		$objAclResource = $objApiRequest->performGETRequest()->getBody();

		//create acl resource entity
		$objAclResource = $this->createAclResourceEntity(NULL, $objAclResource->data);

		return $objAclResource;
	}//end function

	public function fetchCoreAclResource($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("admin/access-control/fetch-resources/$id?type=core");

		//execute
		$objAclResource = $objApiRequest->performGETRequest()->getBody();

		if (is_object($objAclResource->data))
		{
			//create acl resource entity
			$objAclResource = $this->createAclResourceEntity(NULL, $objAclResource->data);
		}//end if

		return $objAclResource;
	}//end function

	public function fetchRoleAclResourceAllocations($role_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("users/admin/roles/resources/$role_id");

		//execute
		$objRoleAclLinks = $objApiRequest->performGETRequest()->getBody();

		return $objRoleAclLinks->data;
	}//end function

	public function fetchRoleAclResourceRecord($record_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("admin/access-control/fetch-resources/$record_id");

		//execute
		$objRoleAclLinkResource = $objApiRequest->performGETRequest(array())->getBody();

		//create entity
		$objRoleAclLink = $this->createAclResourceEntity(NULL, $objRoleAclLinkResource->data);
		return $objRoleAclLink;
	}//end function

	public function createRoleAclResourceLink(FrontUserRoleAclLinkEntity $objRoleAclResourceLink, $resource_type)
	{
		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objRoleAclResourceLink" => $objRoleAclResourceLink));

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("admin/access-control/fetch-resources");

		//execute
		$objRoleAclResourceLink = $objApiRequest->performPOSTRequest($objRoleAclResourceLink->getArrayCopy())->getBody();

		//recreate the Role Acl Resource Link Entity
		$objRoleAclResourceLink = $this->createAclResourceEntity(NULL, $objRoleAclResourceLink->data);

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objRoleAclResourceLink" => $objRoleAclResourceLink));

		return $objRoleAclResourceLink;
	}//end function

	public function updateRoleAclResourceLink(FrontUserRoleAclLinkEntity $objRoleAclResourceLink)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//validate the link
		$objApiRequest->setApiAction("admin/access-control/fetch-resources/" . $objRoleAclResourceLink->get("id"));
		$objRoleAclResourceLinkCheck = $objApiRequest->performGETRequest(array())->getBody();
		//create the role acl link entity
		$objRoleAclResourceLinkCheck = $this->createAclResourceEntity(NULL, $objRoleAclResourceLinkCheck->data);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objRoleAclResourceLink" => $objRoleAclResourceLink));

		//setup the object and specify the action
		$objApiRequest->setApiAction($objRoleAclResourceLinkCheck->getHyperMedia("edit-role-acl-link")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objRoleAclResourceLink = $objApiRequest->performPUTRequest($objRoleAclResourceLink->getArrayCopy())->getBody();

		//recreate the entity object
		$objRoleAclResourceLink = $this->createAclResourceEntity(NULL, $objRoleAclResourceLink->data);

		//trigger post evet
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objRoleAclResourceLink" => $objRoleAclResourceLink));

		return $objRoleAclResourceLink;
	}//end function

	public function deleteRoleAclResourceLink(FrontUserRoleAclLinkEntity $objRoleAclResourceLink)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//validate the link
		$objApiRequest->setApiAction("admin/access-control/fetch-resources/" . $objRoleAclResourceLink->get("id"));
		$objRoleAclResourceLinkCheck = $objApiRequest->performGETRequest(array())->getBody();
		//create the role acl link entity
		$objRoleAclResourceLinkCheck = $this->createAclResourceEntity(NULL, $objRoleAclResourceLinkCheck->data);

		//trigger pre event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".pre", $this, array("objRoleAclResourceLink" => $objRoleAclResourceLink));

		//setup the object and specify the action
		$objApiRequest->setApiAction($objRoleAclResourceLinkCheck->getHyperMedia("delete-role-acl-link")->url);
		$objApiRequest->setApiModule(NULL);

		//execute
		$objRoleAclResourceLink = $objApiRequest->performDELETERequest(array())->getBody();

		//trigger post event
		$result = $this->getEventManager()->trigger(__FUNCTION__ . ".post", $this, array("objRoleAclResourceLink" => $objRoleAclResourceLink));

		return $objRoleAclResourceLink;
	}//end function
	
	private function createAclResourceEntity($id, $objData)
	{
		$entity_role_acl_link = $this->getServiceLocator()->get("FrontUsers\Entities\FrontUserRoleAclLinkEntity");

		//populate the data
		$entity_role_acl_link->set($objData);

		if (!is_null($id))
		{
			$entity_role_acl_link->set("id", $id);
		}//end if

		return $entity_role_acl_link;
	}//end function
}//end class