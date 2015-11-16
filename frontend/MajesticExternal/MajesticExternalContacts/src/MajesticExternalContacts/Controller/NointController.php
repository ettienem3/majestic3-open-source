<?php
namespace MajesticExternalContacts\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class NointController extends AbstractActionController
{
	/**
	 * Container for the External Contacts Model
	 * @var \MajesticExternalContacts\Models\MajesticExternalContactsModel
	 */
	private $model_external_contacts;
	
    public function nointAction()
    {
    	$reg_id = $this->params()->fromRoute("reg_id", "");
		$comm_history_id = $this->params()->fromRoute("comm_history_id", "");
		
		//check required values are set and encoded
		if ($reg_id == "" || $comm_history_id == "" || is_numeric($reg_id) || is_numeric($comm_history_id))
		{
			echo "Request could not be completed. Required information is not available";
			exit;
		}//end if
		
		try {
			//send instruction
			$objJourneyStatus = $this->getExternalContactsModel()->setNoInterestJourneyStatus($reg_id, $comm_history_id);
		} catch (\Exception $e) {
			echo $e->getMessage();
			exit;
		}//end catch
		
		return array(
			"objJourneyStatus" => $objJourneyStatus,
		);
    }//end function   
    
    /**
     * Create an instance of the External Contacts Model
     * @return \MajesticExternalContacts\Models\MajesticExternalContactsModel
     */
    private function getExternalContactsModel()
    {
    	if (!$this->model_external_contacts)
    	{
    		$this->model_external_contacts = $this->getServiceLocator()->get("MajesticExternalContacts\Models\MajesticExternalContactsModel");
    	}//end if
    	
    	return $this->model_external_contacts;
    }//end function
}//end class
