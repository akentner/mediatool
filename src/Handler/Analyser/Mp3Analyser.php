<?php

namespace Mediatool\Handler\Analyser;

/**
 * Class ImageAnalyser
 * @package MediatoolExchangeConfig\Handler\Analyser
 */
class Mp3Analyser extends AbstractAnalyser {

    /**
     *
     */
    public function analyse()
    {
        $this->fileInfo->imageType = $this->getImageType();
        $this->fileInfo->exif = $this->getExif();

        return $this->fileInfo;
    }

}