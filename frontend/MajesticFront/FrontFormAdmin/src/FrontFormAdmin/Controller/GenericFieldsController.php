<?php
namespace FrontFormAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class GenericFieldsController extends AbstractActionController
{
	/**
	 * Container for the Front Generic Fields Model
	 * @var \FrontFormAdmin\Models\FrontGenericFieldsAdminModel
	 */
	protected $model_generic_fields;

	public function indexAction()
	{
		//load the fields
		$objFields = $this->getGenericFieldsModel()->fetchGenericFields($this->params()->fromQuery());

		return array(
					"objFields" => $objFields,
				);
	}//end function

	public function ajaxIndexAction()
	{
		//render layout
		$this->layout("layout/layout-body");
		//load the fields
		$objFields = $this->getGenericFieldsModel()->fetchGenericFields();
		
		return array(
				"objFields" => $objFields,
		);
	}//end function
	
	public function createGenericFieldAction()
	{
		//load the form
		$form = $this->getGenericFieldsModel()->getGenericFieldSystemForm();

		$request = $this->getRequest();
		if ($request->isPost())
		{
			try {
				$form->setData($request->getPost());
				if ($form->isValid())
				{
					//create the field
					$objField = $this->getGenericFieldsModel()->createGenericField($form->getData());

					//set success message
					$this->flashMessenger()->addSuccessMessage("Generic field has been created");

					//redirect back to the generic fields index page
					return $this->redirect()->toRoute("front-form-admin/generic-fields");
				}//end if
			} catch (\Exception $e) {
				//set error message
				$this->flashMessenger()->addErrorMessage($e->getMessage());

				//redirect back to the generic fields index page
				return $this->redirect()->toRoute("front-form-admin/generic-fields");
			}//end catch
		}//end if

		return array(
					"form" => $form,
				);
	}//end function

	public function editGenericFieldAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Generic field could not be loaded. Id is not set");

			//redirect back to the generic fields index page
			return $this->redirect()->toRoute("front-form-admin/generic-fields");
		}//end if

		//load the field data
		$objField = $this->getGenericFieldsModel()->fetchGenericField($id);

		//load the form
		$form = $this->getGenericFieldsModel()->getGenericFieldSystemForm();

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
					$objField = $this->getGenericFieldsModel()->updateGenericField($objField);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Generic field has been updated");

					//redirect back to the generic fields index page
					return $this->redirect()->toRoute("front-form-admin/generic-fields");
				}//end if
			} catch (\Exception $e) {
				//set error message
				$this->flashMessenger()->addErrorMessage($e->getMessage());

				//redirect back to the generic fields index page
				return $this->redirect()->toRoute("front-form-admin/generic-fields");
			}//end catch
		}//end if

		return array(
				"form" => $form,
		);
	}//end function

	public function deleteGenericFieldAction()
	{
		$id = $this->params()->fromRoute("id", "");
		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Generic field could not be loaded. Id is not set");

			//redirect back to the generic fields index page
			return $this->redirect()->toRoute("front-form-admin/generic-fields");
		}//end if

 		try {
			//load the field data
			$objField = $this->getGenericFieldsModel()->fetchGenericField($id);

			$request = $this->getRequest();
			if ($request->isPost())
			{
				if ($this->params()->fromPost("delete") == "Yes")
				{
					//delete the field
					$this->getGenericFieldsModel()->deleteGenericField($objField);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Generic Field has been deleted");

					//redirect back to the generic fields index page
					return $this->redirect()->toRoute("front-form-admin/generic-fields");
				}//end if
			}//end if
		} catch (\Exception $e) {
			//set error message
			$this->flashMessenger()->addErrorMessage($e->getMessage());

			//redirect back to the generic fields index page
			return $this->redirect()->toRoute("front-form-admin/generic-fields");
		}//end catch

		return array(
					"objField" => $objField,
				);
	}//end function

	/**
	 * Create an instance of the Generic Fields Model using the Service Manager
	 * @return \FrontFormAdmin\Models\FrontGenericFieldsAdminModel
	 */
	private function getGenericFieldsModel()
	{
		if (!$this->model_generic_fields)
		{
			$this->model_generic_fields = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontGenericFieldsAdminModel");
		}//end if

		return $this->model_generic_fields;
	}//end if
}//end class