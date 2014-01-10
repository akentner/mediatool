<?php

namespace Mediatool\Cli\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;

class ConfigServiceProvider implements ServiceProviderInterface
{
    const CONTAINER_KEY = 'config';

    public function register(Application $app)
    {
        $app[self::CONTAINER_KEY] = $app->share(
            function () use ($app) {
                if (!file_exists($app[self::CONTAINER_KEY . '.path'])) {
                    throw new \InvalidArgumentException(
                        $app[self::CONTAINER_KEY . '.path'] . ' is not a valid path to the '
                        .'configuration'
                    );
                }

                $fullpath = explode('.', $app[self::CONTAINER_KEY . '.path']);

                switch (strtolower(end($fullpath))) {
                    case 'php':
                        $result = include($app[self::CONTAINER_KEY . '.path']);
                        break;
                    case 'xml':
                        $result = simplexml_load_file($app[self::CONTAINER_KEY . '.path']);
                        break;
                    case 'json':

                        $result = json_decode(str_replace(
                            array('$APP_PATH$'),
                            array($app['APP_PATH']),
                            file_get_contents($app[self::CONTAINER_KEY . '.path'])
                        ));

                        if (null == $result) {
                            throw new \InvalidArgumentException(
                                'Unable to decode the configuration file: ' . $app[self::CONTAINER_KEY . '.path']
                            );
                        }
                        break;
                    default:
                        throw new \InvalidArgumentException(
                            'Unable to load configuration; the provided file extension was not recognized. '
                            .'Only yml, xml or json allowed'
                        );
                        break;
                }

                return $result;
            }
        );
    }
}
