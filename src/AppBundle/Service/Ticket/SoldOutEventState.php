<?php

declare(strict_types=1);

namespace App\Service\Ticket;

use App\Model\EventStateData;

/**
 * SoldOutEventState.
 */
class SoldOutEventState extends AbstractBaseEventState
{
    /**
     * {@inheritdoc}
     */
    public function support(EventStateData $eventStateData): bool
    {
        $event = $eventStateData->getEvent();

        return $event->isActiveAndFuture() && !$event->isHasAvailableTickets();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventState(): string
    {
        return TicketService::TICKETS_SOLD_OUT;
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
        return $this->translator->trans(\sprintf('ticket.status.sold%s', $eventStateData->getMob()));
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
