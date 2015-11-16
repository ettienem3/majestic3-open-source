<?php
namespace FrontPowerTools\Models;

use FrontCore\Adapters\AbstractCoreMessageQueueAdapter;
use PhpAmqpLib\Wire\AMQPTable;
use FrontUserLogin\Models\FrontUserSession;

class FrontPowerToolsNewsFeedModel extends AbstractCoreMessageQueueAdapter
{
	private $objChannel;
	private $arr_messages = array();

	public function readMessages($initial = FALSE)
	{
		$arr_config = $this->getServiceLocator()->get("config")["profile_config"];

		//load user session
		$objUser = FrontUserSession::isLoggedIn();

		if (!isset($arr_config["news_feed_credentials"]) || !isset($objUser->profile->plugins->newsfeed_exchange))
		{
			//return emtpy object for javascript
			return (object) array(json_encode(array("id" => "")));
		}//end if

		if ($initial === TRUE)
		{
			if (isset($_SESSION["user_news_feed"]))
			{
				$this->arr_messages = $_SESSION["user_news_feed"];
				if (count($this->arr_messages) > 2)
				{
					return array_reverse($this->arr_messages);
				}//end if
			} else {
				$this->arr_messages[] = json_encode((object) array("id" => ""));
			}//end if
		}//end if

		$this->objChannel = $this->getConnectionChannel($arr_config["news_feed_credentials"]);
		$exchange = $objUser->profile->plugins->newsfeed_exchange;
		$consumer_tag = 'consumer' . getmypid();

		//set queue identifier
		if (!isset($objUser->user_news_feed_queue) || $objUser->user_news_feed_queue == "")
		{
			$objUser->user_news_feed_queue = sha1(md5(microtime(TRUE) . rand(time(), 249348748) . $consumer_tag));
			$queue = $objUser->user_news_feed_queue;
		} else {
			$queue = $objUser->user_news_feed_queue;
		}//end if

		/*
		 name: $queue // should be unique in fanout exchange.
		 passive: false // don't check if a queue with the same name exists
		 durable: false // the queue will not survive server restarts
		 exclusive: false // the queue might be accessed by other channels
		 auto_delete: true //the queue will be deleted once the channel is closed.
		*/
		$this->objChannel->queue_declare($queue, false, false, false, true, new AMQPTable(array("x-message-ttl" => ((60 * 5) * 100))));
		$this->objChannel->queue_bind($queue, $exchange);

		for ($i = 0; $i < 30; $i++)
		{
			$msg = $this->objChannel->basic_get($queue);

			if (is_object($msg))
			{
				array_unshift($this->arr_messages, $msg->body);
				$this->objChannel->basic_ack($msg->delivery_info['delivery_tag']);
			}//end if
		}//end for

		//save to session
		if (count($this->arr_messages) > 0 && $initial !== TRUE)
		{
			if (!isset($_SESSION["user_news_feed"]) || !is_array($_SESSION["user_news_feed"]))
			{
				$_SESSION["user_news_feed"] = array();
				$arr_messages = array();
			} else {
				$arr_messages = $_SESSION["user_news_feed"];
			}//end if

			$arr_total = array_merge($this->arr_messages, $arr_messages);

			if (count($arr_total) > 50)
			{
				$_SESSION["user_news_feed"] = $this->arr_messages = array_slice($arr_total, 0, 50);
			} else {
				$_SESSION["user_news_feed"] = $arr_total;
			}//end if
		}//end if

		if (count($this->arr_messages) == 0 && $initial !== TRUE && isset($_SESSION["user_news_feed"]) && count($_SESSION["user_news_feed"]) > 0)
		{
			$this->arr_messages = array_reverse($_SESSION["user_news_feed"]);
		}//end if

		//close connection
		$this->close();

		//reverse the order, the information is prended in view
		return ($this->arr_messages);
	}//end function
}//end class