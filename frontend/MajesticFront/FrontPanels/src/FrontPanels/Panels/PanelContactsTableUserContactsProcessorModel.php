<?php
namespace FrontPanels\Panels;

use FrontPanels\Entities\FrontPanelsPanelEntity;
use FrontPanels\Interfaces\InterfacePanelsProcessor;

class PanelContactsTableUserContactsProcessorModel extends AbstractPanelProcessorModel implements InterfacePanelsProcessor
{
	/**
	 * (non-PHPdoc)
	 * @see \FrontPanels\Interfaces\InterfacePanelsProcessor::processPanel()
	 */
	public function processPanel(FrontPanelsPanelEntity $objPanel)
	{
		//load user
		$objUser = \FrontUserLogin\Models\FrontUserSession::isLoggedIn();

		//load user contacts
		$objContacts = $this->getContactsModel()->fetchContacts(array(
 				"regtbl_user" => $objUser->id,
		));

		$arr = array();
		foreach ($objContacts as $objContact)
		{
			if (!is_numeric($objContact->id) || $objContact->id == "")
			{
				continue;
			}//end if

			$arr_contact["name"] = $objContact->fname . " " . $objContact->sname;
			$arr_contact["reference"] = $objContact->reference;
			$arr_contact["source"] = $objContact->source;

			$view_url = "<a href=\"" . $this->getViewUrlHelper()->url("front-contacts", array("action" => "view-contact", "id" => $objContact->id)) . "\" title=\"View Contact Information\" data-toggle=\"tooltip\">" . ICON_SMALL_PROFILE_HTML . "</a>";
			$edit_url = "<a href=\"" . $this->getViewUrlHelper()->url("front-contacts", array("action" => "edit-contact", "id" => $objContact->id)) . "\" title=\"Edit Contact Information\" data-toggle=\"tooltip\">" . ICON_SMALL_MODIFY_HTML . "</a>";
			$comms_url = "<a href=\"" . $this->getViewUrlHelper()->url("front-contacts", array("action" => "view-contact", "id" => $objContact->id)) . "\" class=\"contact_comms\" data-contact-id=\"" .$objContact->id . "\" title=\"Contact Communications\" data-toggle=\"tooltip\">" . ICON_SMALL_COMMS_HTML . "</a>";
			$arr_contact["Links"] = $view_url . "&nbsp;" . $edit_url . "&nbsp" . $comms_url;

			$arr[] = $arr_contact;
		}//end foreach

		$add_url = "<a href=\"" . $this->getViewUrlHelper()->url("front-contacts", array("action" => "create-contact")) . "\" title=\"Create a new Contact\" data-toggle=\"tooltip\"><span class=\"icon-button-very-large\">" . ICON_MEDIUM_ADD_HTML . "</a>";

		//load table helper
		$objSimpleHTMLTable = new \FrontCore\ViewHelpers\FrontRenderSimpleHtmlTable();
		$html = $objSimpleHTMLTable->generate("", array("Name", "Reference", "Source", $add_url), $arr);

		$objPanel->set("html", $html);
		return $objPanel;
	}//end function
}//end class