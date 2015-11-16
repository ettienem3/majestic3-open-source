<?php
namespace FrontCore\Adapters;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Exception\AMQPProtocolConnectionException;
use PhpAmqpLib\Exception\AMQPRuntimeException;

abstract class AbstractCoreMessageQueueAdapter extends AbstractCoreAdapter
{
	/**
	 * Container for the Queueing service Conncetion
	 * @var \PhpAmqpLib\Connection\AMQPConnection
	 */
	protected $queueConnection;

	/**
	 * Container for the Connection Channel
	 * @var \PhpAmqpLib\Connection\AMQPConnection
	 */
	protected $connectionChannel;

	/**
	 * Use explicit SSL connection
	 * By default, a SSL connection will be attempted, where it fails,
	 * the connection will be attempted over a normal wire.
	 * Where this flag is set to true, only SSL connections will be used
	 * @var bool
	 */
	private $force_ssl_connection;

	/**
	 * Set SSL preference
	 * By default, a SSL connection will be attempted, where it fails,
	 * the connection will be attempted over a normal wire.
	 * Where this flag is set to true, only SSL connections will be used
	 * @param string $flag
	 */
	protected function setSSlExplicit($flag = TRUE)
	{
		$this->force_ssl_connection = $flag;
	}//end function

	/**
	 * Create channel
	 * @param array $arr_connection_params
	 * @return \PhpAmqpLib\Connection\AMQPConnection
	 */
	protected function getConnectionChannel($arr_connection_params)
	{
		if (!$this->connectionChannel)
		{
			$this->connectionChannel = $this->createConnection($arr_connection_params)->channel();
		}//end if

		return $this->connectionChannel;
	}//end function

	/**
	 * Create connection to queue service
	 * @return \PhpAmqpLib\Connection\AMQPConnection
	 */
	private function createConnection($arr_params)
	{
		if (!isset($arr_params["vhost"]) || $arr_params["vhost"] == "")
		{
			$arr_params["vhost"] = "/";
		}//end if

		if (isset($arr_params["force_ssl"]) && ($arr_params["force_ssl"] === TRUE || $arr_params["force_ssl"] == 1))
		{
			$this->force_ssl_connection = TRUE;
		}//end if

		if (!$this->queueConnection)
		{
			try {
				$this->queueConnection = new AMQPSSLConnection($arr_params['host'], $arr_params['port'], $arr_params['user'], $arr_params['password'], $arr_params["vhost"], array("verify_peer" => false, 'cafile' => '/root/testca/cacert.pem', 'local_cert' => '/root/testca/client/key-cert.pem'));
			} catch (AMQPRuntimeException $e) {
				if ($this->force_ssl_connection === TRUE)
				{
					throw new \Exception(__CLASS__ . " : " . __LINE__ . " : SQS Messaging could not be initialized. SSL is requested but the connection failed with message:'" . $e->getMessage() . "'", 500);
				}//end if

				//use default port
				$arr_params['port'] = 5672; //@TODO allow setting for this
				$this->queueConnection = new AMQPConnection($arr_params['host'], $arr_params['port'], $arr_params['user'], $arr_params['password'], $arr_params["vhost"]);
			}//end catch
		}//end if

		return $this->queueConnection;
	}//end function
	
	public function close()
	{
		$this->queueConnection->close();
		$this->connectionChannel->close();
	}//end function
}//end class