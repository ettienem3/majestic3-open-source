<?php
namespace FrontInboxManager\Controller;

use FrontCore\Adapters\AbstractCoreActionController;

class InboxManagerController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Inbox Manager Model
	 * @var \FrontInboxManager\Models\FrontInboxManagerModel
	 */
	private $model_inbox_manager;

	public function indexAction()
	{
		$objMessages = $this->getInboxManagerModel()->fetchInboxMessages($this->params()->fromQuery());

		return array(
			"objMessages" => $objMessages,
		);
	}//end function

	public function readMessageAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Message could not be archived. Id is not set");

			//redirect back to the index page
			return $this->redirect()->toRoute("front-inbox-manager");
		}//end if

		//load the message
		$objMessage = $this->getInboxManagerModel()->fetchInboxMessage($id);

		return array(
			"objMessage" => $objMessage,
		);
	}//end function

	public function ajaxReadMessageAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			echo json_encode(array("error" => 1, "response" => "Message could not be archived. Id is not set"), JSON_FORCE_OBJECT); exit;
		}//end if

		try {
			//load the message
			$objMessage = $this->getInboxManagerModel()->fetchInboxMessage($id);

			//format html
			$html .= "<table class=\"table-simple-style data-table mj3-table table table-striped dataTable\" width=\"100%\">";
			$html .=	"<tr>";
			$html .=		"<td>";
			$html .=			"From";
			$html .=		"</td>";
			$html .=		"<td>";
			$html .=			$objMessage->get("from_name") . " (" . $objMessage->get("from_email") . ")";
			$html .=		"</td>";
			$html .=	"</tr>";
			$html .=	"<tr>";
			$html .=		"<td>";
			$html .=			"Date";
			$html .=		"</td>";
			$html .=		"<td>";
			$html .=			$objMessage->get("datetime_received");
			$html .=		"</td>";
			$html .=	"</tr>";
			$html .=	"<tr>";
			$html .=		"<td>";
			$html .=			"Subject";
			$html .=		"</td>";
			$html .=		"<td>";
			$html .=			$objMessage->get("email_subject");
			$html .=		"</td>";
			$html .=	"</tr>";
			$html .=	"<tr>";
			$html .=		"<td>";
			$html .=			"In reply to";
			$html .=		"</td>";
			$html .=		"<td>";
			$html .=			$objMessage->get("comm_history_subject");
			$html .=		"</td>";
			$html .=	"</tr>";

			$html .= "</table>";
			$html .= "<br/>";
			$html .= "<fieldset><legend>Content</legend>";
			$html .=	$objMessage->get("inbox_content");
			$html .= "</fieldset>";

			echo json_encode(array("error" => 0, "response" => $html), JSON_FORCE_OBJECT);
		} catch (\Exception $e) {
			echo json_encode(array("error" => 1, "response" => $e->getMessage()), JSON_FORCE_OBJECT);
		}//end catch

		exit;
	}//end function

	public function archiveMessageAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Message could not be archived. Id is not set");

			//redirect back to the index page
			return $this->redirect()->toRoute("front-inbox-manager");
		}//end if

		try {
			$this->getInboxManagerModel()->updateInboxMessage($id, array("archived" => 1));

			//set success message
			$this->flashMessenger()->addSuccessMessage("Message has been archived");
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage("An error occured trying to delete the Inbox Message");
			$this->flashMessenger()->addErrorMessage("Error: " . $e->getMessage());
		}//end catch

		//redirect back to the index page
		return $this->redirect()->toRoute("front-inbox-manager");
	}//end function

	public function deleteMessageAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Message could not be deleted. Id is not set");

			//redirect back to the index page
			return $this->redirect()->toRoute("front-inbox-manager");
		}//end if

		try {
			//delete the message
			$this->getInboxManagerModel()->deleteInboxMessage($id);

			//set success message
			$this->flashMessenger()->addSuccessMessage("Message has been deleted");
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage("An error occured trying to delete the Inbox Message");
			$this->flashMessenger()->addErrorMessage("Error: " . $e->getMessage());
		}//end catch

		//return to the index page
		return $this->redirect()->toRoute("front-inbox-manager");
	}//end function

	/**
	 * Create an instance of the Inbox Manager Model using the Service Manager
	 * @return \FrontInboxManager\Models\FrontInboxManagerModel
	 */
	private function getInboxManagerModel()
	{
		if (!$this->model_inbox_manager)
		{
			$this->model_inbox_manager = $this->getServiceLocator()->get("FrontInboxManager\Models\FrontInboxManagerModel");
		}//end if

		return $this->model_inbox_manager;
	}//end function
}//end class
