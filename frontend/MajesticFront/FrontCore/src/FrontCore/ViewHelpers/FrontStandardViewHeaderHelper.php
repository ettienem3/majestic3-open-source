<?php
namespace FrontCore\ViewHelpers;

use Zend\View\Helper\AbstractHelper;

class FrontStandardViewHeaderHelper extends AbstractHelper
{
	/**
	 * Generate a standard view heading
	 * @param string $header_html
	 * @return string
	 */
	public function __invoke($header_html)
	{
		$html = '<nav class="navbar navbar-default">';
		$html .=	'<div class="container-fluid">';
		$html .=		'<div class="navbar-header">';
		$html .=			'<span class="navbar-brand">';
		$html .=				$header_html;
		$html .=			'</span>';
		$html .=		'</div>';
		$html .=	'</div>';
		$html .= '</nav>';
		
		return $html;
	}//end function
}//end function