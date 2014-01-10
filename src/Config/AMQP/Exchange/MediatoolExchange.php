<?php
/**
 * Created by PhpStorm.
 * User: ak
 * Date: 07.01.14
 * Time: 15:18
 */

namespace Mediatool\Config\AMQP\Exchange;


use Mediatool\Config\AMQP\Queue\NextQueue;

class MediatoolExchange extends AbstractExchange {

	/**
	 * @var string
	 */
	public $exchange = 'mediatool';

    /**
     * @var string
     */
    public $type = self::EXCHANGE_TYPE_TOPIC;

    /**
     *
     */
    public function __construct()
    {
        $this->queues['mediatool.workflow.next.*'] = new NextQueue();
    }


} 