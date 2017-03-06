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

			if (isset($objData->additional_link_data) && is_array($objData->additional_link_data))
			{
				if (isset($objData->additional_link_data['qp_start']))
				{
					unset($objData->additional_link_data['qp_start']);
				}//end if

				if (isset($objData->additional_link_data['qp_limit']))
				{
					unset($objData->additional_link_data['qp_limit']);
				}//end if

				$append_string = '&' . http_build_query($objData->additional_link_data);
			} else {
				$append_string = '';
			}//end if

			foreach ($objData->page_urls as $key => $objPage)
			{
				if ($objPage->next == 0)
				{
					$string .= "<li><a href=\"$url_route?qp_limit=" . $objData->qp_limit . "&qp_start=" . $objPage->next . "$append_string\" aria-label=\"First\" title=\"First\"><span aria-hidden=\"true\">&laquo;</span></a></li>";
					continue;
				}//end if

				$string2 .= "<li><a  href=\"$url_route?qp_limit=" . $objData->qp_limit . "&qp_start=" . $objPage->next . "$append_string\">" . $key . "</a></li>";
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

			$string .= 		"<li><a  href=\"$url_route?qp_limit=" . $objData->qp_limit . "&qp_start=" . $objPage->next . "$append_string\" aria-label=\"Last\" title=\"Last\"><span aria-hidden=\"true\">&raquo;</span></a></li>";
			$string .=   "</ul>";
			$string .=	"</nav>";

			return $string;
		}//end if
	}//end function
}//end class
