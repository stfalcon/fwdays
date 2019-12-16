<?php

declare(strict_types=1);

namespace App\Service\Ticket;

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
            $amount = $ticketCost ? $ticketCost->getAmount() : $eventStateData->getEvent()->getBiggestTicketCost()->getAmount();
            $altAmount = $ticketCost ? '≈$'.\number_format((float) $ticketCost->getAltAmount(), 0, ',', ' ') : '';
            $caption = $this->translator->trans('ticket.status.pay_for').' '.$this->translator
                    ->trans(
                        'payment.price',
                        [
                            '%summ%' => \number_format((float) $amount, 0, ',', ' '),
                        ]
                    );
            if ($ticketCost && $ticketCost->getAltAmount()) {
                $caption .= '<span class="cost__dollars">'.$altAmount.'</span>';
            }
        }

        return $caption;
    }

    /**
     * {@inheritdoc}
     */
    public function getHref(EventStateData $eventStateData): ?string
    {
        return $this->router->generate('event_pay', ['slug' => $eventStateData->getEvent()->getSlug()]);
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
        $payment = $eventStateData->getPendingPayment();

        return $eventStateData->getEvent()->isActiveAndFuture() && (!$payment || $payment->isPending());
    }
}
