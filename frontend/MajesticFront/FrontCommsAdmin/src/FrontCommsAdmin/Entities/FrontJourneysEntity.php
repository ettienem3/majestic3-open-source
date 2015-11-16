<?php
namespace FrontCommsAdmin\Entities;

use FrontCore\Adapters\AbstractEntityAdapter;

class FrontJourneysEntity extends AbstractEntityAdapter
{
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