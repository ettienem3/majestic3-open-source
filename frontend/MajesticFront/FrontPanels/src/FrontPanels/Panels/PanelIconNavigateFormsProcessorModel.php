<?php
namespace FrontPanels\Panels;

use FrontPanels\Entities\FrontPanelsPanelEntity;
use FrontPanels\Interfaces\InterfacePanelsProcessor;

class PanelIconNavigateFormsProcessorModel extends AbstractPanelProcessorModel implements InterfacePanelsProcessor
{
	/**
	 * (non-PHPdoc)
	 * @see \FrontPanels\Interfaces\InterfacePanelsProcessor::processPanel()
	 */
	public function processPanel(FrontPanelsPanelEntity $objPanel)
	{		
		$html = "<a href=\"" . $this->getViewUrlHelper()->url("front-form-admin/form") . "\" title=\"Manage Forms\">" . ICON_XLARGE_FORMS_HTML . "</a>";
		
		$objPanel->set("html", $html);
		return $objPanel;
	}//end function
}//end class