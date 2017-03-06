<?php
namespace FrontInboxManager\Controller;

use FrontCore\Adapters\AbstractCoreActionController;
use Zend\View\Model\JsonModel;

class InboxManagerController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Inbox Manager Model
	 * @var \FrontInboxManager\Models\FrontInboxManagerModel
	 */
	private $model_inbox_manager;

	public function indexAction()
	{
		try {
			$objMessages = $this->getInboxManagerModel()->fetchInboxMessages($this->params()->fromQuery());

			return array(
				"objMessages" => $objMessages,
			);
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
		}//end catch
	}//end function

	public function appAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['inbox'] != true)
		{
			$this->flashMessenger()->addInfoMessage('The requested view is not available');
			return $this->redirect()->toRoute('front-inbox-manager');
		}//end if

		$this->layout('layout/angular/app');
		$arr_params = $this->params()->fromQuery();
		$arr_params['qp_limit'] = 9;
		$objMessages = $this->getInboxManagerModel()->fetchInboxMessages($arr_params);

		return array(
				"objMessages" => $objMessages,
		);
	}//end function

	public function ajaxRequestAction()
	{
		$arr_config = $this->getServiceLocator()->get('config')['frontend_views_config'];
		if ($arr_config['enabled'] != true || $arr_config['angular-views-enabled']['inbox'] != true)
		{
			return new JsonModel(array(
					'error' => 1,
					'response' => 'Requested functionality is not available',
			));
		}//end if

		$arr_params = $this->params()->fromQuery();
		if (isset($arr_params['acrq']))
		{
			$acrq = $arr_params['acrq'];
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_post_data = json_decode(file_get_contents('php://input'), true);
			if (isset($arr_post_data['acrq']))
			{
				$acrq = $arr_post_data['acrq'];
				unset($arr_post_data['acrq']);
			}//end if
		}//end if

		try {
			switch ($acrq)
			{
				case 'index':
					//load messages
					$objMessages = $this->getInboxManagerModel()->fetchInboxMessages($arr_params);
					$objHyperMedia = $objMessages->hypermedia;
					unset($objMessages->hypermedia);
					$arr_messages = array();
					foreach ($objMessages as $objMessage)
					{
						//format datetime received
						$date = $this->formatUserDate(array("date" =>  $objMessage->tstamp, "options" => array(
								"output_format" => "d M Y H:i",
						)));
						$objMessage->set('datetime_received', $date);

						$arr_messages[] = $objMessage->getArrayCopy();
					}//end foreach

					$objMessages = (object) $arr_messages;
					$objMessages->hypermedia = $objHyperMedia;
					$objResult = new JsonModel(array(
							'objData' => $objMessages,
					));
					return $objResult;
					break;

				case 'load-comm-content':
					//load the message
					$objMessage = $this->getInboxManagerModel()->fetchInboxMessage($arr_params['id']);
					if (!$objMessage)
					{
						$objResult = new JsonModel(array(
								'error' => 1,
								'response' => 'Requested data could not be located',
						));
						return $objResult;
					}//end if

					$objResult = new JsonModel(array(
							'objData' => (object) $objMessage->getArrayCopy(),
					));
					return $objResult;
					break;

				case 'delete-inbox-item':
					$this->getInboxManagerModel()->deleteInboxMessage($arr_params['id']);
					$objData = (object) array('message' => 'Inbox item has been removed');

					$objResult = new JsonModel(array(
							'objData' => $objData,
					));
					return $objResult;
					break;

				case 'forward-to-user':

					$objResult = new JsonModel(array(
							'objData' => $objData,
					));
					return $objResult;
					break;
			}//end switch
		} catch (\Exceptoin $e) {
			$objResult = new JsonModel(array(
					'error' => 1,
					'response' => $e->getMessage(),
			));
			return $objResult;
		}//end catch

		$objResult = new JsonModel(array(
				'error' => 1,
				'response' => 'Request type is not specified',
		));
		return $objResult;
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

			//format datetime received
			$date = $this->formatUserDate(array("date" =>  $objMessage->tstamp, "options" => array(
					"output_format" => "d M Y H:i",
			)));
			$objMessage->set('datetime_received', $date);

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
