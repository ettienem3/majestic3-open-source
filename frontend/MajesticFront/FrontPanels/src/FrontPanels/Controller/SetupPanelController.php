<?php
namespace FrontPanels\Controller;

use FrontCore\Adapters\AbstractCoreActionController;

class SetupPanelController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Panels Model
	 * @var \FrontPanels\Models\FrontPanelsModel
	 */
	private $model_panels;

	public function indexAction()
	{
		try {
			$objPanels = $this->getFrontPanelsModel()->fetchProfilePanels();
     	} catch (\Exception $e) {
     		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
     		return $this->redirect()->toRoute('home');
     	}//end catch

		return array(
			"objPanels" => $objPanels,
		);
	}//end fucntion

	public function createAction()
	{
		//load form
		$form = $this->getFrontPanelsModel()->getProfilePanelAdminForm();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				try {
					//create the panel
					$objProfilePanel = $this->getFrontPanelsModel()->createProfilePanel((array) $form->getData());

					//set success message
					$this->flashMessenger()->addSuccessMessage("Panel has been allocated");

					//return to the index page
					return $this->redirect()->toRoute("front-panels-setup");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if

		return array(
				"form" => $form,
		);
	}//end function

	public function editAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Panel could not loaded. ID is not set");
			//return to the index page
			return $this->redirect()->toRoute("front-panels-setup");
		}//end if

		//load the panel
		try {
			$objPanel = $this->getFrontPanelsModel()->fetchProfilePanel($id);
     	} catch (\Exception $e) {
     		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
     		return $this->redirect()->toRoute('home');
     	}//end catch

		//load form
		$form = $this->getFrontPanelsModel()->getProfilePanelAdminForm();
		$form->bind($objPanel);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());

			if ($form->isValid())
			{
				try {
					$objPanel = $form->getData();
					$objPanel->set("id", $id);

					//update
					$this->getFrontPanelsModel()->editProfilePanel($objPanel);

					//return to the index page
					return $this->redirect()->toRoute("front-panels-setup");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if

		return array(
				"form" => $form,
		);
	}//end function

	public function deleteAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Panel could not loaded. ID is not set");
			//return to the index page
			return $this->redirect()->toRoute("front-panels-setup");
		}//end if

		//load the panel
		try {
			$objPanel = $this->getFrontPanelsModel()->fetchProfilePanel($id);
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
			return $this->redirect()->toRoute('home');
		}//end catch

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{
				$this->getFrontPanelsModel()->deleteProfilePanel($objPanel);
			}//end if

			//return to the index page
			return $this->redirect()->toRoute("front-panels-setup");
		}//end if

		return array(
				"objPanel" => $objPanel,
		);
	}//end function

	public function statusAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Panel could not loaded. ID is not set");
			//return to the index page
			return $this->redirect()->toRoute("front-panels-setup");
		}//end if

		//load the panel
		try {
			$objPanel = $this->getFrontPanelsModel()->fetchProfilePanel($id);
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
			return $this->redirect()->toRoute('home');
		}//end catch			

		$objPanel->set("active", (1 - $objPanel->get("active")));
		$this->getFrontPanelsModel()->editProfilePanel($objPanel);

		//return to the index page
		return $this->redirect()->toRoute("front-panels-setup");
	}//end function

	public function userPanelsAction()
	{
		try {
			$objUserPanels = $this->getFrontPanelsModel()->fetchUserPanels();
		} catch (\Exception $e) {
     		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
     		$objUserPanels = array();
     	}//end catch

		return array(
			"objUserPanels" => $objUserPanels,
		);
	}//end function

	public function userSortPanelsAction()
	{
		try {
			$objUserPanels = $this->getFrontPanelsModel()->fetchUserPanels();
		} catch (\Exception $e) {
     		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
     		$objUserPanels = array();
     	}//end catch

		return array(
				"objUserPanels" => $objUserPanels,
		);
	}//end function

	public function ajaxSaveUserPanelOrderAction()
	{
		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_panels = (array) $request->getPost();
			try {
				foreach ($arr_panels as $id => $order)
				{
					$this->getFrontPanelsModel()->editUserPanelSettings($id, array("order" => $order));
				}//end foreach

				echo json_encode(array("error" => "0", "response" => "Panels updated", "redirect" => $this->url()->fromRoute("front-panels-setup", array("action" => "user-panels"))), JSON_FORCE_OBJECT);
				exit;
			} catch (\Exception $e) {
				echo json_encode(array("error" => 1, "response" => $e->getMessage()), JSON_FORCE_OBJECT);
				exit;
			}//end catch
		}//end if
	}//end function

	public function userAllocatePanelAction()
	{
		//load profile panels
		$objProfilePanels = $this->getFrontPanelsModel()->fetchProfilePanels();

		//load user panels
		$objUserPanels = $this->getFrontPanelsModel()->fetchUserPanels();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			foreach ($request->getPost("panels") as $k => $id)
			{
				try {
					$r = $this->getFrontPanelsModel()->allocatePanelToUser($id);
				} catch (\Exception $e) {
					$this->flashMessenger()->addErrorMessage($e->getMessage());
				}//end catch
			}//end foreach

			//return to user panels page
			return $this->redirect()->toRoute("front-panels-setup", array("action" => "user-panels"));
		}//end if

		return array(
				"objProfilePanels" => $objProfilePanels,
				"objUserPanels" => $objUserPanels,
		);
	}//end function

	public function userRemovePanelAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Panel could not be removed. ID is not set");

			//return to user panels page
			return $this->redirect()->toRoute("front-panels-setup", array("action" => "user-panels"));
		}//end if

		try {
			$this->getFrontPanelsModel()->removePanelFromUser($id);
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($e->getMessage());
		}//end catch

		//return to user panels page
		return $this->redirect()->toRoute("front-panels-setup", array("action" => "user-panels"));
	}//end function

    /**
     * Create an instance for the Front Panels Model using the Service Manager
     * @return \FrontPanels\Models\FrontPanelsModel
     */
    private function getFrontPanelsModel()
    {
    	if (!$this->model_panels)
    	{
    		$this->model_panels = $this->getServiceLocator()->get("FrontPanels\Models\FrontPanelsModel");
    	}//end if

    	return $this->model_panels;
    }//end function
}//end class
