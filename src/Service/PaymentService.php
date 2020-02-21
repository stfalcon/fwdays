<?php

namespace App\Service;

use App\Controller\PaymentController;
use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\PromoCode;
use App\Entity\Ticket;
use App\Entity\TicketCost;
use App\Entity\User;
use App\Repository\PaymentRepository;
use App\Repository\PromoCodeRepository;
use App\Repository\TicketRepository;
use App\Service\Ticket\TicketService;
use App\Service\User\UserService;
use App\Traits;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * PaymentService.
 */
class PaymentService
{
    use Traits\SessionTrait;
    use Traits\TranslatorTrait;
    use Traits\EntityManagerTrait;

    public const PROMO_CODE_SESSION_KEY = 'events_promocode';

    private const ACTIVE_PAYMENT_ID_KEY = 'active_payment_id_%s';

    private $ticketService;
    private $userService;
    private $referralService;

    /**
     * @param TicketService   $ticketService
     * @param UserService     $userService
     * @param ReferralService $referralService
     */
    public function __construct(TicketService $ticketService, UserService $userService, ReferralService $referralService)
    {
        $this->ticketService = $ticketService;
        $this->userService = $userService;
        $this->referralService = $referralService;
    }

    /**
     * Create payment for current user ticket.
     *
     * @param Ticket|null $ticket
     *
     * @return Payment
     */
    public function createPaymentForCurrentUserWithTicket(?Ticket $ticket)
    {
        $user = $this->userService->getCurrentUser();
        $payment = new Payment();
        $payment->setUser($user);

        $this->em->persist($payment);
        if ($ticket instanceof Ticket) {
            $ticketPayment = $ticket->getPayment();
            if ($ticketPayment instanceof Payment) {
                $this->removeTicketFromPayment($ticketPayment, $ticket);
            }
            $this->addTicketToPayment($payment, $ticket);
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
    public function addTicketToPayment(Payment $payment, Ticket $ticket): void
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
     * @param bool   $throwException
     *
     * @throws \Exception
     * @throws BadRequestHttpException
     */
    public function addPromoCodeForTicketByCode(?string $promoCodeString, Event $event, Ticket $ticket, bool $throwException = true): void
    {
        if ($promoCodeString) {
            /** @var PromoCodeRepository $promoCodeRepository */
            $promoCodeRepository = $this->em->getRepository(PromoCode::class);
            $promoCode = $promoCodeRepository
                ->findActivePromoCodeByCodeAndEvent($promoCodeString, $event);

            if (!$promoCode) {
                if ($throwException) {
                    throw new BadRequestHttpException($this->translator->trans('error.promocode.not_found'));
                }

                return;
            }

            if (!$promoCode->isCanBeUsed()) {
                if ($throwException) {
                    throw new BadRequestHttpException($this->translator->trans('error.promocode.used'));
                }

                return;
            }

            $result = $this->addPromoCodeForTicket($ticket, $promoCode);
        } else {
            $result = PromoCode::PROMOCODE_APPLIED;
            $ticket->setPromoCode(null);
        }

        if (PromoCode::PROMOCODE_APPLIED !== $result && $throwException) {
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
    public function checkTicketsPricesInPayment(Payment $payment, Event $event): void
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

            if (0.0 === $ticket->getAmount() && $ticket->hasPromoCode()) {
                $ticket->removeTicketCost();
            }
        }
        $this->recalculatePaymentAmount($payment);
    }

    /**
     * Check ticket costs as sold.
     *
     * @param Payment $payment
     */
    public function setTicketsCostAsSold(Payment $payment): void
    {
        $ticketCostsRecalculate = [];
        if ($payment->isPaid()) {
            /** @var Ticket $ticket */
            foreach ($payment->getTickets() as $ticket) {
                $ticketCost = $ticket->getTicketCost();
                if ($ticketCost instanceof TicketCost) {
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
    public function calculateTicketsPromocode(Payment $payment): void
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

        $user = $this->userService->getCurrentUser();

        /* @var Ticket|null $ticket */
        $ticket = $this->em->getRepository(Ticket::class)->findOneBy(['user' => $user->getId(), 'event' => $event->getId()]);

        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->em->getRepository(Payment::class);
        /** @var Payment|null $payment */
        $payment = $paymentRepository->findPendingPaymentByUserAndEvent($user, $event);
        if (!$payment) {
            $payment = $paymentRepository->findPendingPaymentByUserWithoutEvent($user);
        }

        if (!$ticket instanceof Ticket && !$payment instanceof Payment) {
            $this->userService->registerUserToEvent($user, $event);
            $ticket = $this->ticketService->createTicket($event, $user);
        }
        /** @var Ticket $ticket */
        if (!$payment instanceof Payment && $ticket->getPayment() && !$ticket->getPayment()->isReturned()) {
            $payment = $ticket->getPayment();
            if ($payment->isPending()) {
                $payment->setUser($ticket->getUser());
            }
        }

        if ($ticket instanceof Ticket && !$payment instanceof Payment) {
            $payment = $this->createPaymentForCurrentUserWithTicket($ticket);
        } elseif ($ticket instanceof Ticket && $payment->isPaid()) {
            $payment = $this->createPaymentForCurrentUserWithTicket(null);
        }

        if ($payment->isPending()) {
            $this->addPromocodeFromSession($payment, $event);
            $this->checkTicketsPricesInPayment($payment, $event);
        }
        $sessionKey = \sprintf(self::ACTIVE_PAYMENT_ID_KEY, $event->getId());
        $this->session->set($sessionKey, $payment->getId());

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

        /** @var TicketRepository $ticketRepository */
        $ticketRepository = $this->em->getRepository(Ticket::class);

        $ticket = $ticketRepository->findOneByUserAndEventWithPendingPayment($user, $event);

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
     * @param Event       $event
     * @param Ticket|null $ticket
     *
     * @return Payment|null $payment
     */
    public function getPendingPaymentIfAccess(Event $event, ?Ticket $ticket = null): ?Payment
    {
        $payment = null;
        $currentUser = $this->userService->getCurrentUser();

        $sessionKey = \sprintf(self::ACTIVE_PAYMENT_ID_KEY, $event->getId());
        if ($this->session->has($sessionKey)) {
            $paymentId = $this->session->get($sessionKey);
            /** @var PaymentRepository $paymentRepository */
            $paymentRepository = $this->em->getRepository(Payment::class);
            $payment = $paymentRepository->findPendingPaymentByIdForUser($paymentId, $currentUser);
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
     * @param Payment $payment
     * @param Event   $event
     *
     * @throws \Exception
     */
    private function addPromocodeFromSession(Payment $payment, Event $event): void
    {
        $promoCodes = $this->session->get(self::PROMO_CODE_SESSION_KEY, []);
        if (isset($promoCodes[$event->getSlug()])) {
            foreach ($payment->getTickets() as $ticket) {
                if (!$ticket->hasPromoCode()) {
                    $this->addPromoCodeForTicketByCode($promoCodes[$event->getSlug()], $event, $ticket, false);
                }
            }
        }
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
            $promoCode->clearTmpUsedCount();

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
