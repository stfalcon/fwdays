<?php

namespace App\EventListener\ORM\Payment;

use App\Entity\Payment;
use App\Entity\Ticket;
use App\Helper\MailerHelper;
use App\Repository\TicketRepository;
use App\Service\PaymentService;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * PaymentListener.
 */
final class PaymentListener
{
    /** @var MailerHelper */
    private $mailerHelper;
    /** @var \Swift_Mailer */
    private $mailer;
    /** @var bool */
    private $statusChanged = false;
    private $paymentService;
    private $ticketRepository;

    /**
     * @param MailerHelper     $mailerHelper
     * @param \Swift_Mailer    $mailer
     * @param PaymentService   $paymentService
     * @param TicketRepository $ticketRepository
     */
    public function __construct(MailerHelper $mailerHelper, \Swift_Mailer $mailer, PaymentService $paymentService, TicketRepository $ticketRepository)
    {
        $this->mailer = $mailer;
        $this->mailerHelper = $mailerHelper;
        $this->paymentService = $paymentService;
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * @param Payment            $payment
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Payment $payment, PreUpdateEventArgs $event): void
    {
        $this->statusChanged = $event->hasChangedField('status');
    }

    /**
     * @param Payment $payment
     */
    public function postUpdate(Payment $payment): void
    {
        if (Payment::STATUS_PAID === $payment->getStatus() && $this->statusChanged) {
            $this->paymentService->setTicketsCostAsSold($payment);
            $this->paymentService->calculateTicketsPromocode($payment);
            $tickets = $this->ticketRepository->getAllTicketsByPayment($payment);

            /** @var Ticket $ticket */
            foreach ($tickets as $ticket) {
                $message = $this->mailerHelper->formatMessageWithTicket($ticket);
                $this->mailer->send($message);
            }
        }
    }
}
