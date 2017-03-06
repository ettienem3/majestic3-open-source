<?php
namespace FrontReports\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontReportsModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Report Settings Model
	 * @var \FrontReports\Models\FrontReportSettingsModel
	 */
	private $model_report_settings;

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
	 * Instance of the Core Cahce Manager
	 * @var \FrontCore\Caches\FrontCachesRedis
	 */
	private $storageFactory;

	/**
	 * Load a list of available reports for the profile
	 * @param boolean $flag_fetch_all_reports - Default false. Set to true will ignore profile report available options
	 * @param array $arr_options - array - Placeholder for future additional options
	 * @return stdClass
	 */
	public function fetchReports($flag_fetch_all_reports = FALSE, $arr_options = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("reports/basic/view");

		//execute
		$objReports = $objApiRequest->performGETRequest(array())->getBody();

		//compare report list against profile settings
		if ($flag_fetch_all_reports === FALSE)
		{
			$arr_reports_available = $this->getFrontReportSettingsModel()->getReportsAvailableSettings();
			if (is_array($arr_reports_available) && count($arr_reports_available) > 0)
			{
				foreach ($objReports->data as $k => $objReport)
				{
					if (!isset($objReport->id) || !is_numeric($objReport->id))
					{
						continue;
					}//end if

					if (!in_array($objReport->id, $arr_reports_available))
					{
						unset($objReports->data->$k);
					}//end if
				}//end foreach
			}//end if
		}//end if

		return $objReports->data;
	}//end function

	public function fetchDashboards()
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("reports/dashboard/view");

		//execute
		$objReports = $objApiRequest->performGETRequest(array())->getBody();

		//compare report list against profile settings
		$arr_reports_available = $this->getFrontReportSettingsModel()->getReportsAvailableSettings();
		if (is_array($arr_reports_available))
		{
			foreach ($objReports->data as $k => $objReport)
			{
				if (!isset($objReport->id) || !is_numeric($objReport->id))
				{
					continue;
				}//end if

				if (!in_array($objReport->id, $arr_reports_available))
				{
					unset($objReports->data->$k);
				}//end if
			}//end foreach
		}//end if

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

	/**
	 * List current cached reports available to profile
	 * Reports are cached for 24 hours
	 * @param array $arr_params
	 * @return array
	 */
	public function fetchCachedReports($arr_params = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("reports/cached/operations");

		//execute
		$objReports = $objApiRequest->performGETRequest($arr_params)->getBody();
		return $objReports->data;
	}//end function

	/**
	 * Poll status of cached reports
	 * @param integer $id - ID of the cached report record, not the actual report ID
	 * @param string $reference - Reference value received from requesting the report
	 * @param array $arr_params - Optional, for future use
	 * @return array
	 */
	public function fetchCachedReport($id, $reference, $arr_params = array())
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("reports/cached/operations/$id?reference=$reference");

		//execute
		$objReport = $objApiRequest->performGETRequest($arr_params)->getBody();
		return $objReport->data;
	}//end function

	/**
	 * Generate basic format reports
	 * This makes use of the cache capabiliy for all reports to avoid timeout on large or
	 * long running reports
	 * @param integer $id - Report specific id
	 * @param string $type - report|composite|dashboard
	 * @param array $arr_data
	 */
	public function generateCachedReport($id, $type, $arr_data)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("reports/cached/operations/$id?report_type=$type&download=" . $this->flag_download);

		//execute
		$objReport = $objApiRequest->performPUTRequest($arr_data)->getBody();
		return $objReport->data;
	}//end function

	/**
	 * Remove a report from the list of cached reports
	 * @param integer $id
	 * @param string $reference
	 * @return object
	 */
	public function deleteCachedReport($id, $reference)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("reports/cached/operations/$id");

		//execute
		$objReport = $objApiRequest->performDELETERequest(array('reference' => $reference, 'debug_display_errors' => 1))->getBody();
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

	/**
	 * Request dashboard data
	 * @param array $arr_where
	 * $arr_where['callback'] is mandatory
	 * Callback Options are: loadContactSourcesSummary,
	 * 							loadContactReferencesSummary,
	 * 							loadContactStatusesHistorySummary,
	 * 							loadContactUserHistorySummary,
	 * 							loadContactDbGrowthSummary,
	 * 							loadContactUnsubscribeSummary
	 */
	public function loadDashboardData(array $arr_where)
	{
		if (!isset($arr_where['callback']) || $arr_where['callback'] == '')
		{
			throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : Callback is not set, request has been aborted", 500);
		}//end if

		//check if data is cached
		$cache_key = __FUNCTION__ . '-' . $arr_where['callback'];
		$objData = $this->readCacheItem($cache_key);
		if ($objData !== FALSE)
		{
			return $objData;
		}//end if

		//create the request object
		$objApiRequest = $this->getApiRequestModel();

		//setup the object and specify the action
		$objApiRequest->setApiAction("contacts");

		$objResult = $objApiRequest->performGETRequest($arr_where)->getBody();

		//cache the data, 12 hours
		$this->setCacheItem($cache_key, $objResult->data, array('ttl' => (12 * 3600)));

		return $objResult->data;
	}//end function

	/**
	 * Retrieve item from cache
	 * @param string $key
	 * @param mixed $default_value
	 */
	private function readCacheItem($key, $default_value = FALSE)
	{
		return $this->getCacheManager()->readCacheItem($key, $default_value);
	}//end function

	/**
	 * Write item to cache
	 * @param string $key
	 * @param mixed $value
	 * @param array $arr_options - optional
	 */
	private function setCacheItem($key, $value, $arr_options = array())
	{
		return $this->getCacheManager()->setCacheItem($key, $value, $arr_options);
	}//end function

	/**
	 * Remove item from cache
	 * @param string $key
	 */
	private function clearCacheItem($key)
	{
		return $this->getCacheManager()->clearItem($key);
	}//end function

	/**
	 * Create an instance of the Core Redis Cache Manager using the Service Manager
	 * @return \FrontCore\Caches\FrontCachesRedis
	 */
	private function getCacheManager()
	{
		if (!$this->storageFactory)
		{
			$this->storageFactory = $this->getServiceLocator()->get("FrontCore\Caches\FrontCachesRedis");
		}//end if

		return $this->storageFactory;
	}//end function

	/**
	 * Create an instance of the Report Settings Model
	 * @return \FrontReports\Models\FrontReportSettingsModel
	 */
	private function getFrontReportSettingsModel()
	{
		if (!$this->model_report_settings)
		{
			$this->model_report_settings = $this->getServiceLocator()->get('FrontReports\Models\FrontReportSettingsModel');
		}//end if

		return $this->model_report_settings;
	}//end function
}//end class