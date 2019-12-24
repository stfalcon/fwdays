<?php

declare(strict_types=1);

namespace App\Traits;

use App\Service\LocalsRequiredService;

/**
 * LocalsRequiredTrait.
 */
trait LocalsRequiredTrait
{
    /** @var LocalsRequiredService */
    protected $localsRequired;

    /**
     * @param LocalsRequiredService $localsRequired
     *
     * @required
     */
    public function setLocalsRequired(LocalsRequiredService $localsRequired): void
    {
        $this->localsRequired = $localsRequired;
    }
}
