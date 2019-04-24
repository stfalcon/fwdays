<?php

namespace Application\Bundle\DefaultBundle\Service;

use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Imagine\Cache\Resolver\FlysystemResolver;

/**
 * Class FlySystemStorage.
 *
 * Extend class for add cache.
 */
class MyFlySystemResolver extends FlysystemResolver
{
    private const CACHE_MAX_AGE = 2678400;

    /**
     * Stores the content of the given binary.
     *
     * @param BinaryInterface $binary The image binary to store
     * @param string          $path   The path where the original file is expected to be
     * @param string          $filter The name of the imagine filter in effect
     */
    public function store(BinaryInterface $binary, $path, $filter)
    {
        $this->flysystem->put(
            $this->getFilePath($path, $filter),
            $binary->getContent(),
            [
                'CacheControl' => sprintf('max-age=%s', self::CACHE_MAX_AGE),
                'visibility' => $this->visibility,
            ]
        );
    }
}
