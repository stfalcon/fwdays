<?php

namespace App\Service;

use League\Flysystem\MountManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;
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
     * @param PropertyMappingFactory $factory
     * @param MountManager           $registry
     */
    public function __construct(PropertyMappingFactory $factory, MountManager $registry)
    {
        parent::__construct($factory, $registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function doUpload(PropertyMapping $mapping, UploadedFile $file, ?string $dir, string $name): void
    {
        $fs = $this->getFilesystem($mapping);
        $path = !empty($dir) ? $dir.'/'.$name : $name;
        $realPath = $file->getRealPath();
        if ($realPath) {
            $stream = \fopen($realPath, 'r');
            if ($stream) {
                $fs->writeStream($path, $stream, [
                    'CacheControl' => sprintf('max-age=%s', self::CACHE_MAX_AGE),
                    'mimetype' => $file->getMimeType(),
                ]);
            }
        }
    }
}
