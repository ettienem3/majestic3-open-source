<?php

namespace FrontCore\ViewHelpers;

use Zend\View\Helper\AbstractHelper;

class FrontRenderPaginatorHelper extends AbstractHelper
{

	public function __invoke($objData, $url_route)
	{
		//insert pagination data
		if ($objData->pages_total > 1)
		{
			$string = "<hr/>";
			$string .= "<nav>";
			$string .=		"<ul class=\"pagination pull-left\">";

			foreach ($objData->page_urls as $key => $objPage)
			{
				if ($objPage->next == 0)
				{
					$string .= "<li><a href=\"$url_route?qp_limit=" . $objData->qp_limit . "&qp_start=" . $objPage->next . "\" aria-label=\"First\"><span aria-hidden=\"true\">&laquo;</span></a></li>";
					continue;
				}//end if

				$string .= "<li><a  href=\"$url_route?qp_limit=" . $objData->qp_limit . "&qp_start=" . $objPage->next . "\">" . $key . "</a></li>";
			}//end foreach

			$string .= 		"<li><a  href=\"$url_route?qp_limit=" . $objData->qp_limit . "&qp_start=" . $objPage->next . "\" aria-label=\"Last\"><span aria-hidden=\"true\">&raquo;</span></a></li>";
			$string .=   "</ul>";
			$string .=	"</nav>";

			return $string;
		}//end if
	}//end function
}//end class
