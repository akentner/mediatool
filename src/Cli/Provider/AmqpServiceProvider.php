<?php

namespace Mediatool\Cli\Provider;


use Cilex\Application;
use Cilex\ServiceProviderInterface;
use Mediatool\Incron\Incrontab;
use Mediatool\Services\AMQP;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpServiceProvider implements ServiceProviderInterface {


	const EXCHANGE = 'mediatool';

    const CONTAINER_KEY = 'service.amqp';

    /**
     * Registers services on the given app.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app[self::CONTAINER_KEY] = $app->share(
            function () use ($app) {
                $key = self::CONTAINER_KEY;
                $config = $app[ConfigServiceProvider::CONTAINER_KEY]->$key;
                return new AMQP($config->host, $config->port, $config->user, $config->password);
            }
        );
    }


} 