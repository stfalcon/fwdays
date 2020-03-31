<?php

namespace App\EventListener;

use App\Entity\Payment;
use App\Entity\Ticket;
use App\Helper\StfalconMailerHelper;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class PaymentListener.
 */
class PaymentListener
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
    private $runPaymentPostUpdate = true;
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
     * @param bool $runPaymentPostUpdate
     */
    public function setRunPaymentPostUpdate($runPaymentPostUpdate)
    {
        $this->runPaymentPostUpdate = $runPaymentPostUpdate;
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Exception
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $payment = $args->getEntity();
        if ($payment instanceof Payment) {
            if (Payment::STATUS_PAID === $payment->getStatus() && $this->runPaymentPostUpdate) {
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
}
