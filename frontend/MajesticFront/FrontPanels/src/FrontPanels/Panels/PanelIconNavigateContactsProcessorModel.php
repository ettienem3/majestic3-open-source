<?php
namespace FrontPanels\Panels;

use FrontPanels\Entities\FrontPanelsPanelEntity;
use FrontPanels\Interfaces\InterfacePanelsProcessor;

class PanelIconNavigateContactsProcessorModel extends AbstractPanelProcessorModel implements InterfacePanelsProcessor
{
	/**
	 * (non-PHPdoc)
	 * @see \FrontPanels\Interfaces\InterfacePanelsProcessor::processPanel()
	 */
	public function processPanel(FrontPanelsPanelEntity $objPanel)
	{
		//load user
		$objUser = \FrontUserLogin\Models\FrontUserSession::isLoggedIn();
		
		$html = "<a href=\"" . $this->getViewUrlHelper()->url("front-contacts") . "\" title=\"Contacts\">" . ICON_XLARGE_CONTACTS_HTML . "</a>";
		
		$objPanel->set("html", $html);
		return $objPanel;
	}//end function
}//end class