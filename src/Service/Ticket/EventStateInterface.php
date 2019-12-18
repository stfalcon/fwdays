<?php

declare(strict_types=1);

namespace App\Service\Ticket;

use App\Model\EventStateData;

/**
 * EventStateInterface.
 */
interface EventStateInterface
{
    /**
     * @return bool
     */
    public function isDiv(): bool;

    /**
     * @param EventStateData $eventStateData
     *
     * @return string
     */
    public function getCaption(EventStateData $eventStateData): string;

    /**
     * @return string
     */
    public function getEventState(): string;

    /**
     * @param EventStateData $eventStateData
     *
     * @return string|null
     */
    public function getHref(EventStateData $eventStateData): ?string;

    /**
     * @param EventStateData $eventStateData
     *
     * @return bool
     */
    public function support(EventStateData $eventStateData): bool;

    /**
     * @param EventStateData $eventStateData
     *
     * @return string
     */
    public function getClass(EventStateData $eventStateData): string;
}
