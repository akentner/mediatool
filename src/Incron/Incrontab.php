<?php

namespace Mediatool\Incron;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Incrontab {

    const EVENTS = 'IN_CREATE,IN_DELETE,IN_CLOSE_WRITE,IN_MOVED_TO,IN_ISDIR';

    const IN_CREATE      = 'IN_CREATE';
    const IN_DELETE      = 'IN_DELETE';
    const IN_ISDIR       = 'IN_ISDIR';
    const IN_CLOSE_WRITE = 'IN_CLOSE_WRITE';
	const IN_MOVED_TO    = 'IN_MOVED_TO';

    /**
     * @var string
     */
    protected $incrontab = '';

    /**
     * @var string
     */
    protected $lockfile = '';

    /**
     * @var string[]
     */
    protected $configPaths = array();

    /**
     * @var string
     */
    protected $bin = '';

    /**
     * @param \stdClass $config
     */
    public function __construct(\stdClass $config)
    {
        $this->incrontab = $config->incrontab;
        $this->lockfile = $config->lockfile;
        $this->configPaths = $config->paths;
        $this->bin = $config->bin;
    }

    /**
     *
     */
    public function generate()
    {
        if (!$this->isLocked()) {
            usleep(1337);
            $data = '';
            $this->lock();

            $paths = array();

            foreach ($this->configPaths as $rootPath) {
                foreach($this->getRecursiveDirs($rootPath) as $path) {
	                chown($path, 'data');
	                chgrp($path, 'data');
	                chmod($path, 0755);

                    $paths[] = $path;
                }
            }
            foreach (array_unique($paths) as $path) {
                $data .= sprintf('%s %s %s incron -q -e $%% $@/$# ' . "\n",
                    addcslashes($path, ' '),
                    self::EVENTS,
                    $this->bin
                );
            }

            file_put_contents($this->incrontab, $data);
            $this->unlock();
        }
    }


    /**
     *
     */
    protected function getRecursiveDirs($rootPath)
    {
        $paths = array($rootPath);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );

        foreach ($iterator as $path => $fileinfo) {
            /** @var $fileinfo splFileInfo */
            if ($fileinfo->isDir() && strpos($path, '/.') === false) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * @return array
     */
    public function getIncrontab()
    {
        return preg_split('/\R/', file_get_contents($this->incrontab));
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        $paths = array();

        foreach ($this->getIncrontab() as $line) {
            if (preg_match('/(.+) ' . self::EVENTS . '.*/', $line, $match)) {
                $paths[] = $match[1];
            }
        }

        return $paths;
    }

    /**
     * set lockfile
     */
    public function lock()
    {
        touch($this->lockfile);
    }

    /**
     * unset lockfile
     */
    public function unlock()
    {
        unlink($this->lockfile);
    }

    /**
     * @return bool lockfile exists
     */
    public function isLocked()
    {
        return file_exists($this->lockfile);
    }
}