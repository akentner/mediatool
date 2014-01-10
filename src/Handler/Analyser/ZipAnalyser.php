<?php

namespace Mediatool\Handler\Analyser;

use Mediatool\Handler\FileInfo;

class ZipAnalyser extends AbstractAnalyser {

    /**
     * @return FileInfo
     */
    public function analyse()
    {
        return $this->fileInfo;
    }

} 