<?php

use Cilex\Application;
use Mediatool\Cli\Command\IncronCommand;
use PhpAmqpLib\Connection\AMQPConnection;

require __DIR__ .'/../vendor/autoload.php';


$app = new Application('Mediatool');

$app['APP_PATH'] = realpath(__DIR__ . '/..');

$app->register(new ConfigServiceProvider(),
    array(ConfigServiceProvider::CONTAINER_KEY . '.path' => $app['APP_PATH'] . '/conf/config.json')
);
$app->register(new AmqpServiceProvider());
$app->command(new \Cilex\Command\GreetCommand());
$app->run();
