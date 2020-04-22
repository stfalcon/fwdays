<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Component\Routing\RouterInterface;

/**
 * RouterTrait.
 */
trait RouterTrait
{
    /** @var RouterInterface */
    protected $router;

    /**
     * @param RouterInterface $router
     *
     * @required
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }
}
