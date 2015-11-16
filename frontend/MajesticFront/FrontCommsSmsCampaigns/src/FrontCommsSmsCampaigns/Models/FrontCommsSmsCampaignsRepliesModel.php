<?php
namespace FrontCommsSmsCampaigns\Models;

use FrontCore\Adapters\AbstractCoreAdapter;

class FrontCommsSmsCampaignsRepliesModel extends AbstractCoreAdapter
{
	public function fetchSmsCampaignReplies($sms_campaign_id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/info/sms-campaign-replies");
		
		//execute
		$objReplies = $objApiRequest->performGETRequest(array("dbc-fk-sms-campaign-id" => $sms_campaign_id))->getBody();
	
		if (is_object($objReplies->data))
		{
			//create entities
			foreach($objReplies->data as $objReply)
			{
				$objReplyEntity = $this->getServiceLocator()->get("FrontCommsSmsCampaigns\Entities\FrontCommsSmsCampaignReplyEntity");
				$objReplyEntity->set($objReply);
				$arr[] = $objReplyEntity;
			}//end function
			
			return (object) $arr;
		} else {
			return (object) array();
		}//end if
	}//end function
	
	public function fetchSmsCampaignReply($id)
	{
		//create the request object
		$objApiRequest = $this->getApiRequestModel();
		
		//setup the object and specify the action
		$objApiRequest->setApiAction("comms/info/sms-campaign-replies/$id");
		
		//execute the request
		$objReply = $objApiRequest->performGETRequest()->getBody();
		
		$objReplyEntity = $this->getServiceLocator()->get("FrontCommsSmsCampaigns\Entities\FrontCommsSmsCampaignReplyEntity");
		$objReplyEntity->set($objReply->data);
		
		return $objReplyEntity;
	}//end function
}//end class