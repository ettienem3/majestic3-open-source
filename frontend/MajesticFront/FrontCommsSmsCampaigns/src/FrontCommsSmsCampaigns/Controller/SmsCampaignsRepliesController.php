<?php
namespace FrontCommsSmsCampaigns\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class SmsCampaignsRepliesController extends AbstractActionController
{
	/**
	 * Container for the Front Sms Campaigns Replies Model
	 * @var \FrontCommsSmsCampaigns\Models\FrontCommsSmsCampaignsRepliesModel
	 */
	private $model_sms_campaign_replies;
	
	public function indexAction()
	{
		$id = $this->params()->fromRoute("sms_campaign_id", "");
		if ($id == "")
		{
			//set error
			$this->flashMessenger()->addErrorMessage("SMS Campaign Replies could not be loaded. Campagin ID is not set");
			
			//redirect back to the sms campaigns index page
			return $this->redirect()->toRoute("front-comms-sms-campaigns");
		}//end if
		
		//load data
		$objReplies = $this->getSmsCampaignRepliesModel()->fetchSmsCampaignReplies($id);
		
		return array(
			"objReplies" => $objReplies,	
			"sms_campaign_id" => $id,
		);
	}//end function
	
	public function viewReplyAction()
	{
		$id = $this->params()->fromRoute("sms_campaign_id", "");
		$reply_id = $this->params()->fromRoute("reply_id");
		
		if ($id == "" || $reply_id == "")
		{
			//set error
			$this->flashMessenger()->addErrorMessage("SMS Campaign Reply could not be loaded. Campagin ID or Message ID is not set");
				
			//redirect back to the sms campaigns index page
			return $this->redirect()->toRoute("front-comms-sms-campaigns");
		}//end if
		
		//load data
		$objReply = $this->getSmsCampaignRepliesModel()->fetchSmsCampaignReply($reply_id);
		
		return array(
			"sms_campaign_id" => $id,
			"objReply" => $objReply,	
		);
	}//end function
	
	/**
	 * Create an instance of the Sms Campaign Replies Model using the Service Manager
	 * @return \FrontCommsSmsCampaigns\Models\FrontCommsSmsCampaignsRepliesModel
	 */
	private function getSmsCampaignRepliesModel()
	{
		if (!$this->model_sms_campaign_replies)
		{
			$this->model_sms_campaign_replies = $this->getServiceLocator()->get("FrontCommsSmsCampaigns\Models\FrontCommsSmsCampaignsRepliesModel");
		}//end function
		
		return $this->model_sms_campaign_replies;
	}//end function
}//end class