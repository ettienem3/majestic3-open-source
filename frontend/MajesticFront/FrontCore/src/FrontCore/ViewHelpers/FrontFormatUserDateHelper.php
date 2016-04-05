<?php
namespace FrontCore\ViewHelpers;

use Zend\View\Helper\AbstractHelper;

class FrontFormatUserDateHelper extends AbstractHelper
{
	/**
	 * Container for the profile configuration
	 * @var array
	 */
	private $arr_config;

	/**
	 * Det default output format
	 * Override with $arr_date["options"]["output_format"]
	 * @var string
	 */
	private $output_format = "d M Y H:i:s";

	/**
	 * Enable or disable exception
	 * @var bool
	 */
	private $bool_throw_exception = TRUE;

	/**
	 * Enable or disable error reporting
	 * @var bool
	 */
	private $bool_report_errors = TRUE;

	/**
	 * Generate a standard view heading
	 * @param string $header_html
	 * @return string
	 */
	public function __invoke($arr_date)
	{
		//set options
		if (isset($arr_date["options"]))
		{
			foreach ($arr_date["options"] as $k => $v)
			{
				$this->$k = $v;
			}//end foreach
		}//end if

		//is the date specified?
		if (!isset($arr_date["date"]) || $arr_date["date"] == "" || $arr_date['date'] == '0000-00-00 00:00:00' || $arr_date['date'] == '0000-00-00')
		{
			return FALSE;
		}//end if

		try {
			//create date object and check date is utc formatted
			$objDate = \DateTime::createFromFormat(\DateTime::RFC3339, $arr_date["date"]);

			//was the date received valid?
			if (!$objDate)
			{
				if ($this->bool_report_errors === TRUE)
				{
					trigger_error("Date '" . $arr_date['date'] . "' is not a valid UTC formatted date", E_USER_WARNING);
					return FALSE;
				}//end if
			}//end if

			//format date to requested format and apply user timezone
			$date = $objDate->format($this->output_format);
			return $date;
		} catch (\Exception $e) {
			if ($this->bool_report_errors === TRUE)
			{
				//ignore error
				trigger_error($e->getMessage(), E_USER_NOTICE);
			}//end if

			if ($this->bool_throw_exception === TRUE)
			{
				throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : " . $e->getMessage(), $e->getCode());
			}//end if

			return $arr_date["date"];
		}//end catch
	}//end function

	/**
	 * Set profile config
	 * @param array $arr_config
	 */
	public function setProfileConfig($arr_config)
	{
		$this->arr_config = $arr_config;
	}//end function
}//end function