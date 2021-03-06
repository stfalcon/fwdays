<?php

declare(strict_types=1);

namespace App\Service\Ticket;

use App\Model\EventStateData;

/**
 * DoneEventState.
 */
class DoneEventState extends AbstractBaseEventState
{
    /**
     * {@inheritdoc}
     */
    public function support(EventStateData $eventStateData): bool
    {
        return !$eventStateData->getEvent()->isActiveAndFuture();
    }

    /**
     * {@inheritdoc}
     */
    public function isDiv(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption(EventStateData $eventStateData): string
    {
        return $this->translator->trans(\sprintf('ticket.status.event_done%s', $eventStateData->getMob()));
    }

    /**
     * {@inheritdoc}
     */
    public function getEventState(): string
    {
        return TicketService::EVENT_DONE;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(EventStateData $eventStateData): string
    {
        $position = $eventStateData->getPosition();

        return TicketService::STATES[$position][TicketService::EVENT_DONE] ?? TicketService::STATES[$position][TicketService::EVENT_DEFAULT_STATE];
    }
}
