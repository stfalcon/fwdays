<?php

declare(strict_types=1);

namespace App\Traits;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

/**
 * LiipImagineTrait.
 */
trait LiipImagineTrait
{
    /** @var CacheManager */
    protected $liipImagineCacheManager;

    /** @var FilterManager */
    protected $liipImagineFilterManager;

    /** @var DataManager */
    protected $liipImagineDataManager;

    /**
     * @param CacheManager $liipImagineCacheManager
     *
     * @required
     */
    public function setCacheManager(CacheManager $liipImagineCacheManager): void
    {
        $this->liipImagineCacheManager = $liipImagineCacheManager;
    }

    /**
     * @param FilterManager $liipImagineFilterManager
     *
     * @required
     */
    public function setFilterManager(FilterManager $liipImagineFilterManager): void
    {
        $this->liipImagineFilterManager = $liipImagineFilterManager;
    }

    /**
     * @param DataManager $liipImagineDataManager
     *
     * @required
     */
    public function setDataManager(DataManager $liipImagineDataManager): void
    {
        $this->liipImagineDataManager = $liipImagineDataManager;
    }
}
