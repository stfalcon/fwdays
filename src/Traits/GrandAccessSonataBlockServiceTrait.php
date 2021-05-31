<?php

declare(strict_types=1);

namespace App\Traits;

use App\Service\SonataBlockAccess\GrandAccessSonataBlockService;

/**
 * GrandAccessSonataBlockServiceTrait.
 */
trait GrandAccessSonataBlockServiceTrait
{
    /** @var GrandAccessSonataBlockService */
    protected $accessSonataBlockService;

    /**
     * @param GrandAccessSonataBlockService $accessSonataBlockService
     *
     * @required
     */
    public function setUserService(GrandAccessSonataBlockService $accessSonataBlockService): void
    {
        $this->accessSonataBlockService = $accessSonataBlockService;
    }
}
