<?php
namespace FrontPanels\Panels;

use FrontPanels\Entities\FrontPanelsPanelEntity;
use FrontPanels\Interfaces\InterfacePanelsProcessor;

class PanelContactsWidgetReferencesVsContactsProcessorModel extends AbstractPanelProcessorModel implements InterfacePanelsProcessor
{
	/**
	 * (non-PHPdoc)
	 * @see \FrontPanels\Interfaces\InterfacePanelsProcessor::processPanel()
	 */
	public function processPanel(FrontPanelsPanelEntity $objPanel)
	{
		if (!is_object($objPanel->get("panel_read_data")) && !is_array($objPanel->get("panel_read_data")))
		{
			return $objPanel;
		}//end if
		
		$html_id = str_replace(".", "", microtime(TRUE));
		$html = "<script type=\"text/javascript\">";
		$html .= 	"jQuery(function () {
					    jQuery('#$html_id').highcharts({
					        data: {
					            table: 'references-vs-contacts-count'
					        },
					        chart: {
					            type: 'pie'
					        },
					        title: {
					            text: 'Contacts per Reference'
					        },
					        yAxis: {
					            allowDecimals: false,
					        },
					    });
					});";
		$html .= "</script>";
		$html .= "<table id=\"references-vs-contacts-count\" style=\"display: none;\">";
// 		$html .= 	"<thead>";
// 		$html .= 	"</thead>";
		$html .= 	"<tbody>";
		
		foreach ($objPanel->get("panel_read_data") as $k => $objData)
		{
			if ($objData->reference == "")
			{
				$objData->reference = "Not Specified";	
			}//end if
			
			$html .= "<tr>";
			$html .=	"<th>" . $objData->reference . "</th>";
			$html .= 	"<td>" . $objData->contact_count . "</td>";
			$html .= "</tr>";
		}//end foreach
		
		$html .=	"</tbody>";
		$html .= "</table>";
		$html .= "<div id=\"$html_id\"></div>";
		
		$objPanel->set("html", $html);
		return $objPanel;
	}//end function
}//end class