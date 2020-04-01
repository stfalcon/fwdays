<?php

namespace App\EventListener\ORM\Payment;

use App\Entity\Payment;
use App\Entity\Ticket;
use App\Helper\StfalconMailerHelper;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * PaymentListener.
 */
final class PaymentListener
{
    /** @var StfalconMailerHelper $mailerHelper */
    private $mailerHelper;

    /** @var \Swift_Mailer $mailer */
    private $mailer;
    /**
     * @var Container
     */
    private $container;

    /** @var bool */
    private $statusChanged = false;
    private $requestStack;

    /**
     * PaymentListener constructor.
     *
     * @param Container    $container
     * @param RequestStack $requestStack
     */
    public function __construct($container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
    }

    /**
     * @param Payment            $payment
     * @param PreUpdateEventArgs $event
     */
    public function preUpdate(Payment $payment, PreUpdateEventArgs $event)
    {
        $this->statusChanged = $event->hasChangedField('status');
    }

    /**
     * @param Payment $payment
     */
    public function postUpdate(Payment $payment)
    {
        if (Payment::STATUS_PAID === $payment->getStatus() && $this->statusChanged) {
            $this->mailer = $this->container->get('mailer');
            $this->mailerHelper = $this->container->get(StfalconMailerHelper::class);

            /** @var PaymentService $paymentService */
            $paymentService = $this->container->get(PaymentService::class);
            $paymentService->setTicketsCostAsSold($payment);
            $paymentService->calculateTicketsPromocode($payment);
            /** @var EntityManager $em */
            $em = $this->container->get('doctrine.orm.entity_manager');
            $tickets = $em->getRepository(Ticket::class)
                ->getAllTicketsByPayment($payment);

            /** @var Ticket $ticket */
            foreach ($tickets as $ticket) {
                $message = $this->mailerHelper->formatMessageWithTicket($ticket);
                $this->mailer->send($message);
            }
        }
    }
}
