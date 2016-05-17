<?php
namespace FrontPanels\Controller;

use Zend\View\Model\ViewModel;

use FrontContacts\Controller\IndexController;
use FrontPanels\Controller\PanelsController;
use FrontPanels\Models\FrontPanelsModel;
use FrontCore\Adapters\AbstractCoreActionController;

class PanelsController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Panels Model
	 * @var \FrontPanels\Models\FrontPanelsModel
	 */
	private $model_panels;

	public function displayPanelsAction()
	{
		$this->layout("layout/dashboard");

		//check if panels are enabled
		$objUser = \FrontUserLogin\Models\FrontUserSession::isLoggedIn();
		if (!in_array("panels", $objUser->profile->plugins_enabled))
		{
			return $this->redirect()->toRoute("front-contacts");
		}//end if

		try {
			//load user session
			$objUserSession = new \Zend\Session\Container("user");

			//load user panels
			$objUserPanels = $this->getFrontPanelsModel()->fetchUserPanels();
			$arr_panels = array();

			//preprocess some panels
			foreach ($objUserPanels as $objPanel)
			{
				//check if panel has been cached
				if (isset($objUserSession->arr_cached_processed_panels[$objPanel->get("fk_id_panels")]))
				{
					$obj = $this->getServiceLocator()->get("FrontPanels\Entities\FrontPanelsPanelEntity");
					$obj->set($objUserSession->arr_cached_processed_panels[$objPanel->get("fk_id_panels")]);
					$arr_panels[] = $obj;
					continue;
				}//end if

				switch ($objPanel->get("panels_panel_type"))
				{
					case "icon":
						$objPanelOutput = $this->getFrontPanelsModel()->processUserPanel($objPanel->get("fk_id_panels"), array(
							"panel_id" 						=> $objPanel->get("fk_id_panels"),
							"panels_name" 					=> $objPanel->get("panels_name"),
							"panels_categories_category" 	=> $objPanel->get("panels_categories_category"),
							"panels_unique_identifier" 		=> $objPanel->get("panels_unique_identifier"),
						));

						$objPanel->set("html", $objPanelOutput->get("html"));

						//cache user icon
						if (!isset($objUserSession->arr_cached_processed_panels))
						{
							$objUserSession->arr_cached_processed_panels = array();
						}//end if
						$objUserSession->arr_cached_processed_panels[$objPanel->get("fk_id_panels")] = $objPanel->getArrayCopy();
						break;
				}//end switch

				$arr_panels[] = $objPanel;
			}//end foreach

		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage("Panels could not be loaded");
			return $this->redirect()->toRoute("front-contacts");
		}//end catch

		return array(
				"objUserPanels" => (object) $arr_panels,
		);
	}//end function

	public function ajaxLoadPanelAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			echo json_encode(array(
				"error" => 1,
				"response" => "Panel id is not set",
			), JSON_FORCE_OBJECT);
			exit;
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			try {
				$objPanel = $this->getFrontPanelsModel()->processUserPanel($id, (array) $request->getPost());
//try to prevent session file locks
session_write_close();
				echo json_encode(array(
					"error" => 0,
					"response" => $objPanel->getArrayCopy(),
				), JSON_FORCE_OBJECT);
				exit;
			} catch (\Exception $e) {
				echo json_encode(array(
						"error" => 1,
						"response" => $e->getMessage(),
				), JSON_FORCE_OBJECT);
				exit;
			}//end catch
		}//end if

		echo json_encode(array(
				"error" => 1,
				"response" => "Panel request is invalid",
		), JSON_FORCE_OBJECT);
		exit;
	}//end function

	/**
	 * Create an instance of the Front Panels Model using the Service Manager
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