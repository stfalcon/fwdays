<?php

declare(strict_types=1);

namespace App\Service\Ticket;

use App\Model\EventStateData;
use App\Traits\TranslatorTrait;

/**
 * AbstractBaseEventState.
 */
abstract class AbstractBaseEventState implements EventStateInterface
{
    use TranslatorTrait;

    /**
     *{@inheritdoc}
     */
    abstract public function support(EventStateData $eventStateData): bool;

    /**
     * {@inheritdoc}
     */
    public function isDiv(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getHref(EventStateData $eventStateData): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getCaption(EventStateData $eventStateData): string;

    /**
     * {@inheritdoc}
     */
    abstract public function getEventState(): string;

    /**
     * {@inheritdoc}
     */
    public function getClass(EventStateData $eventStateData): string
    {
        $position = $eventStateData->getPosition();

        return TicketService::STATES[$position][$this->getEventState()] ?? TicketService::STATES[$position][TicketService::EVENT_DEFAULT_STATE];
    }
}
