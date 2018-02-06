<?php

namespace Application\Bundle\DefaultBundle\Service;

use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class SvgToJpg
 */
class SvgToJpg
{
    /** @var Logger */
    private $logger;

    /**
     * SvgToJpg constructor.
     *
     * @param Logger $logger
     */
    public function __construct($logger)
    {
        $this->logger = $logger;
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
            $im->readImageBlob($svg);
            $im->setImageFormat("jpeg");
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), [$e]);
        }

        return $im;
    }
}
