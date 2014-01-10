<?php

namespace Mediatool\Cli\Command;

use Cilex\Command\Command;
use finfo;
use Mediatool\Cli\Provider\AmqpServiceProvider;
use Mediatool\Config\AMQP\Exchange\AbstractExchange;
use Mediatool\Config\AMQP\Exchange\MediatoolExchange;
use Mediatool\Handler\Analyser\AbstractAnalyser;
use Mediatool\Handler\Analyser\ImageAnalyser;
use Mediatool\Handler\Analyser\ZipAnalyser;
use Mediatool\Handler\FileInfo;
use Mediatool\Model\WorkflowJob;
use Mediatool\Services\AMQP;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use stdClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AnalyseCommand
 * @package MediatoolExchangeConfig\Cli\Command
 */
class AnalyseCommand extends Command
{
	const OPTION_QUIET = 'quiet';

	/**
     * @var stdClass
     */
    protected $config;

	/**
	 * @var AbstractExchange
	 */
	protected $exchangeConfig;


	/**
     *
     */
    protected function configure()
    {
	    $this->exchangeConfig = new MediatoolExchange();
        $this
            ->setName('analyse')
	        ->addOption(self::OPTION_QUIET, 'q', InputOption::VALUE_NONE, 'quiet, no output')
            ->addArgument('file', InputArgument::REQUIRED, 'file')
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

        $fileInfo = new FileInfo();
        $fileInfo->filename = $input->getArgument('file');
        $fileInfo->mimeType = $this->getMimeType($fileInfo->filename);

        $analyser = $this->getAnalyser($fileInfo);

	    $job = new WorkflowJob();

        if ($analyser) {
	        $job->status = WorkflowJob::STATUS_ANALYSED;
	        $job->next = WorkflowJob::NEXT_MOVE;
	        $job->data = $analyser->analyse();

	        $this->publish($job);
        }

	    if (!$input->getOption(self::OPTION_QUIET)) {
//		    $output->writeln("FileInfo: " . var_export($fileInfo, true) . "\n\n");
//		    $output->writeln("Analyser: " . var_export($analyser, true) . "\n\n");
		    $output->writeln("Job: " . var_export($job, true) . "\n\n");
		    $output->writeln('Memory usage/peak: ' . memory_get_usage(true) . '/' . memory_get_peak_usage(true));
	    }
    }

    /**
     * @param $filename
     * @return string
     */
    protected function getMimeType($filename, $withMimeEncoding = false)
    {
        if ($withMimeEncoding) {
            return trim(shell_exec("file -bi " . escapeshellarg($filename)));
        }
        return trim(shell_exec("file -b --mime-type " . escapeshellarg($filename)));
    }


    /**
     * @param FileInfo $fileInfo
     * @return AbstractAnalyser|null
     */
    protected function getAnalyser(FileInfo $fileInfo)
    {
        switch ($fileInfo->mimeType) {
            case 'application/zip':
                return new ZipAnalyser($fileInfo);
            case 'image/jpg':
            case 'image/jpeg':
            case 'image/png':
                return new ImageAnalyser($fileInfo);
        }
    }

	/**
	 * @param $job
	 */
	protected function publish(WorkflowJob $job) {
		/** @var $amqpService AMQP */
		$amqpService = $this->getContainer()[AmqpServiceProvider::CONTAINER_KEY];
		$routingKey = 'mediatool.workflow.next.' . strtolower($job->next);
		$amqpService->publish($job, $this->exchangeConfig, $routingKey);
	}


}
