<?php

namespace Application\Bundle\DefaultBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\PromoCode;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Application\Bundle\UserBundle\Entity\User;

class PaymentService
{
    /**
     * @var Container
     */
    protected $container;
    /** @var $em EntityManager */
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
     * Create payment for current user ticket.
     *
     * @param Ticket|null $ticket
     *
     * @return Payment
     */
    public function createPaymentForCurrentUserWithTicket($ticket)
    {
        /* @var  User $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $payment = new Payment();
        $payment->setUser($user);

        $this->em->persist($payment);
        if ($ticket instanceof Ticket) {
            if (($ticket->getPayment() && $this->removeTicketFromPayment($ticket->getPayment(), $ticket))
                || !$ticket->getPayment()) {
                $this->addTicketToPayment($payment, $ticket);
            }
        }

        $this->em->flush();

        return $payment;
    }

    /**
     * додаем тикет до оплати.
     *
     * @param Payment $payment
     * @param Ticket  $ticket
     */
    public function addTicketToPayment($payment, $ticket)
    {
        if (!$ticket->isPaid() && $payment->addTicket($ticket)) {
            $ticket->setPayment($payment);
            $this->em->persist($ticket);
            //$this->recalculatePaymentAmount($payment);
        }
    }

    /**
     * видаляем тикет з оплати.
     *
     * @param Payment $payment
     * @param Ticket  $ticket
     *
     * @return bool
     */
    public function removeTicketFromPayment($payment, $ticket)
    {
        if (!$ticket->isPaid() && $payment->removeTicket($ticket)) {
            $this->em->remove($ticket);
            $this->recalculatePaymentAmount($payment);

            return true;
        }

        return false;
    }

    /**
     * Recalculate amount of payment.
     *
     * @param Payment $payment
     */
    public function recalculatePaymentAmount($payment)
    {
        $paymentAmount = 0;
        $paymentAmountWithoutDiscount = 0;
        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            $paymentAmount += $ticket->getAmount();
            $paymentAmountWithoutDiscount += $ticket->getAmountWithoutDiscount();
        }
        $payment->setAmount($paymentAmount);
        $payment->setBaseAmount($paymentAmountWithoutDiscount);
        $this->payByReferralMoney($payment);
        $this->em->flush();
    }

    /**
     * Get promo code from tickets if it have.
     *
     * @param Payment $payment
     *
     * @return null|PromoCode
     */
    public function getPromoCodeFromPaymentTickets($payment)
    {
        $promoCode = null;
        foreach ($payment->getTickets() as $ticket) {
            /** @var Ticket $ticket */
            if ($promoCode = $ticket->getPromoCode()) {
                return $promoCode;
            }
        }

        return $promoCode;
    }

    /**
     * Get ticket number for payment.
     *
     * @param Payment $payment
     *
     * @return int|void
     */
    public function getTicketNumberFromPayment($payment)
    {
        /** @var ArrayCollection $tickets */
        $tickets = $payment->getTickets();

        if (!$tickets->isEmpty()) {
            return $tickets->first()->getId();
        }

        return;
    }

    /**
     * Add promo code for all tickets in payment
     * if ticket already not have discount and
     * recalculate payment amount.
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
        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            if (!$promoCode->isCanBeTmpUsed()) {
                break;
            }
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
     * с учетом скидки.
     *
     * @param Payment $payment
     * @param Event   $event
     */
    public function checkTicketsPricesInPayment($payment, $event)
    {
        $ticketService = $this->container->get('stfalcon_event.ticket.service');
        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            $currentTicketCost = $this->container->get('app.ticket_cost.service')->getCurrentEventTicketCost($event);

            if (null === $currentTicketCost) {
                $currentTicketCost = $event->getBiggestTicketCost();
            }

            $eventCost = $currentTicketCost->getAmountByTemporaryCount();
            $isMustBeDiscount = $ticketService->isMustBeDiscount($ticket);

            if (($ticket->getTicketCost() !== $currentTicketCost) ||
                ($ticket->getAmountWithoutDiscount() !== $eventCost) ||
                ($ticket->getHasDiscount() !== ($isMustBeDiscount || $ticket->hasPromoCode()))) {
                $ticketService->setTicketAmount($ticket, $eventCost, $isMustBeDiscount, $currentTicketCost);
            }
        }
        $this->recalculatePaymentAmount($payment);
    }

    /**
     * Check ticket costs as sold.
     *
     * @param Payment $payment
     */
    public function setTicketsCostAsSold($payment)
    {
        if ($payment->isPaid()) {
            /** @var Ticket $ticket */
            foreach ($payment->getTickets() as $ticket) {
                $ticketCost = $ticket->getTicketCost();
                if ($ticketCost) {
                    $ticketCost->incSoldCount();
                }
            }

            $this->em->flush();
        }
    }

    /**
     * Calculate using promocode.
     *
     * @param Payment $payment
     */
    public function calculateTicketsPromocode($payment)
    {
        if ($payment->isPaid()) {
            /** @var Ticket $ticket */
            foreach ($payment->getTickets() as $ticket) {
                if ($ticket->hasPromoCode()) {
                    $promoCode = $ticket->getPromoCode();
                    if ($promoCode) {
                        $promoCode->incUsedCount();
                    }
                }
            }
            $this->em->flush();
        }
    }

    /**
     * set payment paid if have referral money.
     *
     * @param Payment $payment
     * @param Event   $event
     *
     * @return bool
     */
    public function setPaidByReferralMoney(Payment $payment, Event $event)
    {
        $this->checkTicketsPricesInPayment($payment, $event);
        if ($payment->isPending() && 0 === $payment->getAmount() && $payment->getFwdaysAmount() > 0) {
            $payment->markedAsPaid();
            $payment->setGate('fwdays-amount');

            $referralService = $this->container->get('stfalcon_event.referral.service');
            $referralService->utilizeBalance($payment);

            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * Correct pay amount by user referral money.
     *
     * @param Payment $payment
     */
    private function payByReferralMoney(Payment $payment)
    {
        /* @var  User $user */
        $user = $payment->getUser();
        if ($user instanceof User && $user->getBalance() > 0 && $payment->getAmount() > 0) {
            $amount = $user->getBalance() - $payment->getAmount();
            if ($amount < 0) {
                $payment->setAmount(-$amount);
                $payment->setFwdaysAmount($user->getBalance());
            } else {
                $payment->setFwdaysAmount($payment->getAmount());
                $payment->setAmount(0);
            }
        } else {
            $payment->setFwdaysAmount(0);
        }
    }
}
