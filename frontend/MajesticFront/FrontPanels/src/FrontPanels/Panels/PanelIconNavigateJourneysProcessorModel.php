<?php
namespace FrontPanels\Panels;

use FrontPanels\Entities\FrontPanelsPanelEntity;
use FrontPanels\Interfaces\InterfacePanelsProcessor;

class PanelIconNavigateJourneysProcessorModel extends AbstractPanelProcessorModel implements InterfacePanelsProcessor
{
	/**
	 * (non-PHPdoc)
	 * @see \FrontPanels\Interfaces\InterfacePanelsProcessor::processPanel()
	 */
	public function processPanel(FrontPanelsPanelEntity $objPanel)
	{		
		$html = "<a href=\"" . $this->getViewUrlHelper()->url("front-comms-admin/journeys") . "\" title=\"My Journeys\">" . ICON_XLARGE_COMMS_HTML . "</a>";
		
		$objPanel->set("html", $html);
		return $objPanel;
	}//end function
}//end class