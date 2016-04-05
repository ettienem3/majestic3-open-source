<?php
namespace MajesticExternalContacts\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class UnsubscribeController extends AbstractActionController
{
	/**
	 * Container for the External Contacts Model
	 * @var \MajesticExternalContacts\Models\MajesticExternalContactsModel
	 */
	private $model_external_contacts;

	public function indexAction()
	{
		$reg_id = $this->params()->fromRoute("reg_id", "");

		//check required values are set and encoded
		if ($reg_id == "" || is_numeric($reg_id))
		{
			echo "Request could not be completed. Required information is not available";
			exit;
		}//end if
	}//end function

    public function unsubscribeAction()
    {
        $reg_id = $this->params()->fromRoute("reg_id", "");

		//check required values are set and encoded
		if ($reg_id == "" || is_numeric($reg_id))
		{
			echo "Request could not be completed. Required information is not available";
			exit;
		}//end if

		$comm_history_id = $this->params()->fromQuery("cid", "");

        try {
	        //load the contact
	        $objContactChannels = $this->getExternalContactsModel()->loadContactSubscriptionStatus($reg_id);

	        //load the form
	        $form = $this->getExternalContactsModel()->getContactUnsubscribeForm($objContactChannels, $reg_id);
        } catch (\Exception $e) {
        	//extract error
        	trigger_error($e->getMessage(), E_USER_WARNING);
        	$arr_t = explode("||", $e->getMessage());
			$objError = json_decode(array_pop($arr_t));
			$arr_t = explode(":", $objError->HTTP_RESPONSE_MESSAGE);
			$this->flashMessenger()->addErrorMessage(array_pop($arr_t));

        	return $this->redirect()->toRoute("majestic-external-contacts-unsub", array("reg_id" => $reg_id));
        }//end catch

       	$request = $this->getRequest();
       	if ($request->isPost())
       	{
       		$form->setData($request->getPost());
       		if ($form->isValid($request->getPost()))
       		{
       			try {
       				$arr_data = (array) $form->getData();
       				$arr_data["cid"] = $comm_history_id;

	       			//submit unsubscribe information
	       			$objChannels = $this->getExternalContactsModel()->updateContactUnsubscribeChannels($reg_id, $arr_data);

	       			//set message
	       			$this->flashMessenger()->addSuccessMessage("Your subscription options have been saved");
       			} catch (\Exception $e) {
       				$this->flashMessenger()->addErrorMessage("Your subscription options could not be saved");

       				//extract error
       				trigger_error($e->getMessage(), E_USER_WARNING);
       				$arr_t = explode("||", $e->getMessage());
       				$objError = json_decode(array_pop($arr_t));
       				$arr_t = explode(":", $objError->HTTP_RESPONSE_MESSAGE);
       				$this->flashMessenger()->addErrorMessage(array_pop($arr_t));
       			}//end catch

       			return $this->redirect()->toRoute("majestic-external-contacts-unsub", array("reg_id" => $reg_id));
       		}//end if
       	}//end if

       	return array(
       		"form" => $form,
       		"objContact" => $objContact,
       	);
    }//end function

    public function resubscribeAction()
    {
    	$reg_id = $this->params()->fromRoute("reg_id", "");

    	//check required values are set and encoded
    	if ($reg_id == "" || is_numeric($reg_id))
    	{
    		echo "Request could not be completed. Required information is not available";
    		exit;
    	}//end if

    	try {
    		//load the contact
    		$objContactChannels = $this->getExternalContactsModel()->loadContactSubscriptionStatus($reg_id);

    		//load the form
    		$form = $this->getExternalContactsModel()->getContactUnsubscribeForm($objContactChannels, $reg_id);
    		$form->get('comm_via_id_all')->setAttribute('title', '');
    	} catch (\Exception $e) {
    		$this->flashMessenger()->addErrorMessage($e->getMessage());
    		return $this->redirect()->toRoute("majestic-external-contacts-unsub", array("reg_id" => $reg_id));
    	}//end catch

    	$request = $this->getRequest();
    	if ($request->isPost())
    	{
    		$form->setData($request->getPost());
    		if ($form->isValid($request->getPost()))
    		{
    			try {
    				//submit unsubscribe information
    				$objChannels = $this->getExternalContactsModel()->updateContactResubscribeChannels($reg_id, (array) $form->getData());

    				//set message
    				$this->flashMessenger()->addSuccessMessage("Your subscription options have been saved");
    			} catch (\Exception $e) {
    				$this->flashMessenger()->addErrorMessage("Your subscription options could not be saved. Error: " . $e->getMessage());
    			}//end catch

    			return $this->redirect()->toRoute("majestic-external-contacts-unsub", array("reg_id" => $reg_id));
    		}//end if
    	}//end if

    	//set form submit button value
    	$form->get("submit")->setValue("Resubscribe");

    	return array(
    			"form" => $form,
    			"objContact" => $objContact,
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
