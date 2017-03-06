<?php
namespace FrontReports\Controller;

use FrontCore\Adapters\AbstractCoreActionController;
use Zend\View\Model\JsonModel;

class ReportViewerController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Reports Model
	 * @var \FrontReports\Models\FrontReportsModel
	 */
	private $model_reports;

	/**
	 * Container for the Front Report Parameters Model
	 * @var \FrontReports\Models\FrontReportParametersModel
	 */
	private $model_report_parameters;

	public function indexAction()
	{
		//load reports
		try {
			$objReports = $this->getReportsModel()->fetchReports();
		} catch (\Exception $e) {
     		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
     		return $this->redirect()->toRoute('home');
     	}//end catch

		return array(
			"objReports" => $objReports,
		);
	}//end function

	public function basicReportsAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['reports-basic'] != true)
		{
			$this->flashMessenger()->addInfoMessage('The requested view is not available');
			return $this->redirect()->toRoute('front-report-viewer');
		}//end if

		$this->layout('layout/angular/app');

		return array();
	}//end function

	public function ajaxRequestAction()
	{
		$arr_params = $this->params()->fromQuery();
		if (isset($arr_params['acrq']))
		{
			$acrq = $arr_params['acrq'];
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_post_data = json_decode(file_get_contents('php://input'), true);
			if (isset($arr_post_data['acrq']))
			{
				$acrq = $arr_post_data['acrq'];
				unset($arr_post_data['acrq']);
			}//end if
		}//end if

		try {
			switch ($acrq)
			{
				case 'list-basic-reports':
					$objReports = $this->getReportsModel()->fetchReports();
					$arr_reports = array();
					foreach ($objReports as $objReport)
					{
						if (isset($objReport->classification) && strtolower($objReport->classification) == 'basic')
						{
							$arr_reports[] = $objReport;
						}//end if
					}//end foreach

					$objData = new JsonModel(array(
						'objReports' => (object) $arr_reports,
					));
					break;

				case 'delete-cached-report':
					$objResponse = $this->getReportsModel()->deleteCachedReport($arr_params['id'], $arr_params['reference']);

					$objData = new JsonModel(array(
							'objResponse' => $objResponse,
					));
					break;

				case 'load-report-params-form':
					$arr_report_params = (array) json_decode($arr_params['report_params']);
					if (!is_array($arr_report_params))
					{
						$arr_report_params = array('type' => 'report');
					}//end if

					//load the report requirements
					$arr_report_params['auto_populate_form'] = 1;
					$arr_report_params['ds_id'] = 17;
					$objReportParams = $this->getReportsModel()->fetchReportParameters($arr_params['report_id'], $arr_report_params);

					//generate the form
					$form = $this->getReportsModel()->generateReportParametersForm($objReportParams->data->form);

					//populate form where fields are available
					foreach($objReportParams->data->report->form_data as $field => $objField)
					{
						if (!$form->has('#' . $field))
						{
							continue;
						}//end if

						if (!is_object($objField) || !isset($objField->data_element))
						{
							continue;
						}//end if

						$arr_data = array();
						foreach ($objField->data_element as $objValue)
						{
							$arr_data[$objValue->id] = $objValue->value;
						}//end foreach

						$form->get('#' . $field)->setOption('value_options', $arr_data);
					}//end foreach

					$objForm = $this->renderSystemAngularFormHelper($form, NULL);

					$objData = new JsonModel(array(
						'objForm' => $objForm,
						'objReportParams' => $objReportParams,
					));
					break;
			}//end function

			if (isset($objData))
			{
				return $objData;
			}//end if
		} catch (\Exception $e) {
			return new JsonModel(array(
					'error' => 1,
					'response' => $e->getMessage(),
			));
		}//end catch

		return new JsonModel(array(
			'error' => 1,
			'response' => 'An invalid request has been received',
		));
	}//end function

	public function ajaxRequestBasicReportsAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['reports-basic'] != true)
		{
			return new JsonModel(array(
					'error' => 1,
					'response' => 'Requested functionality is not available',
			));
		}//end if

		$arr_params = $this->params()->fromQuery();
		if (isset($arr_params['acrq']))
		{
			$acrq = $arr_params['acrq'];
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_post_data = json_decode(file_get_contents('php://input'), true);
			if (isset($arr_post_data['acrq']))
			{
				$acrq = $arr_post_data['acrq'];
				unset($arr_post_data['acrq']);
			}//end if
		}//end if

		try {
			switch ($acrq)
			{
				case 'list-cached-reports':
					$objCachedReports = $this->getReportsModel()->fetchCachedReports();
					$arr_reports = array();
					foreach($objCachedReports as $objReport)
					{
						$arr_reports[] = $objReport;
					}//end foreach

					$objData = new JsonModel(array(
							'objReports' => (object) $arr_reports,
					));
					break;

				case 'generate-cached-report':
					$report_id = $arr_post_data['report_id'];
					unset($arr_post_data['report_id']);

					$report_type = $arr_post_data['report_type'];
					unset($arr_post_data['report_type']);

					if (isset($arr_post_data['download_option']))
					{
						$download = $arr_post_data['download_option'];
						unset($arr_post_data['download_option']);
					} else {
						$download = 0;
					}//end if

					if ($download > 0)
					{
						$this->getReportsModel()->setDownloadFlag($download);
					}//end if

					$objResponse = $this->getReportsModel()->generateCachedReport($report_id, $report_type, $arr_post_data);
					$objData = new JsonModel(array(
							'objResponse' => $objResponse,
					));
					break;

				case 'load-cached-report-content':
					$objResponse = $this->getReportsModel()->fetchCachedReport($arr_params['id'], $arr_params['reference']);

					$objData = new JsonModel(array(
							'objResponse' => $objResponse,
					));
					break;
			}//end function

			if (isset($objData))
			{
				return $objData;
			}//end if
		} catch (\Exception $e) {
			return new JsonModel(array(
					'error' => 1,
					'response' => $e->getMessage(),
			));
		}//end catch

		return new JsonModel(array(
				'error' => 1,
				'response' => 'An invalid request has been received',
		));
	}//end function

	public function dashboardReportsAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['reports-dashboards'] != true)
		{
			$this->flashMessenger()->addInfoMessage('The requested view is not available');
			return $this->redirect()->toRoute('home');
		}//end if

		$this->layout('layout/angular/app');

		return array();
	}//end function

	public function ajaxDashboardsRequestAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['reports-dashboards'] != true)
		{
			$this->flashMessenger()->addInfoMessage('The requested view is not available');
			return $this->redirect()->toRoute('home');
		}//end if

		$arr_params = $this->params()->fromQuery();
		if (isset($arr_params['acrq']))
		{
			$acrq = $arr_params['acrq'];
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_post_data = json_decode(file_get_contents('php://input'), true);
			if (isset($arr_post_data['acrq']))
			{
				$acrq = $arr_post_data['acrq'];
				unset($arr_post_data['acrq']);
			}//end if
		}//end if

		try {
			switch ($acrq)
			{
				case 'load-dashboard-combined-data':
					$objSourceData = $this->getReportsModel()->loadDashboardData(array('callback' => 'loadContactSourcesSummary'));

					//format source data output
					$arr_source_data = array();
					foreach ($objSourceData->sources_breakdown as $objEntry)
					{
						if (!isset($arr_source_data[$objEntry->datetime_created_formatted_month]))
						{
							$arr_source_data[$objEntry->datetime_created_formatted_month] = array(
									'name' => 'Source: ' . $objEntry->datetime_created_formatted_month,
									'name_series' => $objEntry->datetime_created_formatted_month,
									'stack' => 'sources' . strtolower(str_replace(' ', '', $objEntry->datetime_created_formatted_month)),
							);
						}//end if

						if ($objEntry->source == '')
						{
							$objEntry->source = 'Blank';
						}//end if

						$arr_source_data[$objEntry->datetime_created_formatted_month]['data_temp'][] = array(
								'time' => strtotime('10 ' . $objEntry->datetime_created_formatted_month) * 1000,
								'value' => $objEntry->count_source * 1,
								'label' => $objEntry->source,
						);
					}//end foreach

					$objReferenceData = $this->getReportsModel()->loadDashboardData(array('callback' => 'loadContactReferencesSummary'));

					//format reference data output
					$arr_reference_data = array();
					foreach ($objReferenceData->reference_breakdown as $objEntry)
					{
						if (!isset($arr_reference_data[$objEntry->datetime_created_formatted_month]))
						{
							$arr_reference_data[$objEntry->datetime_created_formatted_month] = array(
									'name' => 'Reference: ' . $objEntry->datetime_created_formatted_month,
									'name_series' => $objEntry->datetime_created_formatted_month,
									'stack' => 'references',
							);
						}//end if

						if ($objEntry->reference == '')
						{
							$objEntry->reference = 'Blank';
						}//end if

						$arr_reference_data[$objEntry->datetime_created_formatted_month]['data_temp'][] = array(
								'time' => strtotime('10 ' . $objEntry->datetime_created_formatted_month) * 1000,
								'value' => $objEntry->count_reference * 1,
								'label' => $objEntry->reference,
						);
					}//end foreach

					$objStatusData = $this->getReportsModel()->loadDashboardData(array('callback' => 'loadContactStatusesHistorySummary'));

					//format status data output
					$arr_status_data = array();
					foreach ($objStatusData->status_history_breakdown as $objEntry)
					{
						if (!isset($arr_status_data[$objEntry->datetime_created_formatted_month]))
						{
							$arr_status_data[$objEntry->datetime_created_formatted_month] = array(
									'name' => 'Statuses: ' . $objEntry->datetime_created_formatted_month,
									'stack' => 'statuses',
									'name_series' => $objEntry->datetime_created_formatted_month,
							);
						}//end if

						$arr_status_data[$objEntry->datetime_created_formatted_month]['data_temp'][] = array(
								'time' => strtotime('10 ' . $objEntry->datetime_created_formatted_month) * 1000,
								'value' => $objEntry->count_status * 1,
								'label' => $objEntry->registration_status_status,
						);
					}//end foreach

					$objGrowthData = $this->getReportsModel()->loadDashboardData(array('callback' => 'loadProfileGrowthSummary'));

					//format status data output
					$arr_growth_data = array();
					foreach ($objGrowthData->profile_growth_breakdown as $objEntry)
					{
						if (!isset($arr_growth_data[$objEntry->datetime_created_formatted_month]))
						{
							$arr_growth_data[$objEntry->datetime_created_formatted_month] = array(
									'name' => 'Growth: ' . $objEntry->datetime_created_formatted_month,
									'stack' => 'growth',
									'name_series' => $objEntry->datetime_created_formatted_month,
							);
						}//end if

						$arr_growth_data[$objEntry->datetime_created_formatted_month]['data_temp'][] = array(
								'time' => strtotime('10 ' . $objEntry->datetime_created_formatted_month) * 1000,
								'value' => $objEntry->count_contacts * 1,
								'label' => 'New Contacts',
						);
					}//end foreach

					$objUnsubscribeData = $this->getReportsModel()->loadDashboardData(array('callback' => 'loadContactUnsubscribeSummary'));

					//format status data output
					$arr_unsub_data = array();
					foreach ($objUnsubscribeData->unsubscribe_breakdown as $objEntry)
					{
						if (!isset($arr_unsub_data[$objEntry->datetime_created_formatted_month]))
						{
							$arr_unsub_data[$objEntry->datetime_created_formatted_month] = array(
									'name' => 'Unsubscribed: ' . $objEntry->datetime_created_formatted_month,
									'stack' => 'unsubscribe',
									'name_series' => $objEntry->datetime_created_formatted_month,
							);
						}//end if

						$arr_unsub_data[$objEntry->datetime_created_formatted_month]['data_temp'][] = array(
								'time' => strtotime('10 ' . $objEntry->datetime_created_formatted_month) * 1000,
								'value' => $objEntry->count_contacts * 1,
								'label' => 'Unsubscribed Contacts',
						);
					}//end foreach

					$objResponse = new JsonModel(array(
							'objData' => (object) array(
									'sources' => $arr_source_data,
									'references' => $arr_reference_data,
									'contact_statuses' => $arr_status_data,
									'profile_growth' => $arr_growth_data,
									'contacts_unsubscribed' => $arr_unsub_data,
							)
					));
					break;

				case 'load-dashboard-source-data':
					$objSourceData = $this->getReportsModel()->loadDashboardData(array('callback' => 'loadContactSourcesSummary'));

					//format source data output
					$arr_source_data = array();
					foreach ($objSourceData->sources_breakdown as $objEntry)
					{
						if (!isset($arr_source_data[$objEntry->datetime_created_formatted_month]))
						{
							$arr_source_data[$objEntry->datetime_created_formatted_month] = array(
									'name' => 'Source: ' . $objEntry->datetime_created_formatted_month,
									'name_series' => $objEntry->datetime_created_formatted_month,
									//'stack' => 'sources' . strtolower(str_replace(' ', '', $objEntry->datetime_created_formatted_month)),
							);
						}//end if

						if ($objEntry->source == '')
						{
							$objEntry->source = 'Blank';
						}//end if

						$arr_source_data[$objEntry->datetime_created_formatted_month]['data_temp'][] = array(
								'time' => strtotime('10 ' . $objEntry->datetime_created_formatted_month) * 1000,
								'value' => $objEntry->count_source * 1,
								'label' => $objEntry->source,
						);
					}//end foreach

					$objResponse = new JsonModel(array(
							'objData' => (object) array(
									'sources' => $arr_source_data,
							)
					));
					break;

				case 'load-dashboard-reference-data':
					$objReferenceData = $this->getReportsModel()->loadDashboardData(array('callback' => 'loadContactReferencesSummary'));

					//format reference data output
					$arr_reference_data = array();
					foreach ($objReferenceData->reference_breakdown as $objEntry)
					{
						if (!isset($arr_reference_data[$objEntry->datetime_created_formatted_month]))
						{
							$arr_reference_data[$objEntry->datetime_created_formatted_month] = array(
									'name' => 'Reference: ' . $objEntry->datetime_created_formatted_month,
									'name_series' => $objEntry->datetime_created_formatted_month,
									'stack' => 'references',
							);
						}//end if

						if ($objEntry->reference == '')
						{
							$objEntry->reference = 'Blank';
						}//end if

						$arr_reference_data[$objEntry->datetime_created_formatted_month]['data_temp'][] = array(
								'time' => strtotime('10 ' . $objEntry->datetime_created_formatted_month) * 1000,
								'value' => $objEntry->count_reference * 1,
								'label' => $objEntry->reference,
						);
					}//end foreach

					$objResponse = new JsonModel(array(
							'objData' => (object) array(
									'references' => $arr_reference_data,
							)
					));
					break;

				case 'load-dashboard-user-data':

					break;

				case 'load-dashboard-status-data':
					$objStatusData = $this->getReportsModel()->loadDashboardData(array('callback' => 'loadContactStatusesHistorySummary'));

					//format status data output
					$arr_status_data = array();
					foreach ($objStatusData->status_history_breakdown as $objEntry)
					{
						if (!isset($arr_status_data[$objEntry->datetime_created_formatted_month]))
						{
							$arr_status_data[$objEntry->datetime_created_formatted_month] = array(
									'name' => 'Statuses: ' . $objEntry->datetime_created_formatted_month,
									'stack' => 'statuses',
									'name_series' => $objEntry->datetime_created_formatted_month,
							);
						}//end if

						$arr_status_data[$objEntry->datetime_created_formatted_month]['data_temp'][] = array(
								'time' => strtotime('10 ' . $objEntry->datetime_created_formatted_month) * 1000,
								'value' => $objEntry->count_status * 1,
								'label' => $objEntry->registration_status_status,
						);
					}//end foreach

					$objResponse = new JsonModel(array(
							'objData' => (object) array(
									'contact_statuses' => $arr_status_data
							)
					));
					break;

				case 'load-dashboard-unsubscribe-data':
					$objUnsubscribeData = $this->getReportsModel()->loadDashboardData(array('callback' => 'loadContactUnsubscribeSummary'));

					//format status data output
					$arr_unsub_data = array();
					foreach ($objUnsubscribeData->unsubscribe_breakdown as $objEntry)
					{
						if (!isset($arr_unsub_data[$objEntry->datetime_created_formatted_month]))
						{
							$arr_unsub_data[$objEntry->datetime_created_formatted_month] = array(
									'name' => 'Unsubscribed: ' . $objEntry->datetime_created_formatted_month,
									'stack' => 'unsubscribe',
									'name_series' => $objEntry->datetime_created_formatted_month,
							);
						}//end if

						$arr_unsub_data[$objEntry->datetime_created_formatted_month]['data_temp'][] = array(
								'time' => strtotime('10 ' . $objEntry->datetime_created_formatted_month) * 1000,
								'value' => $objEntry->count_contacts * 1,
								'label' => 'Unsubscribed Contacts',
						);
					}//end foreach

					$objResponse = new JsonModel(array(
							'objData' => (object) array(
									'contacts_unsubscribed' => $arr_unsub_data
							)
					));
					break;

				case 'load-dashboard-dbgrowth-data':
					$objGrowthData = $this->getReportsModel()->loadDashboardData(array('callback' => 'loadProfileGrowthSummary'));

					//format status data output
					$arr_growth_data = array();
					foreach ($objGrowthData->profile_growth_breakdown as $objEntry)
					{
						if (!isset($arr_growth_data[$objEntry->datetime_created_formatted_month]))
						{
							$arr_growth_data[$objEntry->datetime_created_formatted_month] = array(
									'name' => 'Growth: ' . $objEntry->datetime_created_formatted_month,
									'stack' => 'growth',
									'name_series' => $objEntry->datetime_created_formatted_month,
							);
						}//end if

						$arr_growth_data[$objEntry->datetime_created_formatted_month]['data_temp'][] = array(
								'time' => strtotime('10 ' . $objEntry->datetime_created_formatted_month) * 1000,
								'value' => $objEntry->count_contacts * 1,
								'label' => 'New Contacts',
						);
					}//end foreach

					$objResponse = new JsonModel(array(
							'objData' => (object) array(
									'profile_growth' => $arr_growth_data
							)
					));
					break;
			}//end switch

			if (isset($objResponse))
			{
				return $objResponse;
			}//end if
		} catch (\Exception $e) {
			return new JsonModel(array(
					'error' => 1,
					'response' => $e->getMessage(),
			));
		}//end catch

		return new JsonModel(array(
				'error' => 1,
				'response' => 'An invalid request has been received',
		));
	}//end function

	public function ajaxAngRequestBasicReportsAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['reports-basic'] != true)
		{
			return new JsonModel(array(
					'error' => 1,
					'response' => 'Requested functionality is not available',
			));
		}//end if

		$arr_params = $this->params()->fromQuery();
		if (isset($arr_params['acrq']))
		{
			$acrq = $arr_params['acrq'];
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_post_data = json_decode(file_get_contents('php://input'), true);
			if (isset($arr_post_data['acrq']))
			{
				$acrq = $arr_post_data['acrq'];
				unset($arr_post_data['acrq']);
			}//end if
		}//end if

		try {
			switch ($acrq)
			{
				case 'list-cached-reports':
					$objCachedReports = $this->getReportsModel()->fetchCachedReports();
					$arr_reports = array();
					foreach($objCachedReports as $objReport)
					{
						$arr_reports[] = $objReport;
					}//end foreach

					$objData = new JsonModel(array(
						'objReports' => (object) $arr_reports,
					));
					break;

				case 'generate-cached-report':
					$report_id = $arr_post_data['report_id'];
					unset($arr_post_data['report_id']);

					$report_type = $arr_post_data['report_type'];
					unset($arr_post_data['report_type']);

					if (isset($arr_post_data['download_option']))
					{
						$download = $arr_post_data['download_option'];
						unset($arr_post_data['download_option']);
					} else {
						$download = 0;
					}//end if

					if ($download > 0)
					{
						$this->getReportsModel()->setDownloadFlag($download);
					}//end if

					$objResponse = $this->getReportsModel()->generateCachedReport($report_id, $report_type, $arr_post_data);
					$objData = new JsonModel(array(
						'objResponse' => $objResponse,
					));
					break;

				case 'load-cached-report-content':
					$objResponse = $this->getReportsModel()->fetchCachedReport($arr_params['id'], $arr_params['reference']);

					$objData = new JsonModel(array(
						'objResponse' => $objResponse,
					));
					break;
			}//end function

			if (isset($objData))
			{
				return $objData;
			}//end if
		} catch (\Exception $e) {
			return new JsonModel(array(
					'error' => 1,
					'response' => $e->getMessage(),
			));
		}//end catch

		return new JsonModel(array(
				'error' => 1,
				'response' => 'An invalid request has been received',
		));
	}//end function

	public function compositeReportsAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['reports-advanced'] != true)
		{
			$this->flashMessenger()->addInfoMessage('The requested view is not available');
			return $this->redirect()->toRoute('front-report-viewer');
		}//end if

		$this->layout('layout/angular/app');

		return array();
	}//end function

	public function indexDashboardsAction()
	{
		//load reports
		try {
			$objReports = $this->getReportsModel()->fetchDashboards();
		} catch (\Exception $e) {
     		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
     		return $this->redirect()->toRoute('home');
     	}//end catch

		return array(
				"objReports" => $objReports,
		);
	}//end function

	public function ajaxLoadReportFormAction()
	{
		$id = $this->params()->fromRoute("id", "");
		$type = $this->params()->fromQuery("type", "");

		if ($id == "" || $type == "")
		{
			echo json_encode(array("error" => 1, "response" => "Report could not be loaded. Required values are not set"), JSON_FORCE_OBJECT); exit;
		}//end if

		try {
			//load the report requirements
			$objReport = $this->getReportsModel()->fetchReportParameters($id, (array) $this->params()->fromQuery());

			//generate the form
			$form = $this->getReportsModel()->generateReportParametersForm($objReport->data->form);

			//set view layout
			$this->layout("layout/body-pane");

			return array(
				"form" => $form,
				"objReport" => $objReport,
			);
		} catch (\Exception $e) {
			echo json_encode(array("error" => "1", "response" => $e->getMessage()), JSON_FORCE_OBJECT); exit;
		}//end catch
	}//end function

	public function ajaxLoadReportOutputAction()
	{
		$id = $this->params()->fromRoute("id", "");
		$type = $this->params()->fromQuery("type", "");
		$download = $this->params()->fromQuery("download", "0");

		if ($id == "" || $type == "")
		{
			echo json_encode(array("error" => 1, "response" => "Report could not be loaded. Required values are not set"), JSON_FORCE_OBJECT); exit;
		}//end if

		try {
			//load the report requirements
			$objReport = $this->getReportsModel()->fetchReportParameters($id, (array) $this->params()->fromQuery());

			//generate the form
			$form = $this->getReportsModel()->generateReportParametersForm($objReport->data->form);

			$request = $this->getRequest();
			if ($request->isPost())
			{
				$arr_data = (array) $request->getPost();
				$this->getReportsModel()->setDownloadFlag($download);
				$objGeneratedReport = $this->getReportsModel()->generateReport($id, $type, $arr_data);

				//amend some stuff in report output
				$report_output = $objGeneratedReport->report_output;
				$report_output = str_replace('<script type="text/javascript" src="https://code.highcharts.com/highcharts.js"></script>', '', $report_output);
				$report_output = str_replace('<script src="https://code.highcharts.com/modules/exporting.js"></script>', '', $report_output);
				$objGeneratedReport->report_output = $report_output;
				$objGeneratedReport->download_flag = $download;

				echo json_encode(array(
						"error" => 0,
						"objReport" => $objGeneratedReport,
				), JSON_FORCE_OBJECT);
				exit;
			} else {
				echo json_encode(array(
						"error" => 1,
						"response" => "Report could not be loaded. Required parameters are not set",
				), JSON_FORCE_OBJECT);
				exit;
			}//end if
		} catch (\Exception $e) {
			echo json_encode(array("error" => "1", "response" => $e->getMessage()), JSON_FORCE_OBJECT); exit;
		}//end catch
	}//end function

	public function ajaxLoadReportParamAction()
	{
		$field = $this->params()->fromQuery("field");

		try {
			$arr = $this->getReportParametersModel()->generateFieldContent(str_replace("#", "", $field));
			if (!$arr || is_null($arr))
			{
				$arr = "";
			}//end if

			echo json_encode(array(
				"error" => 0,
				"response" => $arr,
			), JSON_FORCE_OBJECT);
			exit;
		} catch (\Exception $e) {
			echo json_encode(array(
				"error" => 1,
				"response" => $e->getMessage(),
			), JSON_FORCE_OBJECT);
			exit;
		}//end catch
	}//end function

	public function reportViewerAction()
	{
		$id = $this->params()->fromRoute("id", "");
		$type = $this->params()->fromQuery("type", "");
		$op = $this->params()->fromQuery("op", "");

		if ($id == "" || $type == "")
		{
			$this->flashMessenger()->addErrorMessage("Report could not be loaded. Required values are not set");
			return $this->redirect()->toRoute("front-report-viewer");
		}//end if

		//load reports list
		switch (strtolower($op))
		{
			case "dashboard":
				$objReports = $this->getReportsModel()->fetchDashboards();
				break;

			default:
				$objReports = $this->getReportsModel()->fetchReports();
				break;
		}//end switch


		//load the report requirements
		try {
			$objReport = $this->getReportsModel()->fetchReportParameters($id, (array) $this->params()->fromQuery());
		} catch (\Exception $e) {
     		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
     		return $this->redirect()->toRoute('home');
     	}//end catch

		//generate the form
		$form = $this->getReportsModel()->generateReportParametersForm($objReport->data->form);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid($request->getPost()))
			{
				$arr_data = $form->getData();
				$str = "";
				foreach ($arr_data as $k => $v)
				{
					$str .= "$k=$v&";
				}//end foreach
				$str = rtrim($str, "&");

				return $this->redirect()->toUrl($this->url()->fromRoute("front-report-viewer", array("id" => $id, "action" => "report-viewer")) . "?type=$type&$str");
			}//end if
		}//end if

		return array(
			"form" => $form,
			"objReport" => $objReport,
			"objReports" => $objReports,
			"op" => $op,
		);
	}//end function

	/**
	 * Create an instance of the Reports Model using the Service Manager
	 * @return \FrontReports\Models\FrontReportsModel
	 */
	private function getReportsModel()
	{
		if (!$this->model_reports)
		{
			$this->model_reports = $this->getServiceLocator()->get("FrontReports\Models\FrontReportsModel");
		}//end if

		return $this->model_reports;
	}//end function

	/**
	 * Create an instance of the Report Parameters Model using the Service Manager
	 * @return \FrontReports\Models\FrontReportParametersModel
	 */
	private function getReportParametersModel()
	{
		if (!$this->model_report_parameters)
		{
			$this->model_report_parameters = $this->getServiceLocator()->get("FrontReports\Models\FrontReportParametersModel");
		}//end if

		return $this->model_report_parameters;
	}//end function
}//end class