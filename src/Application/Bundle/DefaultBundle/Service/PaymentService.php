<?php

namespace Application\Bundle\DefaultBundle\Service;

use Application\Bundle\DefaultBundle\Controller\PaymentController;
use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\PromoCode;
use Application\Bundle\DefaultBundle\Entity\Ticket;
use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Application\Bundle\DefaultBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * PaymentService.
 */
class PaymentService
{
    private const ACTIVE_PAYMENT_ID_KEY = 'active_payment_id';

    private $em;
    private $ticketService;
    private $translator;
    private $tokenStorage;
    private $referralService;
    private $session;

    /**
     * @param EntityManager       $em
     * @param TicketService       $ticketService
     * @param TranslatorInterface $translator
     * @param TokenStorage        $tokenStorage
     * @param ReferralService     $referralService
     * @param Session             $session
     */
    public function __construct(EntityManager $em, TicketService $ticketService, TranslatorInterface $translator, TokenStorage $tokenStorage, ReferralService $referralService, Session $session)
    {
        $this->em = $em;
        $this->ticketService = $ticketService;
        $this->translator = $translator;
        $this->tokenStorage = $tokenStorage;
        $this->referralService = $referralService;
        $this->session = $session;
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
        $user = $this->tokenStorage->getToken()->getUser();
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

        $this->session->set(PaymentController::NEW_PAYMENT_SESSION_KEY, true);

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
     * @param Payment $payment
     * @param bool    $withFlush
     */
    public function recalculatePaymentAmount(Payment $payment, bool $withFlush = true): void
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
        $this->recalculatePaymentFwdaysAmount($payment);
        if ($withFlush) {
            $this->em->flush();
        }
    }

    /**
     * Get promo code from tickets if it have.
     *
     * @param Payment $payment
     *
     * @return PromoCode|null
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
     * @return int|null
     */
    public function getTicketNumberFromPayment($payment)
    {
        /** @var ArrayCollection $tickets */
        $tickets = $payment->getTickets();

        if (!$tickets->isEmpty()) {
            return $tickets->first()->getId();
        }

        return null;
    }

    /**
     * @param string $promoCodeString
     * @param Event  $event
     * @param Ticket $ticket
     *
     * @throws \Exception
     * @throws BadRequestHttpException
     */
    public function addPromoCodeForTicketByCode(?string $promoCodeString, Event $event, Ticket $ticket): void
    {
        if ($promoCodeString) {
            /** @var PromoCode|null $promoCode */
            $promoCode = $this->em->getRepository('ApplicationDefaultBundle:PromoCode')
                ->findActivePromoCodeByCodeAndEvent($promoCodeString, $event);

            if (!$promoCode) {
                throw new BadRequestHttpException($this->translator->trans('error.promocode.not_found'));
            }

            if (!$promoCode->isCanBeUsed()) {
                throw new BadRequestHttpException($this->translator->trans('error.promocode.used'));
            }

            $result = $this->addPromoCodeForTicket($ticket, $promoCode);
        } else {
            $result = PromoCode::PROMOCODE_APPLIED;
            $ticket->setPromoCode(null);
        }

        if (PromoCode::PROMOCODE_APPLIED !== $result) {
            throw new BadRequestHttpException($this->translator->trans($result));
        }
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
        /** @var Ticket $ticket */
        foreach ($payment->getTickets() as $ticket) {
            $currentTicketCost = $this->ticketService->getCurrentEventTicketCost($event);

            if (null === $currentTicketCost) {
                $currentTicketCost = $event->getBiggestTicketCost();
            }

            $eventCost = $currentTicketCost->getAmountByTemporaryCount();
            $isMustBeDiscount = $this->ticketService->isMustBeDiscount($ticket);

            $promoCodeCleared = false;
            $promoCode = $ticket->getPromoCode();
            if ($promoCode instanceof PromoCode) {
                if (!$promoCode->isCanBeTmpUsed()) {
                    $ticket->setPromoCode(null);
                    $promoCodeCleared = true;
                } else {
                    $promoCode->incTmpUsedCount();
                }
            }

            if (($ticket->getTicketCost() !== $currentTicketCost) ||
                ((int) $ticket->getAmountWithoutDiscount() !== (int) $eventCost) ||
                ($ticket->getHasDiscount() !== ($isMustBeDiscount || $ticket->hasPromoCode())) ||
                $promoCodeCleared
            ) {
                $this->ticketService->setTicketAmount($ticket, $eventCost, $isMustBeDiscount, $currentTicketCost);
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
        $ticketCostsRecalculate = [];
        if ($payment->isPaid()) {
            /** @var Ticket $ticket */
            foreach ($payment->getTickets() as $ticket) {
                $ticketCost = $ticket->getTicketCost();
                if ($ticketCost) {
                    $ticketCostsRecalculate[$ticketCost->getId()] = $ticketCost;
                }
            }
            /** @var TicketCost $ticketCost */
            foreach ($ticketCostsRecalculate as $ticketCost) {
                $ticketCost->recalculateSoldCount();
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
    public function setPaidByBonusMoney(Payment $payment, Event $event)
    {
        $this->checkTicketsPricesInPayment($payment, $event);
        if ($payment->isPending() && 0 === (int) $payment->getAmount() && $payment->getFwdaysAmount() > 0) {
            $payment->setPaidWithGate(Payment::BONUS_GATE);

            $this->referralService->utilizeBalance($payment);

            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * set payment paid if have referral money.
     *
     * @param Payment $payment
     * @param Event   $event
     *
     * @return bool
     */
    public function setPaidByPromocode(Payment $payment, Event $event)
    {
        $this->checkTicketsPricesInPayment($payment, $event);
        if ($payment->isPending() && 0 === (int) $payment->getAmount() && 0 === (int) $payment->getFwdaysAmount()) {
            $payment->setPaidWithGate(Payment::PROMOCODE_GATE);

            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * @param Payment $payment
     * @param float   $amount
     */
    public function addFwdaysBonusToPayment(Payment $payment, float $amount): void
    {
        $payment->setFwdaysAmount($amount);
        $this->recalculatePaymentAmount($payment);
    }

    /**
     * @param Event $event
     *
     * @return Payment
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getPaymentForCurrentUser(Event $event): Payment
    {
        $this->session->set(PaymentController::NEW_PAYMENT_SESSION_KEY, false);

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        /* @var Ticket|null $ticket  */
        $ticket = $this->em->getRepository('ApplicationDefaultBundle:Ticket')->findOneBy(['user' => $user->getId(), 'event' => $event->getId()]);

        $paymentRepository = $this->em->getRepository('ApplicationDefaultBundle:Payment');
        /** @var Payment|null $payment */
        $payment = $paymentRepository->findPaymentByUserAndEvent($user, $event);
        if (!$payment) {
            $payment = $paymentRepository->findPaymentByUserWithoutEvent($user);
        }

        if (!$ticket && !$payment) {
            $ticket = $this->ticketService->createTicket($event, $user);
            $user->addWantsToVisitEvents($event);
            $this->em->flush();
        }

        if (!$payment && $ticket->getPayment() && !$ticket->getPayment()->isReturned()) {
            $payment = $ticket->getPayment();
            $payment->setUser($ticket->getUser());
            $this->em->flush();
        }

        if ($ticket && !$payment) {
            $payment = $this->createPaymentForCurrentUserWithTicket($ticket);
        }

        if ($ticket && $payment->isPaid()) {
            $payment = $this->createPaymentForCurrentUserWithTicket(null);
        }

        if ($payment->isPending()) {
            $this->checkTicketsPricesInPayment($payment, $event);
        }

        $this->session->set(self::ACTIVE_PAYMENT_ID_KEY, $payment->getId());

        return $payment;
    }

    /**
     * @param User|null $user
     * @param Event     $event
     * @param Ticket    $editTicket
     *
     * @return Ticket
     */
    public function replaceIfFindOtherUserTicketForEvent(?User $user, Event $event, Ticket $editTicket): Ticket
    {
        if (!$user instanceof User) {
            return $editTicket;
        }
        /** @var Ticket|null $ticket */
        $ticket = $this->em->getRepository('ApplicationDefaultBundle:Ticket')
            ->findOneByUserAndEventWithPendingPayment($user, $event);

        if (!$ticket instanceof Ticket || $editTicket->isEqualTo($ticket)) {
            return $editTicket;
        }
        $payment = $editTicket->getPayment();

        if (!$editTicket->isEqualTo($ticket) && $payment->isEqualTo($ticket->getPayment())) {
            throw new BadRequestHttpException();
        }

        $this->addTicketToPayment($payment, $ticket);
        $this->removeTicketFromPayment($payment, $editTicket);

        return $ticket;
    }

    /**
     * @param Ticket|null $ticket
     *
     * @return Payment|null $payment
     */
    public function getPendingPaymentIfAccess(?Ticket $ticket = null): ?Payment
    {
        $payment = null;
        $currentUser = $this->tokenStorage->getToken()->getUser();

        if (!$currentUser instanceof User) {
            return null;
        }

        if ($this->session->has(self::ACTIVE_PAYMENT_ID_KEY)) {
            $paymentId = $this->session->get(self::ACTIVE_PAYMENT_ID_KEY);
            $payment = $this->em->getRepository('ApplicationDefaultBundle:Payment')
                ->findPendingPaymentByIdForUser($paymentId, $currentUser);
        }

        if (!$payment instanceof Payment) {
            return null;
        }

        if ($ticket instanceof Ticket) {
            $payment = $payment->getTickets()->contains($ticket) ? $payment : null;
        }

        return $payment;
    }

    /**
     * @param Ticket    $ticket
     * @param PromoCode $promoCode
     *
     * @return string
     */
    private function addPromoCodeForTicket(Ticket $ticket, PromoCode $promoCode): string
    {
        $promoCode->clearTmpUsedCount();
        if (!$promoCode->isUnlimited()) {
            $payment = $ticket->getPayment();
            if ($payment instanceof Payment) {
                foreach ($payment->getTickets() as $paymentTicket) {
                    if ($promoCode->isEqualTo($paymentTicket->getPromoCode()) && !$ticket->isEqualTo($paymentTicket)) {
                        $promoCode->incTmpUsedCount();
                    }
                }
            }
        }

        if (!$promoCode->isCanBeTmpUsed()) {
            return PromoCode::PROMOCODE_USED;
        }

        if ($this->ticketService->isMustBeDiscount($ticket)) {
            $result = $this->ticketService->setTicketBestDiscount($ticket, $promoCode);
        } else {
            $this->ticketService->setTicketPromoCode($ticket, $promoCode);
            $result = PromoCode::PROMOCODE_APPLIED;
        }
        $promoCode->clearTmpUsedCount();

        return $result;
    }

    /**
     * @param Payment $payment
     */
    private function recalculatePaymentFwdaysAmount(Payment $payment): void
    {
        $amount = $payment->getFwdaysAmount();
        if ($amount > 0) {
            $user = $payment->getUser();
            $payment->setFwdaysAmount(0);
            if ($user instanceof User) {
                if ($amount > $user->getBalance()) {
                    $amount = $user->getBalance();
                }
                if ($amount > $payment->getAmount()) {
                    $amount = $payment->getAmount();
                }
                $payment->setAmount($payment->getAmount() - $amount);
                $payment->setFwdaysAmount($amount);
            }
        }
    }
}
