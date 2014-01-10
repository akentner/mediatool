<?php

namespace Mediatool\Cli\Command;

use Cilex\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Incron
 * @package Command
 */
class DebugCommand extends Command
{
    /**
     * @var stdClass
     */
    protected $config;

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('debug')
            ->addArgument('what', InputArgument::REQUIRED, 'debug what')
            ->addArgument('key', InputArgument::OPTIONAL)
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

        switch ($input->getArgument('what')) {
            case 'config':
                var_dump($this->config);
                break;
            case 'extensions':
                $output->writeln(get_loaded_extensions());
                break;
            case 'server':
                $key = $input->getArgument('key');
                if ($key) {
                    if (isset($_SERVER[$key])) {
                        var_dump($_SERVER[$key]);
                    } else {
                        $output->writeln($key . ' does not exist');
                    }
                } else {
                    var_dump($_SERVER);
                }
            default:
        }
    }
}
