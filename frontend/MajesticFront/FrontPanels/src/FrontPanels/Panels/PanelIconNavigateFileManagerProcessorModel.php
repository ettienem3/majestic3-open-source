<?php
namespace FrontPanels\Panels;

use FrontPanels\Entities\FrontPanelsPanelEntity;
use FrontPanels\Interfaces\InterfacePanelsProcessor;

class PanelIconNavigateFileManagerProcessorModel extends AbstractPanelProcessorModel implements InterfacePanelsProcessor
{
	/**
	 * (non-PHPdoc)
	 * @see \FrontPanels\Interfaces\InterfacePanelsProcessor::processPanel()
	 */
	public function processPanel(FrontPanelsPanelEntity $objPanel)
	{		
		$html = "<a href=\"" . $this->getViewUrlHelper()->url("front-profile-file-manager") . "\" title=\"File Manager\">" . ICON_XLARGE_FILE_MANAGER_HTML . "</a>";
		
		$objPanel->set("html", $html);
		return $objPanel;
	}//end function
}//end class