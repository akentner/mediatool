<?php

namespace Mediatool\Config\AMQP\Queue;

abstract class AbstractQueue {

    /**
     * @var string
     */
    public $queue = '';

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
    public $exclusive = false;

    /**
     * @var bool
     */
    public $autoDelete = false;

    /**
     * @var int
     */
    public $nowait = false;

    /**
     * @var int
     */
    public $ttl = null;

    /**
     * @var int
     */
    public $autoExpire = null;

    /**
     * @var int
     */
    public $maxLength = null;

    /**
     * @var string
     */
    public $deadLetterExchange = null;

    /**
     * @var string
     */
    public $deadLetterRoutineKey = null;

    /**
     * @var null
     */
    public $arguments = null;

    /**
     * @var null
     */
    public $ticket = null;

} 