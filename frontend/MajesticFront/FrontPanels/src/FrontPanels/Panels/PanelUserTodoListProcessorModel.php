<?php
namespace FrontPanels\Panels;

use FrontPanels\Entities\FrontPanelsPanelEntity;
use FrontPanels\Interfaces\InterfacePanelsProcessor;

class PanelUserTodoListProcessorModel extends AbstractPanelProcessorModel implements InterfacePanelsProcessor
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

		$arr = array();
		foreach ($objPanel->get("panel_read_data") as $objTask)
		{
			if ($objTask->reg_id == "")
			{
				continue;
			}//end if
			
			//do not display tasks already completed
			if ($objTask->complete == 1)
			{
				continue;
			}//end if

			$arr_task["contact"] = "<a href=\"" . $this->getViewUrlHelper()->url("front-contacts", array("action" => "view-contact", "id" => $objTask->reg_id)) . "\" title=\"View Contact Information\" data-toggle=\"tooltip\">" . ICON_SMALL_PROFILE_HTML . " " . $objTask->registrations_fname . " " . $objTask->registrations_sname . "</a>";
			$arr_task["task"] = $objTask->content;
			$arr_task["due"] = $objTask->datetime_reminder;
			$arr_task["status"] = "Pending";
			$complete_url = "<a href=\"" . $this->getViewUrlHelper()->url("front-users-tasks", array("action" => "complete-task", "user_id" => $objTask->user_id, "id" => $objTask->id)) . "\" title=\"Mark task as complete\" data-toggle=\"tooltip\">" . ICON_SMALL_ACTIVE_HTML . "</a>";

			$edit_url = "<a href=\"" . $this->getViewUrlHelper()->url("front-users-tasks", array("action" => "edit", "user_id" => $objTask->user_id, "id" => $objTask->id)) . "\" title=\"Edit Task Details\" data-toggle=\"tooltip\">" . ICON_SMALL_MODIFY_HTML . "</a>";
			$delete_url = "<a href=\"" . $this->getViewUrlHelper()->url("front-users-tasks", array("action" => "delete", "user_id" => $objTask->user_id, "id" => $objTask->id)) . "\" title=\"Delete Task\" data-toggle=\"tooltip\">" . ICON_SMALL_DELETE_HTML . "</a>";
			$arr_task["urls"] = $edit_url . "&nbsp;" . $complete_url . "&nbsp;" . $delete_url;
			$arr[] = $arr_task;
		}//end foreach

		//load table helper
		$objSimpleHTMLTable = new \FrontCore\ViewHelpers\FrontRenderSimpleHtmlTable();
		$html = $objSimpleHTMLTable->generate("", array("Contact", "Task", "Due", "Status", "&nbsp;"), $arr);

		$objPanel->set("html", $html);
		return $objPanel;
	}//end function
}//end class
