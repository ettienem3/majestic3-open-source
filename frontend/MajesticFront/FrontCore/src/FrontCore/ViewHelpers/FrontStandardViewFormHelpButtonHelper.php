<?php
namespace FrontCore\ViewHelpers;

use Zend\View\Helper\AbstractHelper;

class FrontStandardViewFormHelpButtonHelper extends AbstractHelper
{
	/**
	 * Generate a standard view heading
	 * @param string $header_html
	 * @return string
	 */
	public function __invoke($link_html = "")
	{
		if ($link_html == "")
		{
			$link_html = ICON_MEDIUM_HELP_HTML;
		}//end if

		$html = '<li class="mj3_btnhelp clearfix">';
		$html .= 	'<a class="btn btn-default js-help-toggle" href="" data-toggle="tooltip" data-original-title="Display Form help tips">';
		$html .= 		$link_html;
		$html .= 	'</a>';
		$html .= '</li>';
		return $html;
	}//end function
}//end function