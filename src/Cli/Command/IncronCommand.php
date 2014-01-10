<?php

namespace Mediatool\Cli\Command;

use Cilex\Command\Command;
use Mediatool\Cli\Provider\ConfigServiceProvider;
use Mediatool\Cli\Provider\AmqpServiceProvider;
use Mediatool\Cli\Provider\IncrontabServiceProvider;
use Mediatool\Config\AMQP\Exchange\MediatoolExchange;
use Mediatool\Incron\Incrontab;
use Mediatool\Model\WorkflowJob;
use Mediatool\Services\AMQP;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Incron
 * @package Command
 */
class IncronCommand extends Command {
	const OPTION_EVENT = 'event';
	const OPTION_ADD = 'add';
	const OPTION_REMOVE = 'remove';
	const OPTION_LIST = 'list';
	const OPTION_QUIET = 'quiet';
	const ARG_PATH = 'path';
	/**
	 * @var stdClass
	 */
	protected $config;
	/**
	 * @var Incrontab
	 */
	protected $incrontab;

	/**
	 * @var AbstractExchangeConfig
	 */
	protected $exchangeConfig;

	/**
	 *
	 */
	protected function configure() {
		$this->exchangeConfig = new MediatoolExchange();
		$this
			->setName('incron')
			->setDescription('Greet someone')
			->addOption(self::OPTION_ADD, 'a', InputOption::VALUE_NONE, 'add entry in incrontab, optional with path')
			->addOption(self::OPTION_REMOVE, 'r', InputOption::VALUE_NONE, 'remove entry in incrontab, optional with path')
			->addOption(self::OPTION_EVENT, 'e', InputOption::VALUE_REQUIRED, 'execute command for given event')
			->addOption(self::OPTION_LIST, 'l', InputOption::VALUE_NONE, 'list entries in crontab')
			->addOption(self::OPTION_QUIET, 'q', InputOption::VALUE_NONE, 'quiet, no output')
			->addArgument(self::ARG_PATH, InputArgument::OPTIONAL, 'path (mandantory for command option)');
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$key = IncrontabServiceProvider::CONTAINER_KEY;
		$this->config = $this->getContainer()[ConfigServiceProvider::CONTAINER_KEY]->$key;
		$this->incrontab = $this->getContainer()[IncrontabServiceProvider::CONTAINER_KEY];

		switch (true) {
			case (bool)$input->getOption(self::OPTION_EVENT):
				$this->handleEvent($input, $output);
				break;
			case (bool)$input->getOption(self::OPTION_ADD):
				$this->addEntry($input, $output);
				break;
			case (bool)$input->getOption(self::OPTION_REMOVE):
				$this->removeEntry($input, $output);
				break;
			case (bool)$input->getOption(self::OPTION_LIST):
			default:
				$this->listEntries($input, $output);
		}
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return string
	 */
	protected function handleEvent(InputInterface $input, OutputInterface $output) {
		$file = $input->getArgument(self::ARG_PATH);
		if (!$file) {
			throw new \RuntimeException('argument "' . self::ARG_PATH . '" not given');
		}
		$option = $input->getOption(self::OPTION_EVENT);

		switch ($option) {
			case Incrontab::IN_CREATE . ',' . Incrontab::IN_ISDIR:
			case Incrontab::IN_DELETE . ',' . Incrontab::IN_ISDIR:
				$this->incrontab->generate();
				if (!$input->getOption(self::OPTION_QUIET)) {
					$output->writeln($this->incrontab->getIncrontab());
				}
				break;
			case Incrontab::IN_CLOSE_WRITE:
			case Incrontab::IN_MOVED_TO:

				$break = false;
				foreach ($this->config->ignore as $expr) {
					if (strpos(basename($file), $expr) !== false) $break = true;
				}
				if ($break) {
					break;
				}

				$this->setFileAttribs($file);

				$job = new WorkflowJob();
				$job->status = WorkflowJob::STATUS_NEW_FILE;
				$job->next = WorkflowJob::NEXT_ANALYSE;


                $job->data->file = $file;

                $this->publish($job);

				if (!$input->getOption(self::OPTION_QUIET)) {
					$output->writeln('handle ' . $file);
				}
				break;
		}
	}

	/**
	 * @param $file
	 */
	protected function setFileAttribs($file) {
		$config = $this->getContainer()[ConfigServiceProvider::CONTAINER_KEY]->general;
		chown($file, $config->user);
		chgrp($file, $config->group);
		chmod($file, 0755);
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

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return string
	 */
	protected function addEntry(InputInterface $input, OutputInterface $output) {
		$this->incrontab->generate();
		$output->writeln($this->incrontab->getIncrontab());
	}

	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return string
	 */
	protected function removeEntry(InputInterface $input, OutputInterface $output) {
		$this->incrontab->generate();
		$output->writeln($this->incrontab->getIncrontab());
	}

	protected function listEntries(InputInterface $input, OutputInterface $output) {
		$output->writeln('Config Paths:');
		$output->writeln($this->config->paths);
		$output->writeln('');
		$output->writeln('Incrontab Paths');
		$output->writeln($this->incrontab->getIncrontab());
	}

}
