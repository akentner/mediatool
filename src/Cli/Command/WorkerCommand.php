<?php

namespace Mediatool\Cli\Command;

use Cilex\Command\Command;
use Mediatool\Cli\Provider\AmqpServiceProvider;
use Mediatool\Config\AMQP\Exchange\MediatoolExchange;
use Mediatool\Config\AMQP\Queue\AbstractQueue;
use Mediatool\Config\AMQP\Queue\HandlerQueue;
use Mediatool\Config\AMQP\Queue\NextQueue;
use Mediatool\Services\AMQP;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Incron
 * @package Command
 */
class WorkerCommand extends Command
{
    /**
     * @var stdClass
     */
    protected $config;

    /**
     * @var AbstractQueue
     */
    protected $queueConfig;

    /**
     *
     */
    protected function configure()
    {
        $this->queueConfig = new NextQueue();
        $this
            ->setName('worker')
            ->addArgument('id', InputArgument::REQUIRED)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->config = $this->getContainer()['config'];

        /** @var $amqpService AMQP */
        $amqpService = $this->getContainer()[AmqpServiceProvider::CONTAINER_KEY];
        $amqpService->declareQueue($this->queueConfig);

        echo ' [*] Waiting for logs. To exit press CTRL+C', "\n";
        echo '     - Mem:' . memory_get_usage(true) . ' Peak: ' . memory_get_peak_usage() .  "\n";

        $callback = function(AMQPMessage $msg) use ($output) {
            $output->writeln(" [x] Received " . $msg->body. "\n");
            echo '     - Mem:' . memory_get_usage(true) . ' Peak: ' . memory_get_peak_usage() .  "\n";

            $output->writeln($msg->get_properties());
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $amqpService->consume($this->queueConfig,'#',false, false, false, false, $callback);
    }
}
