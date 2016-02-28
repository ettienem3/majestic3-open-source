<?php
namespace FrontContacts\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class ContactCommsController extends AbstractActionController
{
	/**
	 * Container for the Front Contacts Model
	 * @var \FrontContacts\Models\FrontContactsModel
	 */
	private $model_contacts;

	/**
	 * Container for the Comms Journeys Admin Model
	 * @var \FrontCommsAdmin\Models\FrontJourneysModel
	 */
	private $model_comms_journeys_admin;

	/**
	 * Container for the Comms Templates Admin Model
	 * @var \FrontContacts\Models\FrontContactCommsTemplatesModel
	 */
	private $model_contact_comms_templates;

	/**
	 * Container for the Contact's Journeys Model
	 * @var \FrontContacts\Models\FrontContactsJourneysModel
	 */
	private $model_contact_comms_journeys;

	/**
	 * Set the display format to ajax format and return the contact's id from the route
	 * @param string $layout
	 * @param string $arr_view_data
	 * @return mixed contact id
	 */
	private function renderOutputFormat($layout = "layout/layout-body", $arr_view_data = NULL)
	{
		$this->layout($layout);

		$contact_id = $this->params()->fromRoute("id", "");
		return $contact_id;
	}//end function

	public function indexAction()
	{
		$id = $this->renderOutputFormat();

		//load contact
		$objContact = $this->getServiceLocator()->get('FrontContacts\Entities\FrontContactsContactEntity');
		$objContact->set("id", $id);

		//removed for speed
// 		$objContact = $this->getContactsModel()->fetchContact($id);

		return array(
			"objContact" => $objContact,
		);
	}//end function

	public function listTemplatesAction()
	{
		$id = $this->renderOutputFormat();

		//load available templates
		$objTemplates = $this->getCommsJourneysAdminModel()->fetchTemplates(array("dbc-comms_active" => 1, "dbc-journeys_active" => 1));

		//load contact
		$objContact = $this->getContactsModel()->fetchContact($id);

		return array(
				"contact_id" 		=> $id,
				"objTemplates" 		=> $objTemplates,
				"objContact" 		=> $objContact,
		);
	}//end function

	public function previewTemplateAction()
	{
		$id = $this->renderOutputFormat();
		$comms_id = $this->params()->fromRoute("comms_id");

		//load the requested comm
 		$objComm = $this->getContactCommTemplatesModel()->loadCommTemplate($id, $comms_id);

 		//load contact
 		$objContact = $this->getContactsModel()->fetchContact($id);

		return array(
			"contact_id" => $id,
			"objContact" => $objContact,
			"objComm" => $objComm,
		);
	}//end function

	public function sendTemplateAction()
	{
		$id = $this->renderOutputFormat();
		$comms_id = $this->params()->fromRoute("comms_id", "");

		//load the requested comm
		$objComm = $this->getContactCommTemplatesModel()->loadCommTemplate($id, $comms_id);

		//load the form
		$form = $this->getContactCommTemplatesModel()->loadCommTemplateForm($objComm->get("comm_via_data_behaviour"));

		//bind comm data to form
		$form->bind($objComm);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			//populate the form with post data
			$form->setData($request->getPost());

			if ($form->isValid())
			{
				//check if this being previewed or being sent
				switch ($this->params()->fromQuery("comm_action"))
				{
					case "send":
						$objCommEntity = $form->getData();
						//allocate the comm id to the data
						$objCommEntity->set("id",$comms_id);

						//submit the content
						$objResult = $this->getContactCommTemplatesModel()->sendCommTemplate($id, $objCommEntity, $this->params()->fromQuery("comm_action"));
						break;
				}//end switch
			}//end if
		}//end if



		//load the contact
		$objContact = $this->getContactsModel()->fetchContact($id);

		return array(
				"contact_id" 		=> $id,
				"comms_id" 			=> $comms_id,
				"objComm" 			=> $objComm,
				"objContact" 		=> $objContact,
				"form" 				=> $form,
				"comm_action"		=> $this->params()->fromQuery("comm_action", ""),
				"send_confirmation" => $objResult,
		);
	}//end function

	public function viewTemplateHistoryAction()
	{
		$id = $this->renderOutputFormat();
	}//end function

	public function journeysAction()
	{
		$id = $this->renderOutputFormat();

		//load available journeys
		$objJourneys = $this->getCommsJourneysAdminModel()->fetchJourneys(array("journeys_status" => 1), TRUE);

		//load contacts comm history
		$objContactJourneys = $this->getContactJourneysModel()->fetchContactJourneysStarted($id);

		return array(
				"contact_id" => $id,
				"objJourneys" => $objJourneys,
				"objContactJourneys" => $objContactJourneys,

				//set array of journey behaviours that cannot be started manually
				"arr_behaviours_no_start" => array(
												"__birthday",
												"__register",
												"__register_merge",
												"__status",
												"__status_merge",
												"__sf_status",
												"__viral",
												"__viral_register",
											),
		);
	}//end function

	public function historyAction()
	{
		$id = $this->renderOutputFormat();

		try {
			$objCommsData = $this->getContactsModel()->fetchContactCommHistory($id);
		} catch (\Exception $e) {
			echo $e->getMessage();
			exit;
		}//end catch

		return array(
				"contact_id" => $id,
				"objCommsData" => $objCommsData,
		);
	}//end function


	public function ajaxStartJourneyAction()
	{
		$contact_id = $this->params()->fromRoute("id");
		$journey_id = $this->params()->fromRoute("comms_id");

		try {
			$objResult = $this->getContactJourneysModel()->startContactJourney($contact_id, $journey_id);

			//format data for return
			$arr_return = array(
					"result" => TRUE,
					"error" => '',
					//set element replace html
					"html" => "<span>Started</span><br>
										<a href=\"" . $this->url()->fromRoute("front-contact-comms", array("id" => $this->params()->fromRoute("id"), "action" => "ajax-stop-journey", "comms_id" => $this->params()->fromRoute("comms_id"))) . "\" class=\"span-journey-stop\" alt=\"Stop Journey\" onclick=\"return executeJourneyOperations(jQuery(this));\">Stop</a>&nbsp;
										<a href=\"" . $this->url()->fromRoute("front-contact-comms", array("id" => $this->params()->fromRoute("id"), "action" => "ajax-restart-journey", "comms_id" => $this->params()->fromRoute("comms_id"))) . "\" class=\"span-journey-restart\" alt=\"Restart Journey\" onclick=\"return executeJourneyOperations(jQuery(this));\">Restart</a>",
			);
		} catch (\Exception $e) {
			//extract error
			$arr_t = explode("||", $e->getMessage());
			$json = array_pop($arr_t);
			$objResult = json_decode($json);
			if (is_object($objResult))
			{
				switch($objResult->HTTP_RESPONSE_CODE)
				{
					case 999:
					default:
						$arr_tt = explode(":", $objResult->HTTP_RESPONSE_MESSAGE);
						$message = array_pop($arr_tt);

						//format data for return
						$arr_return = array(
								"result" => FALSE,
								"error" => $message,
								//set element replace html
								"html" => "",
						);
						break;
				}//end switch
			} else {
				//format data for return
				$arr_return = array(
						"result" => FALSE,
						"error" => "An unknown error has occured (" . $e->getMessage() . ")",
						//set element replace html
						"html" => "",
				);
			}//end if
		}//end catch

		return new JsonModel($arr_return);
	}//end function

	public function ajaxStopJourneyAction()
	{
		$contact_id = $this->params()->fromRoute("id");
		//we switch over to the registration comms id for this action
		$reg_comm_id = $this->params()->fromRoute("comms_id");

		try {
			$objResult = $this->getContactJourneysModel()->stopContactJourney($contact_id, $reg_comm_id);

			if ($objResult->HTTP_RESPONSE_CODE != 200)
			{
				$result = FALSE;
				$error = $objResult->HTTP_RESPONSE_MESSAGE;
			} else {
				$result = TRUE;
				$error = "";
			}//end if
	
			$arr_return = array(
								"result" => $result,
								"error" => $error,
								//set element replace html
								"html" => "<span>Stopped</span><br>
														<a href=\"" . $this->url()->fromRoute("front-contact-comms", array("id" => $this->params()->fromRoute("id"), "action" => "ajax-start-journey", "comms_id" => $objResult->data->journey_id)) . "\" class=\"span-journey-start\" alt=\"Start Journey\" onclick=\"return executeJourneyOperations(jQuery(this));\">Start</a>&nbsp;
														<a href=\"" . $this->url()->fromRoute("front-contact-comms", array("id" => $this->params()->fromRoute("id"), "action" => "ajax-restart-journey", "comms_id" => $this->params()->fromRoute("comms_id"))) . "\" class=\"span-journey-restart\" alt=\"Restart Journey\" onclick=\"return executeJourneyOperations(jQuery(this));\">Restart</a>",
								);
			return new JsonModel($arr_return);
		} catch (\Exception $e) {
			//extract error
			$arr_t = explode("||", $e->getMessage());
			$json = array_pop($arr_t);
			$objResult = json_decode($json);
			if (is_object($objResult))
			{
				switch($objResult->HTTP_RESPONSE_CODE)
				{
					case 999:
					default:
						$arr_tt = explode(":", $objResult->HTTP_RESPONSE_MESSAGE);
						$message = array_pop($arr_tt);
							
						//format data for return
						$arr_return = array(
								"result" => FALSE,
								"error" => $message,
								//set element replace html
								"html" => "",
						);
						break;
				}//end switch
			} else {
				//format data for return
				$arr_return = array(
						"result" => FALSE,
						"error" => "An unknown error has occured (" . $e->getMessage() . ")",
						//set element replace html
						"html" => "",
				);
			}//end if
				
			return new JsonModel($arr_return);			
		}//end catch
	}//end function

	public function ajaxRestartJourneyAction()
	{
		$contact_id = $this->params()->fromRoute("id");
		//we switch over to the registration comms id for this action
		$reg_comm_id = $this->params()->fromRoute("comms_id");

		try {
			$objResult = $this->getContactJourneysModel()->restartContactJourney($contact_id, $reg_comm_id);
	
			if ($objResult->HTTP_RESPONSE_CODE != 200)
			{
				$result = FALSE;
				$error = $objResult->HTTP_RESPONSE_MESSAGE;
			} else {
				$result = TRUE;
				$error = "";
			}//end if
	
			$arr_return = array(
								"result" => $result,
								"error" => $error,
								//set element replace html
								"html" => "<span>Restarted</span><br>
											<a href=\"" . $this->url()->fromRoute("front-contact-comms", array("id" => $this->params()->fromRoute("id"), "action" => "ajax-stop-journey", "comms_id" => $this->params()->fromRoute("comms_id"))) . "\" class=\"span-journey-stop\" alt=\"Stop Journey\" onclick=\"return executeJourneyOperations(jQuery(this));\">Stop</a>&nbsp;
											<a href=\"" . $this->url()->fromRoute("front-contact-comms", array("id" => $this->params()->fromRoute("id"), "action" => "ajax-restart-journey", "comms_id" => $this->params()->fromRoute("comms_id"))) . "\" class=\"span-journey-restart\" alt=\"Restart Journey\" onclick=\"return executeJourneyOperations(jQuery(this));\">Restart</a>",
							);
			return new JsonModel($arr_return);
		} catch (\Exception $e) {
			//extract error
			$arr_t = explode("||", $e->getMessage());
			$json = array_pop($arr_t);
			$objResult = json_decode($json);
			if (is_object($objResult))
			{
				switch($objResult->HTTP_RESPONSE_CODE)
				{
					case 999:
					default:
						$arr_tt = explode(":", $objResult->HTTP_RESPONSE_MESSAGE);
						$message = array_pop($arr_tt);
			
						//format data for return
						$arr_return = array(
								"result" => FALSE,
								"error" => $message,
								//set element replace html
								"html" => "",
						);
						break;
				}//end switch
			} else {
				//format data for return
				$arr_return = array(
						"result" => FALSE,
						"error" => "An unknown error has occured (" . $e->getMessage() . ")",
						//set element replace html
						"html" => "",
				);
			}//end if	
			
			return new JsonModel($arr_return);
		}//end catch
	}//end function

	/**
	 * Create an instance of the Contacts Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsModel
	 */
	private function getContactsModel()
	{
		if (!$this->model_contacts)
		{
			$this->model_contacts = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsModel");
		}//end if

		return $this->model_contacts;
	}//end function

	/**
	 * Create an instance of the Front Comms Journeys Admin Model using the Service Manager
	 * @return \FrontCommsAdmin\Models\FrontJourneysModel
	 */
	private function getCommsJourneysAdminModel()
	{
		if (!$this->model_comms_journeys_admin)
		{
			$this->model_comms_journeys_admin = $this->getServiceLocator()->get("FrontCommsAdmin\Models\FrontJourneysModel");
		}//end if

		return $this->model_comms_journeys_admin;
	}//end function

	/**
	 * Create an instance of the Contact Journeys Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsJourneysModel
	 */
	private function getContactJourneysModel()
	{
		if (!$this->model_contact_comms_journeys)
		{
			$this->model_contact_comms_journeys = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsJourneysModel");
		}//end if

		return $this->model_contact_comms_journeys;
	}//end function

	/**
	 * Create an instance of the Front Contact Comms Templates Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactCommsTemplatesModel
	 */
	private function getContactCommTemplatesModel()
	{
		if (!$this->model_contact_comms_templates)
		{
			$this->model_contact_comms_templates = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactCommsTemplatesModel");
		}//end if

		return $this->model_contact_comms_templates;
	}//end function
}//end class