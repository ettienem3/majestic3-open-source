<?php
namespace FrontContacts\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use FrontUserLogin\Models\FrontUserSession;
use FrontCore\Adapters\AbstractCoreActionController;

class ContactToolkitController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Contacts Model
	 * @var \FrontContacts\Models\FrontContactsModel
	 */
	private $model_contacts;

	/**
	 * Container for the Front Contacts Forms Model
	 * @var \FrontContacts\Models\FrontContactsFormsModel
	 */
	private $model_contact_forms;

	/**
	 * Container for the Front Contacts Statuses Model
	 * @var \FrontContacts\Models\FrontContactsStatusesModel
	 */
	private $model_contact_statuses;

	/**
	 * Container for the Front Statuses Model
	 * @var \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private $model_statuses;

	/**
	 * Container for the Front Contact Journeys Model
	 * @var \FrontContacts\Models\FrontContactsJourneysModel
	 */
	private $model_contact_journeys;

	/**
	 * Container for the Contact User Tasks Model
	 * @var \FrontUsers\Models\FrontUsersTasksModel
	 */
	private $model_user_tasks;

	/**
	 * Container for the Forms Model
	 * @var \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private $model_forms_admin;

	private function renderOutputFormat($layout = "layout/layout-toolkit-body", $arr_view_data = NULL)
	{
		$this->layout($layout);

		$contact_id = $this->params()->fromRoute("id", "");
		return $contact_id;
	}//end function

	private function loadContactData($contact_id)
	{
		return $this->getContactsModel()->fetchContact($contact_id);
	}//end function

	public function contactCommentsAction()
	{
		$contact_id = $this->renderOutputFormat();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			//load comment
			$arr_data = (array) $request->getPost();

			//create the comment
			try {
				$objResult = $this->getContactsModel()->createContactComment($contact_id, $arr_data);
				echo "true";
				exit;
			} catch (\Exception $e) {
				echo "Comment could not be created. " . $e->getMessage();
			}//end catch
		}//end if

		$objComments = $this->getContactsModel()->fetchContactComments($contact_id);

		return array(
			"objComments" => $objComments,
			"contact_id" => $contact_id,
		);
	}//end function

	public function contactFormsCompletedAction()
	{
		$contact_id = $this->renderOutputFormat();

		//load forms
 		$objContactForms = $this->getContactFormsModel()->fetchContactFormsCompleted($contact_id);

 		//load web forms
		$objWebForms = $this->getFrontFormAdminModel()->fetchForms(array(
				'forms_type_id' => 1,
				'forms_active' => 1,
		));

		return array(
			"contact_id" => $contact_id,
			"objContactForms" => $objContactForms,
			"objWebForms" => $objWebForms,
		);
	}//end function

	public function contactJourneysAction()
	{
		$contact_id = $this->renderOutputFormat();

		//load journeys
 		$objContactJourneys = $this->getFrontContactJourneysModel()->fetchContactJourneysStarted($contact_id);

		return array(
			"contact_id" 			=> $contact_id,
			"objContactJourneys" 	=> $objContactJourneys,
		);
	}//end function

	public function contactStatusHistoryAction()
	{
		$contact_id = $this->renderOutputFormat();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			//load data
			$arr_data = (array) $request->getPost();

			//update the status
			try {
				$objResult = $this->getContactStatusesModel()->updateContactStatus($contact_id, $arr_data);

				if ($objResult->HTTP_RESPONSE_CODE != 200)
				{
					echo "Status colud not be updated. " . $objResult->HTTP_RESPONSE_MESSAGE;
					exit;
				}//end if

				echo "true";
			} catch (\Exception $e) {
				echo "Status could not be updated. " . $e->getMessage();
			}//end catch

			exit;
		}//end if

		//load contact statuses to change current status for contact
		$objStatuses = $this->getFrontStatusesModel()->fetchContactStatuses();

		//load status history
		$objContactStatusData = $this->getContactStatusesModel()->fetchContactStatusHistory($contact_id);

		return array(
			"contact_id" 				=> $contact_id,
			"objContactStatusData" 		=> $objContactStatusData,
			"objStatuses" 				=> $objStatuses,
		);
	}//end function

	public function contactUserTasksAction()
	{
		$contact_id = $this->renderOutputFormat();

		//load tasks belonging to contact
		$objUserTasks = $this->getFrontUserTasksModel()->fetchUserTasks(array("user_tasks_reg_id" => $contact_id));

		$form = $this->getFrontUserTasksModel()->getUserTasksForm();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				$arr_data = $form->getData();
				$arr_data["reg_id"] = $contact_id;

				//create the user task
				$objUserTask = $this->getFrontUserTasksModel()->createUserTask($arr_data);
			} else {
var_dump($form->getMessages());
exit;
			}//end if
		}//end if

		return array(
			"objUserTasks" => $objUserTasks,
			"contact_id" => $contact_id,
			"form" => $form,
		);
	}//end function

	public function contactSalesFunnelsAction()
	{
		$contact_id = $this->renderOutputFormat();

		//load contact sales funnels
		try {
			$objSalesFunnels = $this->getContactFormsModel()->fetchContactSalesFunnelsCompleted($contact_id);
		} catch (\Exception $e) {
var_dump($e); exit;
		}//end catch

		return array(
			"objSalesFunnels" => $objSalesFunnels,
			"contact_id" => $contact_id,
		);
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
	 * Create an instance of the Front Contact Forms Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsFormsModel
	 */
	private function getContactFormsModel()
	{
		if (!$this->model_contact_forms)
		{
			$this->model_contact_forms = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsFormsModel");
		}//end if

		return $this->model_contact_forms;
	}//end function

	/**
	 * Create an instance of the Front Contact Statuses Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsStatusesModel
	 */
	private function getContactStatusesModel()
	{
		if (!$this->model_contact_statuses)
		{
			$this->model_contact_statuses = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsStatusesModel");
		}//end if

		return $this->model_contact_statuses;
	}//end function

	/**
	 * Create an instance of the Front Statuses Model using the Service Manager
	 * @return \FrontStatuses\Models\FrontContactStatusesModel
	 */
	private function getFrontStatusesModel()
	{
		if (!$this->model_statuses)
		{
			$this->model_statuses = $this->getServiceLocator()->get("FrontStatuses\Models\FrontContactStatusesModel");
		}//end if

		return $this->model_statuses;
	}//end function

	/**
	 * Create an instance of the Front Contact Journeys Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsJourneysModel
	 */
	private function getFrontContactJourneysModel()
	{
		if (!$this->model_contact_journeys)
		{
			$this->model_contact_journeys = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsJourneysModel");
		}//end if

		return $this->model_contact_journeys;
	}//end function

	/**
	 * Create an instance of the Front User Tasks Model using the Service Manager
	 * @return \FrontUsers\Models\FrontUsersTasksModel
	 */
	private function getFrontUserTasksModel()
	{
		if (!$this->model_user_tasks)
		{
			$this->model_user_tasks = $this->getServiceLocator()->get("FrontUsers\Models\FrontUsersTasksModel");
		}//end if

		return $this->model_user_tasks;
	}//end function

	/**
	 * Create an instance of the Front Form Admin Model using the Service Manager
	 * @return \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private function getFrontFormAdminModel()
	{
		if (!$this->model_forms_admin)
		{
			$this->model_forms_admin = $this->getServiceLocator()->get('FrontFormAdmin\Models\FrontFormAdminModel');
		}//end if

		return $this->model_forms_admin;
	}//end function
}//end class