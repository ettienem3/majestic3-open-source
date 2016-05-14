<?php

namespace FrontCore\ViewHelpers;

use Zend\View\Helper\AbstractHelper;

class FrontRenderPaginatorHelper extends AbstractHelper
{
	public function __invoke($objData, $url_route)
	{
		//insert pagination data
		if ($objData->pages_total > 0)
		{
			$string3 = '';
			$string = "<hr/>";
			$string .= "<nav>";
			$string .=		"<ul class=\"pagination pull-left\">";

			foreach ($objData->page_urls as $key => $objPage)
			{
				if ($objPage->next == 0)
				{
					$string .= "<li><a href=\"$url_route?qp_limit=" . $objData->qp_limit . "&qp_start=" . $objPage->next . "\" aria-label=\"First\" title=\"First\"><span aria-hidden=\"true\">&laquo;</span></a></li>";
					continue;
				}//end if

				$string2 .= "<li><a  href=\"$url_route?qp_limit=" . $objData->qp_limit . "&qp_start=" . $objPage->next . "\">" . $key . "</a></li>";
			}//end foreach

			if ($objData->pages_total > 20)
			{
				$string .=		'<li class="pull-left" style="margin-left: 10px; margin-right: 10px;"><div class="btn-group">';
				$string .=			'<button class="btn btn-primary">Page to</button>';
				$string .=			'<button data-toggle="dropdown" class="btn btn-success dropdown-toggle"><span class="caret"></span></button>';
				$string .=			'<ul class="dropdown-menu" role="menu">';
				$string .=				$string2;
				$string .=			'</ul>';
				$string .=		'</div></li>';
			} else {
				$string .=	$string2;
			}//end if

			$string .= 		"<li><a  href=\"$url_route?qp_limit=" . $objData->qp_limit . "&qp_start=" . $objPage->next . "\" aria-label=\"Last\" title=\"Last\"><span aria-hidden=\"true\">&raquo;</span></a></li>";
			$string .=   "</ul>";
			$string .=	"</nav>";

			return $string;
		}//end if
	}//end function
}//end class