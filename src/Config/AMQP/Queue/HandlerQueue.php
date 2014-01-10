<?php

namespace Mediatool\Config\AMQP\Queue;

class HandlerQueue extends AbstractQueue {

    /**
     * @var string
     */
    public $queue = 'mediatool.handler';

    /**
     * @var bool
     */
    public $durable = true;

    /**
     * @var int
     */
    public $ttl = 3600000;


} 