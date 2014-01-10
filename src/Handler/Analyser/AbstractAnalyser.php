<?php

namespace Mediatool\Handler\Analyser;
use Mediatool\Handler\FileInfo;

/**
 * Class AbstractAnalyser
 * @package MediatoolExchangeConfig\Handler\Analyser
 */
abstract class AbstractAnalyser {

    /**
     * @var FileInfo
     */
    protected $fileInfo;

    /**
     * @param \Mediatool\Handler\FileInfo $fileInfo
     */
    public function __construct(FileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
    }

    /**
     * @return FileInfo
     */
    abstract public function analyse();

} 