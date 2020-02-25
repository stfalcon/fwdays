<?php

declare(strict_types=1);

namespace App\Service\Ticket;

use App\Model\EventStateData;

/**
 * WaitPaymentReceiveEventState.
 */
class WaitPaymentReceiveEventState extends AbstractBaseEventState
{
    /**
     * {@inheritdoc}
     */
    public function support(EventStateData $eventStateData): bool
    {
        $event = $eventStateData->getEvent();

        return $event->isActiveAndFuture() && $eventStateData->getTicket() && !$event->getReceivePayments();
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
    public function getEventState(): string
    {
        return TicketService::WAIT_FOR_PAYMENT_RECEIVE;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption(EventStateData $eventStateData): string
    {
        return $this->translator->trans('ticket.status.event.add');
    }
}
