<?php

namespace Mediatool\Handler\Analyser;

/**
 * Class ImageAnalyser
 * @package MediatoolExchangeConfig\Handler\Analyser
 */
class ImageAnalyser extends AbstractAnalyser {

    /**
     * @var string
     */
    public $imageType = '';

    /**
     * @var array
     */
    protected $imageData;

    /**
     * @var array
     */
    public $exif = array();

    /**
     * @var array
     */
    private $imageTypes = array(
        IMAGETYPE_GIF     => 'IMAGETYPE_GIF',
        IMAGETYPE_JPEG    => 'IMAGETYPE_JPEG',
        IMAGETYPE_PNG     => 'IMAGETYPE_PNG',
        IMAGETYPE_SWF     => 'IMAGETYPE_SWF',
        IMAGETYPE_PSD     => 'IMAGETYPE_PSD',
        IMAGETYPE_BMP     => 'IMAGETYPE_BMP',
        IMAGETYPE_TIFF_II => 'IMAGETYPE_TIFF_II',
        IMAGETYPE_TIFF_MM => 'IMAGETYPE_TIFF_MM',
        IMAGETYPE_JPC     => 'IMAGETYPE_JPC',
        IMAGETYPE_JP2     => 'IMAGETYPE_JP2',
        IMAGETYPE_JPX     => 'IMAGETYPE_JPX',
        IMAGETYPE_JB2     => 'IMAGETYPE_JB2',
        IMAGETYPE_SWC     => 'IMAGETYPE_SWC',
        IMAGETYPE_IFF     => 'IMAGETYPE_IFF',
        IMAGETYPE_WBMP    => 'IMAGETYPE_WBMP',
        IMAGETYPE_XBM     => 'IMAGETYPE_XBM',
        IMAGETYPE_ICO     => 'IMAGETYPE_ICO',
    );

    /**
     *
     */
    public function analyse()
    {
        $this->fileInfo->imageType = $this->getImageType();
        $this->fileInfo->exif = $this->getInfoExif();
        $this->fileInfo->file = $this->getInfoFile();
        $this->fileInfo->computed = $this->getInfoComputed();
        $this->fileInfo->thumbnail = $this->getInfoThumbnail();
        $this->fileInfo->ifd0 = $this->getInfoIfd0();
        $this->fileInfo->gps = $this->getInfoGps();
        $this->fileInfo->interop = $this->getInfoInterop();
//        $this->fileInfo->data = array_keys($this->getImageData());
        return $this->fileInfo;
    }

    /**
     * @return null|string
     */
    protected function getImageType()
    {
        $imageType = exif_imagetype($this->fileInfo->filename);
        if (isset($this->imageTypes[$imageType])) {
            return $this->imageTypes[$imageType];
        }

        return null;
    }

    /**
     * @return array
     */
    protected function getInfoFile()
    {
        $data = $this->getImageData();
        return isset($data['FILE']) ? $data['FILE'] : null;
    }

    /**
     * @return array
     */
    protected function getInfoComputed()
    {
        $data = $this->getImageData();
        return isset($data['COMPUTED']) ? $data['COMPUTED'] : null;
    }

    /**
     * @return array
     */
    protected function getInfoAnyTag()
    {
        $data = $this->getImageData();
        return isset($data['ANY_TAG']) ? $data['ANY_TAG'] : null;
    }

    /**
     * @return array
     */
    protected function getInfoIfd0()
    {
        $data = $this->getImageData();
        return isset($data['IFD0']) ? $data['IFD0'] : null;
    }

    /**
     * @return array
     */
    protected function getInfoThumbnail()
    {
        $data = $this->getImageData();
        return isset($data['THUMBNAIL']) ? $data['THUMBNAIL'] : null;
    }

    /**
     * @return array
     */
    protected function getInfoComment()
    {
        $data = $this->getImageData();
        return isset($data['COMMENT']) ? $data['COMMENT'] : null;
    }

    /**
     * @return array
     */
    protected function getInfoExif()
    {
        $data = $this->getImageData();
        return isset($data['EXIF']) ? $data['EXIF'] : null;
    }

    /**
     * @return array
     */
    protected function getInfoGps()
    {
        $data = $this->getImageData();
        return isset($data['GPS']) ? $data['GPS'] : null;
    }

    /**
     * @return array
     */
    protected function getInfoInterop()
    {
        $data = $this->getImageData();
        return isset($data['INTEROP']) ? $data['INTEROP'] : null;
    }

    /**
     * @return array
     */
    protected function getInfoMakernote()
    {
        $data = $this->getImageData();
        return isset($data['MAKERNOTE']) ? $data['MAKERNOTE'] : null;
    }

    /**
     * @return array
     */
    protected function getImageData()
    {
        if (!$this->imageData) {
            $this->imageData = @exif_read_data($this->fileInfo->filename, null, true, true);
	        function remove_key(&$a) {
		        if(is_array($a)) {
					foreach(array_keys($a) as $k) {
						if (preg_match('/MakerNote|UndefinedTag:.+/',$k)) unset($a[$k]);
					}
			        array_walk($a, __FUNCTION__);
		        }
	        }
	        remove_key($this->imageData);
        }
        return $this->imageData;
    }
}