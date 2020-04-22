<?php

declare(strict_types=1);

namespace App\EventListener\ORM\Ticket;

use App\Entity\Payment;
use App\Entity\Ticket;
use App\Service\PaymentService;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * TicketAmountListener.
 */
final class TicketAmountListener
{
    private $paymentService;

    /** @var bool */
    private $amountChanged = false;

    /**
     * @param PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @param Ticket             $ticket
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Ticket $ticket, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('amount')) {
            $payment = $ticket->getPayment();
            $this->amountChanged = $payment instanceof Payment;
        }
    }

    /**
     * @param Ticket $ticket
     */
    public function postUpdate(Ticket $ticket): void
    {
        $payment = $ticket->getPayment();
        if ($this->amountChanged && $payment instanceof Payment) {
            if ($payment->isPaid()) {
                $this->paymentService->recalculateRefundedAmount($payment);
            } elseif ($payment->isPending()) {
                $this->paymentService->recalculatePaymentAmount($payment);
            }
        }
    }
}
