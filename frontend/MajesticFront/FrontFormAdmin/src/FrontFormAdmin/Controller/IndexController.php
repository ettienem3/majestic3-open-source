<?php
namespace FrontFormAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * Dealing with forms
 * @author ettiene
 *
 */
class IndexController extends AbstractActionController
{
	/**
	 * Container for the Forms admin model
	 * @var \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private $model_forms_admin;

	/**
	 * Container for the Front Behaviours Config Model
	 * @var \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
	 */
	private $model_front_behaviours_config;

	/**
	 * List forms
	 * @see \Zend\Mvc\Controller\AbstractActionController::indexAction()
	 */
    public function indexAction()
    {
    	$arr_params = (array) $this->params()->fromQuery();
    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$arr_params = array_merge($arr_params, (array) $request->getPost());
    	}//end foreach

       	$objForms = $this->getFormAdminModel()->fetchForms($arr_params);
       	return array(
       			"objForms" => $objForms,
       			"arr_params" => $arr_params,
       	);
    }//end function

    public function ajaxSearchValuesAction()
    {
    	try {
    		switch($this->params()->fromQuery("param"))
    		{
    			case "forms_type_id":
					//load form types
    				$form = $this->getFormAdminModel()->getFormAdminForm();

    				$arr_form_types = $form->get("fk_form_type_id")->getValueOptions();
    				foreach ($arr_form_types as $key => $value)
    				{
    					$arr_data[] = array("id" => $key, "val" => $value);
    				}//end foreach
    				break;
    		}//end switch
    	} catch (\Exception $e) {
    		echo json_encode(array(
    				"error" => 1,
    				"response" => $e->getMessage(),
    		));
    		exit;
    	}//end catch

    	echo json_encode(array(
    			"error" => 0,
    			"response" => $arr_data,
    	), JSON_FORCE_OBJECT);
    	exit;
    }//end function

    /**
     * Create a new form
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>|multitype:\Zend\Form\Form
     */
    public function createFormAction()
    {
    	$form_type = $this->params()->fromQuery("ftype", "");

		//load form
		$form = $this->getFormAdminModel()->getFormAdminForm($form_type);

		//set default content for submit button
		if ($form->has("submit_button"))
		{
			$form->get("submit_button")->setValue("Submit");
		}//end if

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$form->setData($request->getPost());
			if ($form->isValid())
			{
				try {
					//create the form
					$objForm = $this->getFormAdminModel()->createForm($form->getData());

					//set success message
					$this->flashMessenger()->addSuccessMessage("Form created");
					
					//redirect to form edit page
					if ($form_type != "")
					{
						return $this->redirect()->toUrl($this->url()->fromRoute("front-form-admin/form", array("action" => "edit-form", "id" => $objForm->get("id"))) . "?ftype=$form_type");
					}//end if
					
					return $this->redirect()->toRoute("front-form-admin/form", array("action" => "edit-form", "id" => $objForm->get("id")));
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if

		return array("form" => $form);
    }//end function

    /**
     * Update a form
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function editFormAction()
    {
		$id = $this->params()->fromRoute("id", "");
		$form_type = $this->params()->fromQuery("ftype", "");

		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Form could not be loaded. Id is not set");
			//redirect to index page
			return $this->redirect()->toRoute("front-form-admin");
		}//end if

		//load form data
		$objForm = $this->getFormAdminModel()->getForm($id);

		//load form
		$form = $this->getFormAdminModel()->getFormAdminForm($form_type);

		//save form type and remove option from form
		$fk_form_type_id = $objForm->get("fk_form_type_id");

		//disable form type element
		$form->get("fk_form_type_id")->setAttribute("disabled", "disabled");

		//bind data to form
		$form->bind($objForm);

		$request = $this->getRequest();
		if ($request->isPost())
		{
			$arr_data = $request->getPost();
			$arr_data["fk_form_type_id"] = $fk_form_type_id;
			$form->setData($arr_data);

			if ($form->isValid($request->getPost()))
			{
				try {
					//update the form
					$objForm = $form->getData();
					$objForm->set("id", $id);
					$objForm->set("fk_form_type_id", $fk_form_type_id);

					$objForm = $this->getFormAdminModel()->editForm($objForm);

					//set success message
					$this->flashMessenger()->addSuccessMessage("Form has been updated");

					//redirect to index page
					return $this->redirect()->toRoute("front-form-admin");
				} catch (\Exception $e) {
					//set error message
					$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
				}//end catch
			}//end if
		}//end if

		if ($objForm->get("id") == "")
		{
			//reload form data
			$objForm = $this->getFormAdminModel()->getForm($id);
		}//end if

		return array(
				"form" => $form,
				"objForm" => $objForm
		);
    }//end function

    /**
     * Delete an existing form
     * @return Ambigous <\Zend\Http\Response, \Zend\Stdlib\ResponseInterface>
     */
    public function deleteFormAction()
    {
		$id = $this->params()->fromRoute("id");
		if ($id == "")
		{
			$this->flashMessenger()->addErrorMessage("Form could not be deleted. ID is not set");

			//return to index page
			return $this->redirect()->toRoute("front-form-admin");
		}//end if

		//load data
		try {
			$objForm = $this->getFormAdminModel()->fetchForm($id);

			if (!$objForm)
			{
				$this->flashMessenger()->addErrorMessage("A problem occurred. The requested form could not be laoded");
				//return to index page
				return $this->redirect()->toRoute("front-form-admin");
			}//end if
		} catch (\Exception $e) {
    		//set error message
    		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));

			//return to index page
			return $this->redirect()->toRoute("front-form-admin");
		}//end catch

		$request = $this->getRequest();
		if ($request->isPost())
		{
			if (strtolower($request->getPost("delete")) == "yes")
			{
				try {
					$this->getFormAdminModel()->deleteForm($id);

					//set message
					$this->flashMessenger()->addSuccessMessage("Form deleted successfully");
				} catch (\Exception $e) {
					//set error message
					$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
				}//end catch
			}//end if
			
			//return to index page
			return $this->redirect()->toRoute("front-form-admin");
		}//end if

		return array(
			"objForm" => $objForm,
		);
    }//end function

    public function formBehavioursAction()
    {
    	//set layout
    	$this->layout("layout/behaviours-view");

    	//set data array to collect behaviours and pass url data to view
    	$arr_behaviour_params = array(
    			"form_id" => $this->params()->fromRoute("id"),
    			"behaviour" => $this->params()->fromQuery("behaviour", "form"),
    	);

    	//load behaviours form
    	$arr_config_form_data = $this->getFrontBehavioursModel()->getBehaviourActionsForm($this->params()->fromQuery("behaviour", "form"), $arr_behaviour_params);
    	$form = $arr_config_form_data["form"];
    	$arr_descriptors = $arr_config_form_data["arr_descriptors"];

    	//load current field behaviours...
    	$objBehaviours = $this->getFrontBehavioursModel()->fetchBehaviourActions($arr_behaviour_params);

    	//load the form data
    	$objForm = $this->getFormAdminModel()->getForm($this->params()->fromRoute("id"));

    	//check if behaviour is being reconfigured
    	if (is_numeric($this->params()->fromQuery("behaviour_id", "")))
    	{
    		$objBehaviour = $this->getFrontBehavioursModel()->fetchBehaviour($this->params()->fromQuery("behaviour_id"));
    	} else {
    		$objBehaviour = FALSE;
    	}//end if

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$form->setData($request->getPost());

    		if ($form->isValid())
    		{
    			$arr_params = $form->getData();
    			$arr_params["behaviour"] = $this->params()->fromQuery("behaviour", "form");
    			$arr_behaviour_params["beh_action"] = $arr_params["beh_action"];

    			$form = $this->getFrontBehavioursModel()->getBehaviourConfigForm($arr_params);

    			//check if a local defined form exists for the behaviour, sometime needed since the api wont render the form correctly
    			$class = "\\FrontBehavioursConfig\\Forms\\Forms\\Behaviour" . str_replace(" ", "", ucwords(str_replace("_", " ", $arr_params['beh_action']))) . "Form";
    			
    			if (class_exists($class))
    			{
    				$form = new $class($form);
    			}//end if
    			
    			//assign data to form is behaviour is being reconfigured
    			if ($objBehaviour instanceof \FrontBehaviours\Entities\FrontBehavioursBehaviourConfigEntity)
    			{
    				$form->bind($objBehaviour);
    			}//end if

    			//check if submitted form is the complete behaviour config
    			if ($this->params()->fromPost("setup_complete", 0) == 1)
    			{
    				//revalidate the form
    				$form->setData($request->getPost());
    				if ($form->isValid())
    				{
    					if ($objBehaviour === FALSE)
    					{
    						//set additional params
    						$arr_form_data = $form->getData();
    						$arr_form_data["form_id"] = $this->params()->fromRoute("id");

    						//create/update the behaviour
    						$objBehaviour = $this->getFrontBehavioursModel()->createBehaviourAction($arr_form_data);

    						//redirect back to the "index" view
    						return $this->redirect()->toUrl($this->url()->fromRoute("front-form-admin/form", array("action" => "form-behaviours", "id" => $this->params()->fromRoute("id"))));
    					} else {
    						//set additional params
    						$objBehaviour = $form->getData();
    						$objBehaviour->set("form_id", $this->params()->fromRoute("id"));

    						//update the behaviour
    						$objBehaviour = $this->getFrontBehavioursModel()->editBehaviourAction($objBehaviour);

    						//redirect back to the "index" view
    						return $this->redirect()->toUrl($this->url()->fromRoute("front-form-admin/form", array("action" => "form-behaviours", "id" => $this->params()->fromRoute("id"))));
    					}//end if
    				}//end if
    			}//end if
    		}//end if
    	}//end if

    	$viewModel = new ViewModel(array(
				//form to add behavours
				"form"      			=> $form,
				//existing behaviours
				"objBehaviours" 		=> $objBehaviours,
				//behaviour params
				"arr_behaviour_params" 	=> $arr_behaviour_params,
				//action descriptions
				"arr_descriptors" 		=> $arr_descriptors,
    			//load form data
    			"objForm"				=> $objForm,
    			//set header
    			"behaviours_header" 	=> "Behaviours configured for <span class=\"text-info\">" . $objForm->get("form") . "</span>",
    	));
    	$viewModel->setTemplate('front-behaviours-config/index/configure-behaviours.phtml');

    	return $viewModel;
    }//end function

    public function orderFieldsAction()
    {
    	$id = $this->params()->fromRoute("id", "");

    	if ($id == "")
    	{
    		//set error message
    		$this->flashMessenger()->addErrorMessage("Form could not be loaded. Id is not set");
    		//redirect to index page
    		return $this->redirect()->toRoute("front-form-admin");
    	}//end if

    	//load form
    	$objForm = $this->getFormAdminModel()->getForm($id);

    	return array(
    		"objForm" => $objForm,
    	);
    }//end function

    /**
     * Request data about a form via ajax
     */
    public function ajaxLoadFormDataAction()
    {
    	//extract information
    	$form_id = $this->params()->fromRoute("id", "");

    	if ($form_id == "")
    	{
    		//set error message
    		return new JsonModel(array("error" => "Field information could not be loaded. Field id or Field Type is not available"));
    	}//end if

    	//load the form
    	$objForm = $this->getFormAdminModel()->getForm($form_id);

    	return new JsonModel($objForm->getArrayCopy());
    }//end function

    /**
     * Create an instance of the Forms Admin model using the Service Manager
     * @return \FrontFormAdmin\Models\FrontFormAdminModel
     */
    private function getFormAdminModel()
    {
    	if (!$this->model_forms_admin)
    	{
    		$this->model_forms_admin = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontFormAdminModel");
    	}//end function

    	return $this->model_forms_admin;
    }//end function

    /**
     * Create an instance of the Front Behaviours Config Model using the Service Manager
     * @return \FrontBehavioursConfig\Models\FrontBehavioursConfigModel
     */
    private function getFrontBehavioursModel()
    {
    	if (!$this->model_front_behaviours_config)
    	{
    		$this->model_front_behaviours_config = $this->getServiceLocator()->get("FrontBehavioursConfig\Models\FrontBehavioursConfigModel");
    	}//end if

    	return $this->model_front_behaviours_config;
    }//end function
}//end class
