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
     * @param User   $user
     * @param Ticket $ticket
     * @return Payment
     */
    public function createPaymentForUserWithTicket($user, $ticket)
    {
        $payment = new Payment();
        $payment->setUser($user);
        $this->addTicketToPayment($payment, $ticket, false);
        $this->em->persist($payment);
        $this->em->flush();

        return $payment;
    }
    /**
     * додаем тикет до оплати
     *
     * @param Payment $payment
     * @param Ticket  $ticket
     * @param bool    $withFlush
     */
    public function addTicketToPayment($payment, $ticket, $withFlush = true)
    {
        if (!$ticket->isPaid() && $payment->addTicket($ticket)) {
            $ticket->setPayment($payment);

            $payment->setAmount($payment->getAmount() + $ticket->getAmount());
            $payment->setBaseAmount($payment->getBaseAmount() + $ticket->getAmount());
            if ($withFlush) {
                $this->em->persist($ticket);
                $this->em->flush();
            }
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

            $payment->setAmount($payment->getAmount() - $ticket->getAmount());
            $payment->setBaseAmount($payment->getBaseAmount() - $ticket->getAmount());
            $this->em->remove($ticket);
            $this->em->flush();
        }
    }

    /**
     * Recalculate amount of payment
     * @param Payment $payment
     */
    public function recalculatePaymentAmount($payment)
    {
        $result = 0;
        foreach ($payment->getTickets() as $ticket) {
            /** @var Ticket $ticket*/
            $result += $ticket->getAmount();
        }
        $payment->setAmount($result);

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
     * @param int       $baseDiscount
     *
     * @return array
     */
    public function addPromoCodeForTicketsInPayment($payment, $promoCode, $baseDiscount)
    {
        $notUsedPromoCode = [];
        /** @var  Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            if (!$ticket->getHasDiscount()) {
                $ticket->setPromoCode($promoCode);
            } elseif ($ticket->getHasDiscount() &&
                !$ticket->hasPromoCode() &&
                ($promoCode->getDiscountAmount() > $baseDiscount)
            ) {
                $ticket->setPromoCode($promoCode);
            } else {
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
        // Вытягиваем скидку из конфига
        $paymentsConfig = $this->container->getParameter('stfalcon_event.config');
        $discount = (float) $paymentsConfig['discount'];

        $eventCost = $event->getCost();
        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            // получаем оплаченые платежи пользователя
            $paidPayments = $this->em->getRepository('StfalconEventBundle:Payment')
                ->findPaidPaymentsForUser($ticket->getUser());

            //правильно ли установлен флаг наличия скидки
            // @todo с расчетом скидки у нас явно проблемы. ниже почти такой же код идет. плюс ещё в нескольких
            // местах по коду делаем подобные расчеты. плюс в самой модели билета есть логика расчета цены со скидкой...

            $isCorrectDiscount = $ticket->getHasDiscount() == ((count($paidPayments) > 0 && $event->getUseDiscounts()) || $ticket->hasPromoCode());

            // если цена билета без скидки не ровна новой цене на ивент
            // или неверно указан флаг наличия скидки
            if (($ticket->getAmountWithoutDiscount() != $eventCost) || !$isCorrectDiscount) {
                // если не правильно установлен флаг наличия скидки, тогда устанавливаем его заново
                if (!$isCorrectDiscount) {
                    // @todo для реализации возможности отключения скидки постоянных участников мне пришлось
                    // использовать метод $event->getUseDiscounts() в трех разных местах. а нужно, чтобы
                    // это можно было сделать в одном месте
                    $ticket->setHasDiscount(((count($paidPayments) > 0 && $event->getUseDiscounts()) || $ticket->hasPromoCode()));
                }

                $ticket->setAmountWithoutDiscount($eventCost);
                if ($ticket->getHasDiscount()) {
                    $ticket->setAmountWithDiscount($discount);
                } else {
                    $ticket->setAmount($eventCost);
                }
                $this->em->merge($ticket);
            }
        }

        $this->recalculatePaymentAmount($payment);

        $this->em->merge($payment);
        $this->em->flush();
    }
}
