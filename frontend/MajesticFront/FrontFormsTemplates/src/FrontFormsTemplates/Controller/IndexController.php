<?php
namespace FrontFormsTemplates\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
	/**
	 *
	 * Container for the FormsTemplates Model instance
	 * @var \FrontFormsTemplates\Models\FrontFormsTemplatesModel
	 *
	 */
	private $model_forms_templates;

    public function indexAction()
    {
    	//load form templates
    	$objFormsTemplates = $this->getFormsTemplatesModel()->getFormsTemplates($this->params()->fromQuery());

    	return array("objFormsTemplates" => $objFormsTemplates);
    }// end function

    	/**
		 *
		 * Create new form template
		 * @return multitype:\Zend\Form\Form
		 */
		public function createAction()
		{
			$form = $this->getFormsTemplatesModel()->getAdminFormsTemplates();
			$request = $this->getRequest();
			
			//set default content
			$form->get("content")->setValue("#content");
			
			if ($request->isPost())
			{
				//set form template data
				$form->setData($request->getPost());

				if ($form->isValid())
				{
					try {
						// create the template
						$objFormTemplate = $this->getFormsTemplatesModel()->createFormTemplate($form->getData());

						//set success message
						$this->flashMessenger()->addSuccessMessage("Look and Feel has been created");

						// redirect to the index page
						return $this->redirect()->toRoute("front-form-templates");
					}	catch (\Exception $e) {
    					//set error message
    					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
					}// end catch
				}//end function
			}//end if

			return array("form" => $form);
		}// end function

		/**
		 *
		 * Update an existing form template
		 * @return Ambiguous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface> |multitype:\Zend\Form\Form
		 */

		public function editAction()
		{
			// get id from route
			$id = $this->params()->fromRoute("id", "");

			if ($id == "")
			{
				//set error message
				$this->flashMessenger()->addErrorMessage("Template could not be loaded. Id is not set");
				return $this->redirect()->toRoute("front-form-templates");

			}//end if

			//load Template details
			$objFormTemplate = $this->getFormsTemplatesModel()->getFormTemplate($id);

			//load the system form
			$form = $this->getFormsTemplatesModel()->getAdminFormsTemplates();

			//bind the data
			$form->bind($objFormTemplate);

			$request = $this->getRequest();
			if ($request->isPost())
			{
				// set form data
				$form->setData($request->getPost());

					if ($form->isValid())
					{
						try {
							$objFormTemplate = $form->getData();
							$objFormTemplate->set("id", $id);
							$objFormTemplate = $this->getFormsTemplatesModel()->updateTemplate($objFormTemplate);

							// set success message
							$this->flashMessenger()->addSuccessMessage("Template Updated");
							return $this->redirect()->toRoute("front-form-templates");
						} catch (\Exception $e) {
							//set message
							$this->flashMessenger()->addErrorMessage($e->getMessage());
						}// end if
					}//end if
			}// end if

			return array(
					"form" => $form,
					"objFormTemplate" => $objFormTemplate,
			);
		}//end function

		/**
		 * Delete the existing form template
		 */
		public function deleteAction()
		{
			$id = $this->params()->fromRoute("id", "");

			if ($id == "")
			{
				//set error message
				$this->flashMessenger()->addErrorMessage("The Template could not be dleted. Id is not set");
				//return to index page
				return $this->redirect()->toRoute("front-form-templates");
			}//end if

			$request = $this->getRequest();
			if ($request->isPost())
			{
				if (strtolower($request->getPost("delete")) == "yes")
				{
					//delete the link
					try {
						$objFormsTemplates = $this->getFormsTemplatesModel()->deleteTemplate($id);

						//set message
						$this->flashMessenger()->addSuccessMessage("Template Deleted");
					} catch (\Exception $e) {
						//set errror message
						$this->flashMessenger()->addErrorMessage($e->getMessage());
					}//end catch
				}//end if

				//redirect to index page
				return $this->redirect()->toRoute("front-form-templates");
			}//end if
		}// end function

		/**
		 *
		 * Activate or deactivate a form template
		 */
		public function statusAction()
		{
			$id = $this->params()->fromRoute("id", "");

			if ($id == "")
			{
				//set error message
				$this->flashMessenger()->addErrorMessage("Template could not be Activated. Id is not set");
				//return to index page
				return $this->redirect()->toRoute("front-form-templates");
			}//end if

			try {
				//load the template details
				$objFormTemplate = $this->getFormsTemplatesModel()->getFormTemplate($id);
				$objFormTemplate->set("active", (1 - $objFormTemplate->get("active")));
				//update the template
				$objFormTemplate = $this->getFormsTemplatesModel()->updateTemplate($objFormTemplate);

				//set success message
				$this->flashMessenger()->addSuccessMessage("Template Status Updated");
			} catch (\Exception $e) {
				//set message
				$this->flashMessenger()->addErrorMessage($e->getMessage());
			}//end if

			//redirect to index page
			return $this->redirect()->toRoute("front-form-templates");
		}// end function

	    /**
	     * Creates an instance of the Form Template model using the service manager
	     * @return \FrontFormsTemplates\Models\FrontFormsTemplatesModel
	     */
	    private function getFormsTemplatesModel()
	    {
	    	if (!$this->model_forms_templates)
	    	{
	    		$this->model_forms_templates = $this->getServiceLocator()->get("FrontFormsTemplates\Models\FrontFormsTemplatesModel");
	    	}// end if

	    	return $this->model_forms_templates;
	    }//end function
}// end class
