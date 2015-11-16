<?php
namespace FrontReports\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontReportsModel extends AbstractCoreAdapter
{
	/**
	 * Container for the System Forms Model
	 * @var \FrontCore\Models\SystemFormsModel
	 */
	private $objCoreForm;

	/**
	 * Flag indicating if a report should be requested for a download
	 * @var int
	 */
	private $flag_download = 0;

	/**
	 * Load a list of available reports for the profile
	 * @return stdClass
	 */
	public function fetchReports()
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("reports/basic/view");

		//execute
		$objReports = $objApiRequest->performGETRequest(array())->getBody();
		return $objReports->data;
	}//end function

	public function fetchReportParameters($id, array $arr_params)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("reports/basic/view/$id");
		$arr_params["download"] = $this->flag_download;
		
		//execute
		$objReports = $objApiRequest->performGETRequest($arr_params)->getBody();
		return $objReports->data;
	}//end functin

	public function generateReport($id, $type, $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("reports/basic/view/$id?type=$type&download=" . $this->flag_download);

		//execute
		$objReport = $objApiRequest->performPUTRequest($arr_data)->getBody();
		return $objReport->data;
	}//end function

	public function generateReportParametersForm($objForm)
	{
		$this->objCoreForm = $this->getServiceLocator()->get("FrontCore\Models\SystemFormsModel");

		//construct the form
		$form = $this->objCoreForm->constructCustomForm($objForm);
		return $form;
	}//end function

	public function setDownloadFlag($value)
	{
		$this->flag_download = $value;
	}//end function
}//end class