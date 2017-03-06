<?php
namespace FrontFormAdmin\Controller;

use Zend\View\Model\JsonModel;
use FrontCore\Adapters\AbstractCoreActionController;

class ReplaceFieldsController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Replace Fields Model
	 * @var \FrontFormAdmin\Models\FrontReplaceFieldsAdminModel
	 */
	protected $model_replace_fields;

	public function indexAction()
	{
		//load the fields
		try {
			$objFields = $this->getReplaceFieldsModel()->fetchReplaceFields($this->params()->fromQuery());
		} catch (\Exception $e) {
			$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
			return $this->redirect()->toRoute('home');
		}//end catch

		return array(
					"objFields" => $objFields,
				);
	}//end function

	public function ajaxIndexAction()
	{
		//render layout
		$this->layout("layout/layout-body");
		//load the fields
		$objFields = $this->getReplaceFieldsModel()->fetchReplaceFields(array(), TRUE);

		if ($this->params()->fromQuery("json", 0) == 1)
		{
			echo json_encode(array("fields" => $objFields), JSON_FORCE_OBJECT); exit;
			return new JsonModel(array("fields" => $objFields));
		}//end if

		return array(
				"objFields" => $objFields,
		);
	}//end function

	public function createReplaceFieldAction()
	{
		//load the form
		$form = $this->getReplaceFieldsModel()->getReplaceFieldSystemForm();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			try {
				$form->setData($request->getPost());
				if ($form->isValid())
				{
					//create the field
					$objField = $this->getReplaceFieldsModel()->createReplaceField($form->getData());

					//set success message
					$this->flashMessenger()->addSuccessMessage("Replace field has been created");

					//redirect back to the replace fields index page
					return $this->redirect()->toRoute("front-form-admin/replace-fields");
				}//end if
			} catch (\Exception $e) {
				//set error message
				$this->flashMessenger()->addErrorMessage($e->getMessage());

				//redirect back to the replace fields index page
				return $this->redirect()->toRoute("front-form-admin/replace-fields");
			}//end catch
		}//end if

		return array(
					"form" => $form,
				);
	}//end function

	public function editReplaceFieldAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Replace field could not be loaded. Id is not set");

			//redirect back to the replace fields index page
			return $this->redirect()->toRoute("front-form-admin/replace-fields");
		}//end if

		//load the field data
		$objField = $this->getReplaceFieldsModel()->fetchReplaceField($id);

		//load the form
		$form = $this->getReplaceFieldsModel()->getReplaceFieldSystemForm();

		//bind data to form
		$form->bind($objField);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			try {
				$form->setData($request->getPost());
				if ($form->isValid())
				{
					//extract the data
					$objField = $form->getData();

					//set id from route
					$objField->set("id", $id);

					//update the field
					$objField = $this->getReplaceFieldsModel()->updateReplaceField($objField);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Replace field has been updated");

					//redirect back to the replace fields index page
					return $this->redirect()->toRoute("front-form-admin/replace-fields");
				}//end if
			} catch (\Exception $e) {
				//set error message
				$this->flashMessenger()->addErrorMessage($e->getMessage());

				//redirect back to the replace fields index page
				return $this->redirect()->toRoute("front-form-admin/replace-fields");
			}//end catch
		}//end if

		return array(
				"form" => $form,
		);
	}//end function

	public function deleteReplaceFieldAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Replace field could not be loaded. Id is not set");

			//redirect back to the replace fields index page
			return $this->redirect()->toRoute("front-form-admin/replace-fields");
		}//end if

 		try {
			//load the field data
			$objField = $this->getReplaceFieldsModel()->fetchReplaceField($id);

			$request = $this->getRequest();
			if ($request->isPost())
			{
				if ($this->params()->fromPost("delete") == "Yes")
				{
					//delete the field
					$this->getReplaceFieldsModel()->deleteReplaceField($objField);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Replace Field has been deleted");

					//redirect back to the replace fields index page
					return $this->redirect()->toRoute("front-form-admin/replace-fields");
				}//end if
			}//end if
		} catch (\Exception $e) {
			//set error message
			$this->flashMessenger()->addErrorMessage($e->getMessage());

			//redirect back to the replace fields index page
			return $this->redirect()->toRoute("front-form-admin/replace-fields");
		}//end catch

		return array(
					"objField" => $objField,
				);
	}//end function

	/**
	 * Create an instance of the Replace Fields Model using the Service Manager
	 * @return \FrontFormAdmin\Models\FrontReplaceFieldsAdminModel
	 */
	private function getReplaceFieldsModel()
	{
		if (!$this->model_replace_fields)
		{
			$this->model_replace_fields = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontReplaceFieldsAdminModel");
		}//end if

		return $this->model_replace_fields;
	}//end if
}//end class