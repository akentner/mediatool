<?php

namespace Mediatool\Config\AMQP\Exchange;
use Mediatool\Config\AMQP\Queue\AbstractQueue;

/**
 * Class AbstractExchangeConfig
 * @package MediatoolExchangeConfig\Config\AMQP\Exchange
 */
abstract class AbstractExchange {

	const EXCHANGE_TYPE_FANOUT  = 'fanout';
	const EXCHANGE_TYPE_DIRECT  = 'direct';
	const EXCHANGE_TYPE_TOPIC   = 'topic';
	const EXCHANGE_TYPE_HEADERS = 'headers';

	/**
	 * @var string
	 */
	public $exchange = '';

	/**
	 * @var string
	 */
	public $type = '';

	/**
	 * @var bool
	 */
	public $passive = false;

	/**
	 * @var bool
	 */
	public $durable = true;

	/**
	 * @var bool
	 */
	public $auto_delete = false;

	/**
	 * @var bool
	 */
	public $internal = false;

	/**
	 * @var bool
	 */
	public $nowait = false;

	/**
	 * @var null
	 */
	public $arguments = null;

	/**
	 * @var null
	 */
	public $ticket = null;

    /**
     * @var AbstractQueue[]
     */
    public $queues = array();
}