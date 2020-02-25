<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * SessionTrait.
 */
trait SessionTrait
{
    /** @var Session */
    protected $session;

    /**
     * @param Session $session
     *
     * @required
     */
    public function setSession(Session $session): void
    {
        $this->session = $session;
    }
}
