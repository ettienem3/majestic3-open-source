<?php
namespace FrontPanels\Panels;

use FrontCore\Adapters\AbstractCoreAdapter;

abstract class AbstractPanelProcessorModel extends AbstractCoreAdapter
{
	/**
	 * Container for the Contacts Model
	 * @var \FrontContacts\Models\FrontContactsModel
	 */
	private $model_contacts;
	
	/**
	 * Load url view helper
	 * @return \Zend\View\Helper\Url
	 */
	protected function getViewUrlHelper()
	{
		$objUrlHelper = $this->getServiceLocator()->get('viewhelpermanager')->get('url')->getView(); 
		return $objUrlHelper;
	}//end function
	
	/**
	 * Create an instance of the Contacts Model using the Service Manager
	 * @return \FrontContacts\Models\FrontContactsModel
	 */
	protected function getContactsModel()
	{
		if (!$this->model_contacts)
		{
			$this->model_contacts = $this->getServiceLocator()->get("FrontContacts\Models\FrontContactsModel");
		}//end if
		
		return $this->model_contacts;
	}//end function
}//end class