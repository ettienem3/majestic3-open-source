<?php
namespace FrontReports\Controller;

use Zend\View\Model\ViewModel;
use FrontCore\Adapters\AbstractCoreActionController;

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
		$objReports = $this->getReportsModel()->fetchReports();

		return array(
			"objReports" => $objReports,
		);
	}//end function

	public function indexDashboardsAction()
	{
		//load reports
		$objReports = $this->getReportsModel()->fetchDashboards();

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
		$objReport = $this->getReportsModel()->fetchReportParameters($id, (array) $this->params()->fromQuery());

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