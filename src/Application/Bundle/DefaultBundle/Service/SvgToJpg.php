<?php

namespace Application\Bundle\DefaultBundle\Service;

use Symfony\Component\HttpFoundation\File\File;

/**
 * Class SvgToJpg
 */
class SvgToJpg
{
    /**
     * @param File   $file
     * @param string $backgroundColor
     *
     * @return \Imagick
     */
    public function convert(File $file, $backgroundColor = '#F5F3EA')
    {
        $im = new \Imagick();
        $svg = file_get_contents($file);
        $im->setBackgroundColor(new \ImagickPixel($backgroundColor));
        $im->readImageBlob($svg);
        $im->setImageFormat("jpeg");

        return $im;
    }
}
