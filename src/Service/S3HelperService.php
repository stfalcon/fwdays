<?php

declare(strict_types=1);

namespace App\Service;

use Aws\S3\S3Client;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

/**
 * S3HelperService.
 */
class S3HelperService
{
    public const CACHE_MAX_AGE = 2678400;

    private $s3Client;
    private $bucketName;
    private $cacheManager;
    private $filterManager;
    private $dataManager;

    /**
     * S3HelperService constructor.
     *
     * @param S3Client      $s3Client
     * @param string        $bucketName
     * @param CacheManager  $cacheManager
     * @param FilterManager $filterManager
     * @param DataManager   $dataManager
     */
    public function __construct(S3Client $s3Client, string $bucketName, $cacheManager, $filterManager, $dataManager)
    {
        $this->s3Client = $s3Client;
        $this->bucketName = $bucketName;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
        $this->dataManager = $dataManager;
    }

    /**
     * @param string      $fileName
     * @param string|null $newFilename
     * @param array       $meta
     * @param string      $privacy
     * @param bool        $withCache
     *
     * @return string file url
     */
    public function uploadFile(string $fileName, ?string $newFilename = null, array $meta = [], $privacy = 'public-read', bool $withCache = true): string
    {
        if (!$newFilename) {
            $newFilename = \basename($fileName);
        }

        if (!isset($meta['contentType'])) {
            $mimeTypeHandler = \finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = $mimeTypeHandler ? \finfo_file($mimeTypeHandler, $fileName) : null;
            $mimeTypeHandler ? \finfo_close($mimeTypeHandler) : false;

            $meta['ContentType'] = $mimeType;
            $meta['mimetype'] = $mimeType;
        }

        $meta['visibility'] = 'public';
        if ($withCache) {
            $meta['CacheControl'] = \sprintf('max-age=%s', self::CACHE_MAX_AGE);
        }
        $meta['ACL'] = $privacy;

        $uploadResult = $this->s3Client->upload($this->bucketName, $newFilename, \file_get_contents($fileName), $privacy, ['params' => $meta]);

        return $uploadResult->toArray()['ObjectURL'];
    }

    /**
     * @param string $filter
     * @param string $filename
     */
    public function prepareImageCache(string $filter, string $filename): void
    {
        if (empty($filename)) {
            return;
        }

        if (!$this->cacheManager->isStored($filename, $filter)) {
            $liipFilter = $this->dataManager->find($filter, $filename);
            $binary = $this->filterManager->applyFilter($liipFilter, $filter);
            $this->cacheManager->store($binary, $filename, $filter);
        }
    }
}
