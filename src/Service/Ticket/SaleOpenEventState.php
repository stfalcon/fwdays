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
        $caption = $this->translator->trans(\sprintf('ticket.status.pay%s', $eventStateData->getMob()));
        if ('price_block' === $eventStateData->getPosition()) {
            $ticketCost = $eventStateData->getTicketCost();
            if (null === $ticketCost->getType()) {
                $amount = $ticketCost ? $ticketCost->getAmount() : $eventStateData->getEvent()->getBiggestTicketCost()->getAmount();
                $altAmount = $ticketCost ? '≈$' . \number_format($ticketCost->getAltAmount(), 0, ',', ' ') : '';
                $caption = $this->translator->trans('ticket.status.pay_for') . ' ' . $this->translator
                        ->trans(
                            'payment.price',
                            [
                                '%summ%' => \number_format((float)$amount, 0, ',', ' '),
                            ]
                        );
                if ($ticketCost && $ticketCost->getAltAmount()) {
                    $caption .= '<span class="cost__dollars">' . $altAmount . '</span>';
                }
            } else {
                $caption = $this->translator->trans('ticket.status.pay');
            }
        }

        return $caption;
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
