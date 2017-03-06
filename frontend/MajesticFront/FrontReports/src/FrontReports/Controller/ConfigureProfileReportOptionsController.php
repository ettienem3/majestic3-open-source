<?php
namespace FrontReports\Controller;

use FrontCore\Adapters\AbstractCoreActionController;
use Zend\Form\Form;

class ConfigureProfileReportOptionsController extends AbstractCoreActionController
{
	/**
	 * Container for the Reports Model
	 * @var \FrontReports\Models\FrontReportsModel $model_front_reports
	 */
	private $model_front_reports;

	/**
	 * Container for the Report Config Model
	 * @var \FrontReports\Models\FrontReportSettingsModel
	 */
	private $model_front_reports_config;

	public function indexAction()
	{
		return array();
	}//end function

	public function configureReportsAvailableAction()
	{
		//load reports
		$objReports = $this->getFrontReportsModel()->fetchReports(TRUE);

		//create form
		$form = new Form();
		$form->setAttribute('method', 'post');

		//extract reports into array and sort by name\
		$arr_r = array();
		foreach ($objReports as $objReport)
		{
			if (!isset($objReport->id) || !is_numeric($objReport->id))
			{
				continue;
			}//end if

			$arr_r[$objReport->display_title] = $objReport;
		}//end foreach
		ksort($arr_r);

		foreach ($arr_r as $k => $objReport)
		{
			if (!isset($objReport->id) || !is_numeric($objReport->id))
			{
				continue;
			}//end if

			$report_name = 'report_id_' . $objReport->id;
			$report_label = $objReport->display_title . ' (Type: ' . $objReport->report_generators_name . ')';
			$report_title = $objReport->description;

			$form->add(array(
				'type' => 'checkbox',
				'name' => $report_name,
				'attributes' => array(
					'id' => $report_name,
					'title' => $report_title,
				),
				'options' => array(
					'label' => $report_label,
					'checked_value' => $objReport->id,
					'unchecked_value' => 0,
					'use_hidden_element' => true,
				),
			));
		}//end foreach

		$form->add(array(
			'type' => 'submit',
			'name' => 'submit',
			'attributes' => array(
					'value' => 'Save'
			)
		));

		try {
			$request = $this->getRequest();
			if ($request->isPost())
			{
				$arr_data = (array) $request->getPost();
				unset($arr_data['submit']);

				$arr_report_config = array();
				foreach ($arr_data as $k => $v)
				{
					if (is_numeric($v) && $v > 0)
					{
						$arr_report_config[] = $v;
					}//end foreach
				}//end foreach

				$this->getFrontReportSettingsModel()->setReportsAvailableSettings($arr_report_config);
				$this->flashMessenger()->addSuccessMessage('Options have been saved');
			}//end foreach

			//load current settings
			$arr_reports_available = $this->getFrontReportSettingsModel()->getReportsAvailableSettings();
			if (is_array($arr_reports_available))
			{
				$arr_form_data = array();
				foreach ($arr_reports_available as $k => $v)
				{
					if ($v > 0)
					{
						$arr_form_data['report_id_' . $v] = $v;
					}//end if
				}//end foreach

				$form->setData($arr_form_data);
			}//end if
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
		}//end catch

		return array(
				'form' => $form,
		);
	}//end function

	/**
	 * Create an instance of the Reports Model using the Service Manager
	 * @return \FrontReports\Models\FrontReportsModel
	 */
	private function getFrontReportsModel()
	{
		if (!$this->model_front_reports)
		{
			$this->model_front_reports = $this->getServiceLocator()->get('FrontReports\Models\FrontReportsModel');
		}//end if

		return $this->model_front_reports;
	}//end function

	/**
	 * Create an instance of the Reports Config Model using the Service Manager
	 * @return \FrontReports\Models\FrontReportSettingsModel
	 */
	private function getFrontReportSettingsModel()
	{
		if (!$this->model_front_reports_config)
		{
			$this->model_front_reports_config = $this->getServiceLocator()->get('FrontReports\Models\FrontReportSettingsModel');
		}//end function

		return $this->model_front_reports_config;
	}//end function
}//end class