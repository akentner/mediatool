<?php

namespace Mediatool\Config\AMQP\Queue;

class NextQueue extends AbstractQueue {

    /**
     * @var string
     */
    public $queue = 'mediatool.next';

    /**
     * @var bool
     */
    public $durable = true;

    /**
     * @var int
     */
    public $ttl = 3600000;


} 