<?php
namespace FrontCommsAdmin\Entities;

use FrontCore\Adapters\AbstractEntityAdapter;

class FrontCommAdminEntity extends AbstractEntityAdapter
{
	/**
	 * Set date format
	 * @var string
	 */
	private $format_date = "d M Y";

	public function get($key)
	{
		switch ($key)
		{
			case "date_expiry":
			case "date_start":
				if (parent::get($key) == "0000-00-00" || parent::get($key) == "")
				{
					return "";
				}//end if

				$date = $this->getUserDateFormatHelper()->__invoke(array(
						"date" => parent::get($key),
						"options" => array(
								"output_format" => $this->format_date,
						),
				));
				return $date;
				break;

			default:
				return parent::get($key);
				break;
		}//end switch
	}//end function

	/**
	 * (non-PHPdoc)
	 * @see \FrontCore\Adapters\AbstractEntityAdapter::getDataForSubmit()
	 */
	public function getDataForSubmit()
	{
		$arr_data = parent::getDataForSubmit();

		//manipulate dates for compatible api restrictions
		if ($arr_data["date_expiry"] != "")
		{
			$objDate = \DateTime::createFromFormat($this->format_date, $arr_data["date_expiry"]);
			$arr_data["date_expiry"] = $objDate->format("c");
		}//end if

		if ($arr_data["date_start"] != "")
		{
			$objDate = \DateTime::createFromFormat($this->format_date, $arr_data["date_start"]);
			$arr_data["date_start"] = $objDate->format("c");
		}//end if

		return $arr_data;
	}//end function

}//end class