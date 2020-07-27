<?php

declare(strict_types=1);

namespace App\Service\Ticket;

use App\Entity\TicketCost;
use App\Model\EventStateData;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * SaleOpenEventState.
 */
class SaleOpenEventState extends AbstractBaseEventState
{
    private $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption(EventStateData $eventStateData): string
    {
        return $this->translator->trans('ticket.status.pay');
    }

    /**
     * {@inheritdoc}
     */
    public function getHref(EventStateData $eventStateData): ?string
    {
        $ticketCost = $eventStateData->getTicketCost();
        $type = $ticketCost instanceof TicketCost ? $ticketCost->getType() : null;

        if ('price_block' === $eventStateData->getPosition()) {
            return $this->router->generate('event_pay', ['slug' => $eventStateData->getEvent()->getSlug(), 'type' => $type]);
        }

        return $this->router->generate('event_show', ['slug' => $eventStateData->getEvent()->getSlug()]).'#price-event';
    }

    /**
     * {@inheritdoc}
     */
    public function getEventState(): string
    {
        return TicketService::CAN_BUY_TICKET;
    }

    /**
     * {@inheritdoc}
     */
    public function support(EventStateData $eventStateData): bool
    {
        $event = $eventStateData->getEvent();

        return $event->isActiveAndFuture() && $event->getReceivePayments();
    }
}
