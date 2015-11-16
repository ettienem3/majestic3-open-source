<?php

namespace FrontCore\ViewHelpers;

use Zend\View\Helper\AbstractHelper;

class FrontRenderNotificationHelper extends AbstractHelper
{

	public function __invoke($notification,$header,$type,$style,$styled)
	{
		if ($styled == false)
		{
			$fresult = "<span class=\"$style\">$header<br>$notification";
		} else {
			$fresult = "<fieldset class=\"$type ui-widget ui-corner-all\" style=\"$style\"><legend>";
			$fresult .= "$header</legend>";

			if (is_array($notification))
			{
				for ($i=0; $i<count($notification); $i++)
				{
					$fresult .= $notification[$i] . "<br>";
				}//end for
			} else {
				if (is_string($notification))
				{
					$fresult .= $notification;
				}//end if
			}//end if

			$fresult .= "</fieldset><br>";
		}//end if

		return $fresult;
	}// function ends
}// class ends
