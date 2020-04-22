<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * RequestStackTrait.
 */
trait RequestStackTrait
{
    /** @var RequestStack */
    protected $requestStack;

    /**
     * @param RequestStack $requestStack
     *
     * @required
     */
    public function setRequestStack(RequestStack $requestStack): void
    {
        $this->requestStack = $requestStack;
    }
}
