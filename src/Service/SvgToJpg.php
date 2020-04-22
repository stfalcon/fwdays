<?php

namespace App\Service;

use App\Traits\LoggerTrait;

/**
 * Class SvgToJpg.
 */
class SvgToJpg
{
    use LoggerTrait;

    private $xResolution = 500;
    private $yResolution = 500;

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
     * @param string $fileName
     * @param string $backgroundColor
     *
     * @return \Imagick
     */
    public function convert($fileName, $backgroundColor = '#F5F3EA')
    {
        $im = new \Imagick();
        try {
            $im->setBackgroundColor(new \ImagickPixel($backgroundColor));
            $im->setResolution($this->xResolution, $this->yResolution);
            $svg = \file_get_contents($fileName);
            if (false !== $svg) {
                $im->readImageBlob($svg);
            }
            $im->setImageFormat('jpeg');
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), [$e]);
        }

        return $im;
    }
}
