<?php

namespace Mediatool\Services;


use Mediatool\Config\AMQP\Exchange\AbstractExchange;
use Mediatool\Config\AMQP\Queue\AbstractQueue;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AMQP
 * @package Mediatool\Services
 */
class AMQP {

	/**
	 * @var AMQPConnection
	 */
	protected $connection;

	/**
	 * @var AMQPChannel
	 */
	protected $channel;

	/**
	 * @var array
	 */
	protected $declaredExchanges = array();

	/**
	 * @var array
	 */
	protected $declaredQueues = array();

	/**
	 * @param string $host
	 * @param string $port
	 * @param string $user
	 * @param string $password
	 */
	public function __construct($host, $port, $user, $password)
	{
		$this->connection = new AMQPConnection($host, $port, $user, $password);
		$this->channel = $this->connection->channel();
	}

	/**
	 *
	 */
	public function __destruct() {
		$this->channel->close();
		$this->connection->close();
	}

	/**
	 * @param AbstractExchange $exchangeConfig
	 */
	public function declareExchange(AbstractExchange $exchangeConfig) {
        if (!in_array($exchangeConfig->exchange, $this->declaredExchanges)) {
			$this->channel->exchange_declare(
				$exchangeConfig->exchange,
				$exchangeConfig->type,
				$exchangeConfig->passive,
				$exchangeConfig->durable,
				$exchangeConfig->auto_delete,
				$exchangeConfig->internal,
				$exchangeConfig->nowait,
				$exchangeConfig->arguments,
				$exchangeConfig->ticket
			);
            $this->bindQueuesByExchangeConfig($exchangeConfig);
			$this->declaredExchanges[] = $exchangeConfig->exchange;
		}
	}

	/**
	 * @param AbstractQueue $queueConfig
	 */
	public function declareQueue(AbstractQueue $queueConfig) {
		if (!in_array($queueConfig->queue, $this->declaredQueues)) {
			$this->channel->queue_declare(
                $queueConfig->queue,
                $queueConfig->passive,
                $queueConfig->durable,
                $queueConfig->exclusive,
                $queueConfig->autoDelete,
                $queueConfig->nowait,
                $queueConfig->arguments,
                $queueConfig->ticket
			);
			$this->declaredQueues[] = $queueConfig->queue;
		}
	}

	/**
	 * @param                                                        $data
	 * @param \Mediatool\Config\AMQP\Exchange\AbstractExchange $exchangeConfig
	 * @param string                                                 $routingKey
	 * @param bool                                                   $mandantory
	 * @param bool                                                   $immediate
	 * @param null                                                   $ticket
	 */
	public function publish($data, AbstractExchange $exchangeConfig, $routingKey = '', $mandantory = false, $immediate = false, $ticket = null) {
		$this->declareExchange($exchangeConfig);

		$this->channel->basic_publish(new AMQPMessage(json_encode($data)), $exchangeConfig->exchange, $routingKey, $mandantory, $immediate, $ticket);
	}

    /**
     * @param AbstractQueue $queueConfig
     * @param string $ConsumerTag
     * @param bool $noLocal
     * @param bool $noAck
     * @param bool $exclusive
     * @param bool $nowait
     * @param $callback
     * @param null $ticket
     * @param array $arguments
     */
    public function consume(AbstractQueue $queueConfig, $ConsumerTag = '', $noLocal = false,
                            $noAck = false, $exclusive = false, $nowait = false, $callback,
                            $ticket = null, $arguments = array()
    )
    {
        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume(
            $queueConfig->queue,
            $ConsumerTag,
            $noLocal,
            $noAck,
            $exclusive,
            $nowait,
            $callback,
            $ticket,
            $arguments
        );

        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }


    public function bindQueuesByExchangeConfig(AbstractExchange $exchangeConfig)
    {
        foreach ($exchangeConfig->queues as $routingKey => $queueConfig) {
            $this->declareQueue($queueConfig);
            $this->channel->queue_bind($queueConfig->queue, $exchangeConfig->exchange, $routingKey);
        }
    }

}