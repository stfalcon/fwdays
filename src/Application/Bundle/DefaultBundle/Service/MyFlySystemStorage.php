<?php

namespace Application\Bundle\DefaultBundle\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Storage\FlysystemStorage;

/**
 * Class FlySystemStorage.
 *
 * Extend class for add cache.
 */
class MyFlySystemStorage extends FlysystemStorage
{
    private const CACHE_MAX_AGE = 2678400;

    /**
     * {@inheritdoc}
     */
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, ?string $dir, string $name): void
    {
        $fs = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;

        $stream = fopen($file->getRealPath(), 'r');
        $fs->writeStream($path, $stream, [
            'CacheControl' => sprintf('max-age=%s', self::CACHE_MAX_AGE),
            'mimetype' => $file->getMimeType(),
        ]);
    }
}
