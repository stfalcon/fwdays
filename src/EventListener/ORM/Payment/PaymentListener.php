<?php

namespace App\EventListener\ORM\Payment;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Helper\MailerHelper;
use App\Message\AddUserToGoogleCalendarEventMessage;
use App\Repository\TicketRepository;
use App\Service\GoogleEvent\GoogleEventService;
use App\Service\PaymentService;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

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
    /** @var MessageBusInterface */
    private $bus;

    //@todo remove GoogleEventService after add redis and active messenger
    /** @var GoogleEventService */
    private $eventService;

    /**
     * @param MailerHelper        $mailerHelper
     * @param \Swift_Mailer       $mailer
     * @param PaymentService      $paymentService
     * @param TicketRepository    $ticketRepository
     * @param MessageBusInterface $bus
     */
    public function __construct(MailerHelper $mailerHelper, \Swift_Mailer $mailer, PaymentService $paymentService, TicketRepository $ticketRepository, MessageBusInterface $bus, GoogleEventService $eventService)
    {
        $this->mailer = $mailer;
        $this->mailerHelper = $mailerHelper;
        $this->paymentService = $paymentService;
        $this->ticketRepository = $ticketRepository;
        $this->bus = $bus;
        $this->eventService = $eventService;
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
        if ($this->statusChanged && Payment::STATUS_PAID === $payment->getStatus()) {
            $this->paymentService->setTicketsCostAsSold($payment);
            $this->paymentService->calculateTicketsPromocode($payment);
            $tickets = $this->ticketRepository->getAllTicketsByPayment($payment);

            /** @var Ticket $ticket */
            foreach ($tickets as $ticket) {
                $user = $ticket->getUser();
                $event = $ticket->getEvent();

                if ($user instanceof User && $event instanceof Event && \is_string($event->getGoogleCalendarEventId())) {
                    $this->eventService->addAttendeeToEvent($event, $user);
//                    $this->bus->dispatch(new AddUserToGoogleCalendarEventMessage($event->getId(), $user->getId()));
                }

                $message = $this->mailerHelper->formatMessageWithTicket($ticket);
                $this->mailer->send($message);
            }
        }
    }
}
