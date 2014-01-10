<?php

namespace Mediatool\Cli\Provider;


use Cilex\Application;
use Cilex\ServiceProviderInterface;
use Mediatool\Incron\Incrontab;

class IncrontabServiceProvider implements ServiceProviderInterface {

    const CONTAINER_KEY = 'service.incron';

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
                return new Incrontab($app[ConfigServiceProvider::CONTAINER_KEY]->$key);
            }
        );
    }


} 