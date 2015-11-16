<?php
namespace FrontPanels\Panels;

use FrontPanels\Entities\FrontPanelsPanelEntity;
use FrontPanels\Interfaces\InterfacePanelsProcessor;

class PanelIconNavigateCommsTemplatesProcessorModel extends AbstractPanelProcessorModel implements InterfacePanelsProcessor
{
	/**
	 * (non-PHPdoc)
	 * @see \FrontPanels\Interfaces\InterfacePanelsProcessor::processPanel()
	 */
	public function processPanel(FrontPanelsPanelEntity $objPanel)
	{		
		$html = "<a href=\"" . $this->getViewUrlHelper()->url("front-comms-templates") . "\" title=\"Communications Templates\">" . ICON_XLARGE_HTML_TEMPLATES_COMMS_HTML . "</a>";
		
		$objPanel->set("html", $html);
		return $objPanel;
	}//end function
}//end class