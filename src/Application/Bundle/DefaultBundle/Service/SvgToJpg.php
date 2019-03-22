<?php

namespace Application\Bundle\DefaultBundle\Service;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class SvgToJpg.
 */
class SvgToJpg
{
    /** @var Logger */
    private $logger;

    /**
     * @var string
     */
    private $kernelPath;

    /**
     * @var string
     */
    private $uploadDir;

    private $xResolution = 500;
    private $yResolution = 500;

    /**
     * SvgToJpg constructor.
     *
     * @param Logger $logger
     * @param string $kernelPath
     * @param string $uploadDir
     */
    public function __construct($logger, $kernelPath, $uploadDir)
    {
        $this->logger = $logger;
        $this->kernelPath = $kernelPath;
        $this->uploadDir = $uploadDir;
    }

    /**
     * @param int $x
     * @param int $y
     */
    public function setResolution($x, $y)
    {
        $this->xResolution = $x;
        $this->yResolution = $y;
    }

    /**
     * @param File   $file
     * @param string $backgroundColor
     *
     * @return \Imagick
     */
    public function convert(File $file, $backgroundColor = '#F5F3EA')
    {
        $im = new \Imagick();
        try {
            $svg = file_get_contents($file);
            $im->setBackgroundColor(new \ImagickPixel($backgroundColor));
            $im->setResolution($this->xResolution, $this->yResolution);

            $im->readImageBlob($svg);
            $im->setImageFormat('jpeg');
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), [$e]);
        }

        return $im;
    }
}
