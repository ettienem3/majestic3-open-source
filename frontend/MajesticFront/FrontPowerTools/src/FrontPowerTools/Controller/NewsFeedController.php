<?php
namespace FrontPowerTools\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use FrontUserLogin\Models\FrontUserSession;

class NewsFeedController extends AbstractActionController
{
	/**
	 * Container for the News Feed Model
	 * @var \FrontPowerTools\Models\FrontPowerToolsNewsFeedModel
	 */
	private $model_news_feed;

	public function indexAction()
	{
		$arr_messages = $this->getNewsFeedModel()->readMessages();

		return array(
			"arr_messages" => $arr_messages,
		);
	}//end function

	public function ajaxReadQueueAction()
	{
		$initial = $this->params()->fromQuery("i", 0);
		if ($initial == "1")
		{
			$initial = TRUE;
		}//end if

		$arr_messages = $this->getNewsFeedModel()->readMessages($initial);
		echo json_encode($arr_messages); exit;
	}//end function

	public function ajaxToggleFeedAction()
	{
		$i = $this->params()->fromQuery("i", 1);

		//load user session
		$objUser = FrontUserSession::isLoggedIn();
		if ($i == 1 || $i == 0)
		{
			$objUser->user_news_feed_activity = $i;
		} else {
			$objUser->user_news_feed_activity = (1 - $objUser->user_news_feed_activity);
		}//end if
		exit;
	}//end function

	/**
	 * Create an instance of the News Feed Model using the Service Manager
	 * @return \FrontPowerTools\Models\FrontPowerToolsNewsFeedModel
	 */
	private function getNewsFeedModel()
	{
		if (!$this->model_news_feed)
		{
			$this->model_news_feed = $this->getServiceLocator()->get("FrontPowerTools\Models\FrontPowerToolsNewsFeedModel");
		}//end if

		return $this->model_news_feed;
	}//end function
}//end class