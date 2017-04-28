<?php

namespace Stfalcon\Bundle\EventBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\Container;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\PromoCode;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Application\Bundle\UserBundle\Entity\User;

class PaymentService
{
    /**
     * @var Container $container
     */
    protected $container;
    protected $em;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Create payment for current user ticket
     *
     * @param Ticket $ticket
     * @return Payment
     */
    public function createPaymentForCurrentUserWithTicket($ticket)
    {
        /* @var  User $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $payment = new Payment();
        $payment->setUser($user);
        $this->em->persist($payment);
        $this->addTicketToPayment($payment, $ticket);
        $this->em->flush();

        return $payment;
    }
    /**
     * додаем тикет до оплати
     *
     * @param Payment $payment
     * @param Ticket  $ticket
     */
    public function addTicketToPayment($payment, $ticket)
    {
        if (!$ticket->isPaid() && $payment->addTicket($ticket)) {
            $ticket->setPayment($payment);
            $this->em->persist($ticket);
            $this->recalculatePaymentAmount($payment);
        }
    }
    /**
     * видаляем тикет з оплати
     *
     * @param Payment $payment
     * @param Ticket  $ticket
     */
    public function removeTicketFromPayment($payment, $ticket)
    {
        if (!$ticket->isPaid() && $payment->removeTicket($ticket)) {
            $ticket->setPayment(null);
            $this->em->remove($ticket);
            $this->recalculatePaymentAmount($payment);
        }
    }

    /**
     * Recalculate amount of payment
     * @param Payment $payment
     */
    public function recalculatePaymentAmount($payment)
    {
        $paymentAmount = 0;
        $paymentAmountWithoutDiscount = 0;
        /** @var Ticket $ticket*/
        foreach ($payment->getTickets() as $ticket) {
            $paymentAmount += $ticket->getAmount();
            $paymentAmountWithoutDiscount += $ticket->getAmountWithoutDiscount();
        }
        $payment->setAmount($paymentAmount);
        $payment->setBaseAmount($paymentAmountWithoutDiscount);

        $this->em->flush();
    }

    /**
     * Get promo code from tickets if it have
     * @param Payment $payment
     * @return null|PromoCode
     */
    public function getPromoCodeFromPaymentTickets($payment)
    {
        $promoCode = null;
        foreach ($payment->getTickets() as $ticket) {
            /** @var  Ticket $ticket */
            if ($promoCode = $ticket->getPromoCode()) {
                return $promoCode;
            }
        }

        return $promoCode;
    }

    /**
     * Get ticket number for payment
     * @param Payment $payment
     * @return int|void
     */
    public function getTicketNumberFromPayment($payment)
    {
        /** @var ArrayCollection $tickets */
        $tickets = $payment->getTickets();

        if (!$tickets->isEmpty()) {
            return $tickets->first()->getId();
        }

        return ;
    }
    /**
     * Add promo code for all tickets in payment
     * if ticket already not have discount and
     * recalculate payment amount
     *
     * @param Payment   $payment
     * @param PromoCode $promoCode
     *
     * @return array
     */
    public function addPromoCodeForTicketsInPayment($payment, $promoCode)
    {
        $notUsedPromoCode = [];

        $ticketService = $this->container->get('stfalcon_event.ticket.service');
        /** @var  Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            if ($ticketService->isMustBeDiscount($ticket)) {
                $ticketService->setTicketBestDiscount($ticket, $promoCode);
            } else {
                $ticketService->setTicketPromoCode($ticket, $promoCode);
            }
            if (!$ticket->hasPromoCode()) {
                $notUsedPromoCode[] = $ticket->getUser()->getFullname();
            }
        }
        $this->recalculatePaymentAmount($payment);

        return $notUsedPromoCode;
    }

    /**
     * Пересчитываем итоговую сумму платежа по всем билетам
     * с учетом скидки
     *
     * @param Payment $payment
     * @param Event   $event
     */
    public function checkTicketsPricesInPayment($payment, $event)
    {
        $ticketService = $this->container->get('stfalcon_event.ticket.service');
        $eventCost = $event->getCost();
        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {

            $isMustBeDiscount = $ticketService->isMustBeDiscount($ticket);

            if (($ticket->getAmountWithoutDiscount() != $eventCost) ||
                ($ticket->getHasDiscount() != ($isMustBeDiscount || $ticket->hasPromoCode()))) {
                $ticketService->setTicketAmount($ticket, $eventCost, $isMustBeDiscount);
            }
        }
        $this->recalculatePaymentAmount($payment);
    }

    /**
     * Correct pay amount by user referral money
     *
     * @param Payment $payment
     */
    public function payByReferralMoney(Payment $payment)
    {
        /* @var  User $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if ($user->getBalance() > 0) {
            $amount = $user->getBalance() - $payment->getAmount();
            if ($amount < 0) {
                $payment->setAmount(-$amount);
                $payment->setFwdaysAmount($user->getBalance());
            } else {
                $payment->setAmount(0);
                $payment->setFwdaysAmount($payment->getBaseAmount());
                $payment->markedAsPaid();
                $this->container->get('stfalcon_event.referral.service')->utilizeBalance($payment);
            }
            $this->em->flush();
        }
    }
}
