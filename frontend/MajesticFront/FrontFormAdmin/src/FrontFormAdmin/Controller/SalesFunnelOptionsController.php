<?php
namespace FrontFormAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SalesFunnelOptionsController extends AbstractActionController
{
	/**
	 * Container for the Forms admin model
	 * @var \FrontFormAdmin\Models\FrontFormAdminModel
	 */
	private $model_forms_admin;
	
	public function sfAdvancedSettingsAction()
	{
		$id = $this->params()->fromRoute("id", "");
		 
		if ($id == "")
		{
			//set error message
			$this->flashMessenger()->addErrorMessage("Tracker could not be loaded. ID is not set");
			//redirect to index page
			return $this->redirect()->toRoute("front-form-admin");
		}//end if
		 
		//load form data
		$objForm = $this->getFormAdminModel()->getForm($id);		
		 
		//load deal number form
		$objDealNumberAdminForm = $this->getFormAdminModel()->getSalesFunnelDealNumberFieldForm();
		$objDealNumberAdminForm->remove("submit");
		$objDealNumberAdminForm->add(array(
			"type" => "submit",
			"name" => "submit_deal_number",
			"attributes" => array(
				"value" => "Save",
				"class" => "btn btn-primary",
			),
		));
		
		//load sales funnel deal number field information
		$objDealNumberField = $this->getFormAdminModel()->fetchSalesFunnelDealNumberField($id);
		if ($objDealNumberField->get("id") != "")
		{
			$objDealNumberAdminForm->bind($objDealNumberField);
		}//end if
		 
		//load deal status form
		$objDealStatusAdminForm = $this->getFormAdminModel()->getSalesFunnelDealStatusFieldForm();
		$objDealStatusAdminForm->remove("submit");
		$objDealStatusAdminForm->add(array(
			"type" => "submit",
			"name" => "submit_deal_status",
			"attributes" => array(
				"value" => "Save",
				"class" => "btn btn-primary"
			),
		));
		
		//load sales funnel deal status field information\
		try {
			$objDealStatusField = $this->getFormAdminModel()->fetchSalesFunnelStatusField($id);
			if ($objDealStatusField->get("id") != "")
			{
				//extract some field values
				$arr_dropdown_values = explode("\r\n", $objDealStatusField->get("fields_custom_field_values"));
				$arr = array();
				foreach ($arr_dropdown_values as $k => $v)
				{
					$arr[$v] = $v;
				}//end foreach
				
				$objDealStatusField->set("sf_status_success", explode("|", $objDealStatusField->get("sf_status_success")));
				$objDealStatusField->set("sf_status_closed", explode("|", $objDealStatusField->get("sf_status_closed")));
				
				$objDealStatusAdminForm->get("sf_status_open")->setValueOptions($arr);
				$objDealStatusAdminForm->get("sf_status_success")->setValueOptions($arr);
				$objDealStatusAdminForm->get("sf_status_closed")->setValueOptions($arr);
				$objDealStatusAdminForm->bind($objDealStatusField);
			}//end if
		} catch (\Exception $e) {
			$objDealStatusField = FALSE;
		}//end catch
		 
		$request = $this->getRequest();
		if ($request->isPost())
		{
			//allocate status field
			if ($request->getPost("create_sf_status_field") != "")
			{
				//load field data
				$objField = $this->getFormAdminModel()->getFormField($objForm->get("id"), $request->getPost("allocate_sf_status_field"), "custom");
				
				//update field settings
				$objField->set("sf_status_field", 1);
				
				//save the field
				$this->getFormAdminModel()->updateFormField($objField);
				
				//reload the page
				return $this->redirect()->toRoute("front-form-admin/sales-funnel", array("action" => "sf-advanced-settings", "id" => $objForm->get("id")));
			}//end if
			
			//remove status field
			if ($request->getPost("remove_sf_status_field") != "")
			{
				//load field data
				$objField = $this->getFormAdminModel()->getFormField($objForm->get("id"), $request->getPost("sf_status_field_id"), "custom");
			
				//update field settings
				$objField->set("sf_status_field", 0);
			
				//save the field
				$this->getFormAdminModel()->updateFormField($objField);
			
				//reload the page
				return $this->redirect()->toRoute("front-form-admin/sales-funnel", array("action" => "sf-advanced-settings", "id" => $objForm->get("id")));
			}//end if
			
			//update status field settings
			if ($request->getPost("submit_deal_status") != "")
			{
				//load field data
				$objField = $this->getFormAdminModel()->getFormField($objForm->get("id"), $request->getPost("sf_status_field_id"), "custom");
				$objField->set("sf_status_open", $request->getPost("sf_status_open"));
				$objField->set("sf_status_success", $request->getPost("sf_status_success"));
				$objField->set("sf_status_closed", $request->getPost("sf_status_closed"));
				
				//save the field
				$this->getFormAdminModel()->updateFormField($objField);
					
				//reload the page
				return $this->redirect()->toRoute("front-form-admin/sales-funnel", array("action" => "sf-advanced-settings", "id" => $objForm->get("id")));
			}//end if
			
			//update deal number settings
			if ($request->getPost("submit_deal_number") != "")
			{
				//load field data
				$objField = $this->getFormAdminModel()->getFormField($objForm->get("id"), $objDealNumberField->get("fields_custom_id"), "custom");
				$objField->set("sf_id_field", $request->getPost("sf_id_field"));
			
				//save the field
				$this->getFormAdminModel()->updateFormField($objField);
					
				//reload the page
				return $this->redirect()->toRoute("front-form-admin/sales-funnel", array("action" => "sf-advanced-settings", "id" => $objForm->get("id")));
			}//end if			
		}//end if
		
		return array(
				"objDealStatusField" => $objDealStatusField,
				"deal_status_form" => $objDealStatusAdminForm,
				"objDealNumberField" => $objDealNumberField,
				"deal_number_form" => $objDealNumberAdminForm,
				"objForm" => $objForm,
			);
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
}//end class