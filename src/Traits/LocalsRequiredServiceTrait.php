<?php

declare(strict_types=1);

namespace App\Traits;

use App\Service\LocalsRequiredService;

/**
 * LocalsRequiredServiceTrait.
 */
trait LocalsRequiredServiceTrait
{
    /** @var LocalsRequiredService */
    protected $localsRequiredService;

    /**
     * @param LocalsRequiredService $localsRequiredService
     *
     * @required
     */
    public function setLocalsRequiredService(LocalsRequiredService $localsRequiredService): void
    {
        $this->localsRequiredService = $localsRequiredService;
    }
}
