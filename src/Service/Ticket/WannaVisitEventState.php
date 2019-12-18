<?php

declare(strict_types=1);

namespace App\Service\Ticket;

use App\Model\EventStateData;

/**
 * WannaVisitEventState.
 */
class WannaVisitEventState extends AbstractBaseEventState
{
    /**
     * {@inheritdoc}
     */
    public function support(EventStateData $eventStateData): bool
    {
        $event = $eventStateData->getEvent();

        return $event->isActiveAndFuture() && !$event->getReceivePayments();
    }

    /**
     * {@inheritdoc}
     */
    public function getEventState(): string
    {
        return TicketService::CAN_WANNA_VISIT;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption(EventStateData $eventStateData): string
    {
        $event = $eventStateData->getEvent();
        $user = $eventStateData->getUser();

        if ($user && $user->isEventInWants($event)) {
            $caption = $this->translator->trans('ticket.status.not_take_apart');
        } else {
            $caption = $this->translator->trans('ticket.status.take_apart');
        }

        return $caption;
    }

    /**
     * {@inheritdoc}
     */
    public function getClass(EventStateData $eventStateData): string
    {
        $class = parent::getClass($eventStateData);
        $event = $eventStateData->getEvent();
        $user = $eventStateData->getUser();

        if ($user && $user->isEventInWants($event)) {
            $class .= ' sub-wants-visit-event';
        } else {
            $class .= ' add-wants-visit-event';
        }

        return $class;
    }
}
