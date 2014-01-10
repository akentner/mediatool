<?php

namespace Mediatool\Model;

use Mediatool\Model\Data\AnalysedData;
use stdClass;

class WorkflowJob extends stdClass{

	const STATUS_NEW_FILE = 'NEW_FILE';
	const STATUS_ANALYSED = 'ANALYSED';
	const STATUS_MOVED    = 'MOVED';

	const NEXT_ANALYSE = 'ANALYSE';
	const NEXT_MOVE    = 'MOVE';

	/**
	 * @var string
	 */
	public $status = '';

	/**
	 * @var string
	 */
	public $next = '';

	/**
	 * @var stdClass
	 */
	public $data;



    public function __construct()
    {
        $this->data = new AnalysedData();
    }
} 