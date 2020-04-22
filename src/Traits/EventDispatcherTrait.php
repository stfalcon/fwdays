<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * EventDispatcherTrait.
 */
trait EventDispatcherTrait
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @required
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
