<?php
namespace FrontCommsAdmin\Entities;

use FrontCore\Adapters\AbstractEntityAdapter;

class FrontJourneysEntity extends AbstractEntityAdapter
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
				if (parent::get("date_expiry") == "0000-00-00" || parent::get("date_expiry") == "")
				{
					return "";
				}//end if

				$date = $this->getUserDateFormatHelper()->__invoke(array(
					"date" => parent::get("date_expiry"),
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

		//manipulate date to valid format
		if ($arr_data["date_expiry"] != "")
		{
			$objDate = \DateTime::createFromFormat($this->format_date, $arr_data["date_expiry"]);
			if (!$objDate)
			{
				throw new \Exception(__CLASS__ . " : Line " . __LINE__ . " : An error occurred setting the expiry date", 500);
			}//end if
			$arr_data["date_expiry"] = $objDate->format('c');
		}//end if

		return $arr_data;
	}//end function

	public function isExpired()
	{
		if ($this->get("date_expiry") != "0000-00-00" && $this->get("date_expiry") != "")
		{
			$arr_ex = explode("-", $objJourney->date_expiry);
			$ex_time = mktime(0, 0, 0, (int) $arr_ex[1], (int) $arr_ex[2], (int) $arr_ex[0]);

			if (time() > $ex_time)
			{
				return TRUE;
			}//end if
		}//end if

		return FALSE;
	}//end function
}//end class