<?php
namespace FrontCore\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

/**
 * This model manages form requests for the Front Package.
 * This requests a form from the API, generates a Form object and returns
 * a Zend Form object.
 *
 * Where a form is submitted, it extracts the data and POST the data only.
 * @author ettiene
 *
 */
class ApiRequestFormsModel extends AbstractCoreAdapter
{
	protected function requestApiForm()
	{
		$model_api_request = $this->getApiRequestModel();
		//set route
		$model_api_request->setApiAction("forms/load");
	}//end function

	protected function submitApiForm()
	{
		$model_api_request = $this->getApiRequestModel();
		//set route
		$model_api_request->setApiAction("forms/submit");
	}//end function

	public function getContactProfilePageForm()
	{

	}//end function

	public function getWebForm()
	{

	}//end function

	public function getSalesFunnelForm()
	{

	}//end function

	public function getViralForm()
	{

	}//end function
}//end function