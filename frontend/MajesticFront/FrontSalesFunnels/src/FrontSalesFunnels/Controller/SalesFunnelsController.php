<?php
namespace FrontSalesFunnels\Controller;

use FrontCore\Adapters\AbstractCoreActionController;

class SalesFunnelsController extends AbstractCoreActionController
{
	/**
	 * Container for the Front Contacts Model
	 * @var \FrontContacts\Models\FrontContactsModel
	 */
	private $model_contacts;

	/**
	 * Container for the Front Sales Funnels Model
	 * @var \FrontSalesFunnels\Models\FrontSalesFunnelsModel
	 */
	private $model_sales_funnels;

	/**
	 * Container for the Form Admin Model
	 * @var \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private $model_form_admin;

	/**
	 * Container for the External Forms Model
	 * @var \MajesticExternalForms\Models\MajesticExternalFormsModel
	 */
	private $model_external_forms;

    public function indexAction()
    {
    	try {
    		$objContact = $this->loadContact();
    	} catch (\Exception $e) {
    		$this->flashMessenger()->addErrorMessage($e->getMessage());
    		return $this->redirect()->toRoute("front-contacts");
    	}//end catch

       	//load contact sales funnels
       	try {
       		$objSalesFunnels = $this->getFrontSalesFunnelModel()->fetchSalesFunnels(array("contact_id" => $objContact->get("id")));
       	} catch (\Exception $e) {
       		$this->flashMessenger()->addErrorMessage($this->frontControllerErrorHelper()->formatErrors($e));
       		return $this->redirect()->toRoute('home');
       	}//end catch

       	return array(
       		"objContact" => $objContact,
       		"objSalesFunnels" => $objSalesFunnels,
       	);
    }//end function

    public function createAction()
    {
    	try {
    		$objContact = $this->loadContact();
    	} catch (\Exception $e) {
    		$this->flashMessenger()->addErrorMessage($e->getMessage());
    		return $this->redirect()->toRoute("front-contacts");
    	}//end catch

    	$form_id = $this->params()->fromQuery("fid", "");
    	if ($form_id == "")
    	{
    		$this->flashMessenger()->addErrorMessage("Tracker could not be loaded. Form ID is not set");
    		return $this->redirect()->toRoute("front-sales-funnels", array("reg_id" => $objContact->get("id")));
    	}//end if

    	//load sales funnel form
    	$arr_form = $this->getExternalFormsModel()->loadForm($form_id, NULL, array("behaviour" => "__sales_funnel", "cache_clear" => 1));
		$form = $arr_form["objForm"];

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$form->setData($request->getPost());

    		if ($form->isValid($request->getPost()))
    		{
    			try {
    				//extract data
    				$arr_data = $form->getData();

    				//set additional variables
    				$arr_data["fk_form_id"] = $form_id;

    				//create the sales funnel
    				$objSalesFunnel = $this->getFrontSalesFunnelModel()->createSalesFunnel($objContact, $arr_data);

    				//set success message
    				$this->flashMessenger()->addSuccessMessage("Tracker has been created");

    				//redirect back to the index page
    				return $this->redirect()->toRoute("front-sales-funnels", array("reg_id" => $objContact->get("id")));
    			} catch (\Exception $e) {
    				//set error message
    				$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			}//end catch
    		}//end if
    	}//end if

    	return array(
    		"arr_form" => $arr_form,
    		"form" => $form,
    		"objContact" => $objContact,
    	);
    }//end function

    public function editAction()
    {
    	try {
    		$objContact = $this->loadContact();
    	} catch (\Exception $e) {
    		$this->flashMessenger()->addErrorMessage($e->getMessage());
    		return $this->redirect()->toRoute("front-contacts");
    	}//end catch

    	$id = $this->params()->fromRoute("id", "");
    	if ($id == "")
    	{
    		$this->flashMessenger()->addErrorMessage("Requested Tracker could not be loaded. ID is not set");
    		return $this->redirect()->toRoute("front-sales-funnels", array("reg_id" => $objContact->id));
    	}//end if

    	//load sales funnel
    	try {
    		$objSalesFunnel = $this->getFrontSalesFunnelModel()->fetchSalesFunnel($objContact, $id);
    		if (!is_numeric($objSalesFunnel->get("fk_form_id")))
    		{
    			$this->flashMessenger()->addErrorMessage("Tracker cannot be updated. Form ID is not available from data");
    			return $this->redirect()->toRoute("front-sales-funnels", array("reg_id" => $objContact->id));
    		} else {
    			$form_id = $objSalesFunnel->get("fk_form_id");
    		}//end if
    	} catch (\Exception $e) {
    		$this->flashMessenger()->addErrorMessage($e->getMessage());
    		return $this->redirect()->toRoute("front-sales-funnels", array("reg_id" => $objContact->id));
    	}//end catch

    	//load sales funnel form
    	$arr_form = $this->getExternalFormsModel()->loadForm($form_id, NULL, array("behaviour" => "__sales_funnel"));

    	$form = $arr_form["objForm"];

    	//add some additional options to the form
    	$form->add(array(
    		"type" => "select",
    		"name" => "front_sf_options",
    		"attributes" => array(
    			"id" => "front_sf_options",
    		),
    		"options" => array(
    			"label" => "Extra Options",
    			"empty_option" => "--select--",
    			"value_options" => array(
    				"create_duplicate" => "Create a duplicate copy of the original Tracker",
    			),
    		),

    	));

    	//bind sales funnel data
    	$form->bind($objSalesFunnel);

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$form->remove("front_sf_options");
    		$form->setData($request->getPost());

    		if ($form->isValid($request->getPost()))
    		{
    			try {
    				//extract data
    				$obj = $form->getData();

    				//set additional variables
    				$obj->set("id", $id);
    				$obj->set("fk_form_id", $form_id);

    				switch ($request->getPost("front_sf_options"))
    				{
    					default:
    						//update the sales funnel
    						$objSalesFunnel = $this->getFrontSalesFunnelModel()->editSalesFunnel($objContact, $obj);

    						//set success message
    						$this->flashMessenger()->addSuccessMessage("Tracker has been updated");
    						break;

    					case "create_duplicate":
    						//create the sales funnel
    						$arr_data = $obj->getArrayCopy();
    						unset($arr_data["id"]);
    						$objSalesFunnel = $this->getFrontSalesFunnelModel()->createSalesFunnel($objContact, $arr_data);

    						//set success message
    						$this->flashMessenger()->addSuccessMessage("Tracker has been duplicated");
    						break;
    				}//end switch

    				//redirect back to the index page
    				return $this->redirect()->toRoute("front-sales-funnels", array("reg_id" => $objContact->get("id")));
    			} catch (\Exception $e) {
    				//set error message
    				$form = $this->frontFormHelper()->formatFormErrors($form, $e->getMessage());
    			}//end catch
    		}//end if
    	}//end if

    	return array(
    		"arr_form" => $arr_form,
    		"form" => $form,
    		"objContact" => $objContact,
    	);
    }//end function

    public function deleteAction()
    {
    	try {
    		$objContact = $this->loadContact();
    	} catch (\Exception $e) {
    		$this->flashMessenger()->addErrorMessage($e->getMessage());
    		return $this->redirect()->toRoute("front-contacts");
    	}//end catch

    	$id = $this->params()->fromRoute("id", "");
    	if ($id == "")
    	{
    		$this->flashMessenger()->addErrorMessage("Requested Tracker could not be loaded. ID is not set");
    		return $this->redirect()->toRoute("front-sales-funnels", array("reg_id" => $objContact->id));
    	}//end if

    	//load sales funnel
    	try {
    		$objSalesFunnel = $this->getFrontSalesFunnelModel()->fetchSalesFunnel($objContact, $id);
    	} catch (\Exception $e) {
    		$this->flashMessenger()->addErrorMessage($e->getMessage());
    		return $this->redirect()->toRoute("front-sales-funnels", array("reg_id" => $objContact->id));
    	}//end catch

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		if (strtolower($request->getPost("delete")) == "yes")
    		{
    			try {
    				$this->getFrontSalesFunnelModel()->deleteSalesFunnel($objContact, $objSalesFunnel);
    				$this->flashMessenger()->addSuccessMessage("Tracker has been deleted");
    			} catch (\Exception $e) {
    				$this->flashMessenger()->addErrorMessage($e->getMessage());
    			}//end catch
    		}//end if

    		return $this->redirect()->toRoute("front-sales-funnels", array("reg_id" => $objContact->get("id")));
    	}//end if

    	return array(
    			"objContact" => $objContact,
    			"objSalesFunnel" => $objSalesFunnel,
    	);
    }//end function

    public function ajaxLoadSalesFunnelsAction()
    {
    	$objForms = $this->getFormAdminModel()->fetchForms(array(
    		"form_type_behaviour" => "__sales_funnel",
    		"forms_active" => 1,
    	));

    	$reg_id = $this->params()->fromRoute("reg_id");

    	$html = "";
    	$i = 0;
    	foreach ($objForms as $objForm)
    	{
    		$html .= "<a href=\"" . $this->url()->fromRoute("front-sales-funnels", array("reg_id" => $reg_id, "action" => "create")) . "?fid=" . $objForm->id . "\" title=\"" . $objForm->form . "\" data-toggle=\"tooltip\">" . $objForm->form . "</a><br/>";
    		$i++;
    	}//end foreach

    	if ($i == 0)
    	{
    		echo json_encode(array("error" => 1, "response" => "You cannot create a Tracker now. Please create a new form with a type \"Tracker\""));
    		exit;
    	}//end if

    	echo json_encode(array("error" => 0, "response" => $html));
    	exit;
    }//end function

    /**
     * Load the request contact
     * @throws \Exception
     * @return \FrontContacts\Entities\FrontContactsContactEntity
     */
    private function loadContact()
    {
    	$reg_id = $this->params()->fromRoute("reg_id", "");
    	if ($reg_id == "")
    	{
    		throw new \Exception("Trackers could not be loaded. Contact is not specified");
    	}//end if

    	//load the contact
    	$objContact = $this->getFrontContactsModel()->fetchContact($reg_id);
    	if (!$objContact instanceof \FrontContacts\Entities\FrontContactsContactEntity)
    	{
    		throw new \Exception("Trackers could not be loaded. Contact could not be located");
    	}//end if

    	if (!is_numeric($reg_id))
    	{
    		$location = $this->url()->fromRoute("front-sales-funnels", array("reg_id" => $objContact->get("id")));
    		$arr_query_params = (array) $this->params()->fromQuery();
    		if (count($arr_query_params) > 0)
    		{
    			$location .= "?" . http_build_query($arr_query_params);
    		}//end if

			header("location:" . $location);
			exit;
    	}//end if

    	return $objContact;
    }//end function

    /**
     * Create an instance of the Contacts Model using the Service Manager
     * @return \FrontContacts\Models\FrontContactsModel
     */
    private function getFrontContactsModel()
    {
    	if (!$this->model_contacts)
    	{
    		$this->model_contacts = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsModel");
    	}//end if

    	return $this->model_contacts;
    }//end function

    /**
     * Create an instance of the Front Sales Funnel Model using the Service Manager
     * @return \FrontSalesFunnels\Models\FrontSalesFunnelsModel
     */
    private function getFrontSalesFunnelModel()
    {
    	if (!$this->model_sales_funnels)
    	{
    		$this->model_sales_funnels = $this->getServiceLocator()->get("FrontSalesFunnels\Models\FrontSalesFunnelsModel");
    	}//end if

    	return $this->model_sales_funnels;
    }//end function

    /**
     * Create an instance of the Form Admin Model using the Service Manager
     * @return \FrontFormAdmin\Models\FrontFormAdminModel
     */
    private function getFormAdminModel()
    {
    	if (!$this->model_form_admin)
    	{
    		$this->model_form_admin = $this->getServiceLocator()->get("FrontFormAdmin\Models\FrontFormAdminModel");
    	}//end if

    	return $this->model_form_admin;
    }//end function

    /**
     * Create an instance of the External Forms Model using the Service Manager
     * @return \MajesticExternalForms\Models\MajesticExternalFormsModel
     */
    private function getExternalFormsModel()
    {
    	if (!$this->model_external_forms)
    	{
    		$this->model_external_forms = $this->getServiceLocator()->get("MajesticExternalForms\Models\MajesticExternalFormsModel");
    	}//end if

    	return $this->model_external_forms;
    }//end function
}//end class
