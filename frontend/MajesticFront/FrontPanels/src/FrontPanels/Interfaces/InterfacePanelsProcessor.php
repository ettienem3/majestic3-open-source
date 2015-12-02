<?php
namespace FrontPanels\Interfaces;

use FrontPanels\Entities\FrontPanelsPanelEntity;

interface InterfacePanelsProcessor
{
	/**
	 * Process a panel locally
	 * @param FrontPanelsPanelEntity $objPanel
	 */
	public function processPanel(FrontPanelsPanelEntity $objPanel);
}//end interface