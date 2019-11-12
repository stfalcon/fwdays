<?php

namespace Application\Bundle\DefaultBundle\Service;

use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\PromoCode;
use Application\Bundle\DefaultBundle\Entity\Ticket;
use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Application\Bundle\DefaultBundle\Entity\User;
use Application\Bundle\DefaultBundle\Repository\PaymentRepository;
use Application\Bundle\DefaultBundle\Repository\TicketCostRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Сервис для работы с билетами.
 */
class TicketService
{
    const CAN_BUY_TICKET = 'can buy ticket';
    const CAN_DOWNLOAD_TICKET = 'can download ticket';
    const TICKETS_SOLD_OUT = 'all tickets sold out';
    const CAN_WANNA_VISIT = 'can wanna visit';
    const WAIT_FOR_PAYMENT_RECEIVE = 'wit for payment receive';
    const PAID_FOR_ANOTHER = 'paid for another';
    const PAID_IS_RETURNED = 'paid is return';
    const EVENT_DONE = 'event done';
    const EVENT_DEFAULT_STATE = 'event default state';

    /** @var EntityManager */
    private $em;

    /** @var array */
    private $paymentsConfig;

    /** @var TranslatorInterface */
    private $translator;

    /** @var RouterInterface */
    private $router;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * TicketService constructor.
     *
     * @param EntityManager         $em
     * @param array                 $paymentsConfig
     * @param TranslatorInterface   $translator
     * @param RouterInterface       $router
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct($em, $paymentsConfig, $translator, $router, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->paymentsConfig = $paymentsConfig;
        $this->translator = $translator;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Check discount for ticket.
     *
     * @param Ticket $ticket
     *
     * @return bool
     */
    public function isMustBeDiscount($ticket)
    {
        $event = $ticket->getEvent();
        $user = $ticket->getUser();

        if (!$event->getUseDiscounts()) {
            return false;
        }

        $paidPayments = $this->em->getRepository('ApplicationDefaultBundle:Payment')
            ->findPaidPaymentsForUser($user);

        return \count($paidPayments) > 0;
    }

    /**
     * Set Ticket Amount with recalculate discount.
     *
     * @param Ticket     $ticket
     * @param float      $amount
     * @param bool       $isMustBeDiscount
     * @param TicketCost $currentTicketCost
     */
    public function setTicketAmount($ticket, $amount, $isMustBeDiscount, $currentTicketCost)
    {
        $ticket->setAmountWithoutDiscount($amount);
        $ticket->setAmount($amount);
        $ticket->setTicketCost($currentTicketCost);
        /** -1 flag means you need to discount in the configuration */
        $discount = $isMustBeDiscount ? -1 : 0;
        $this->setTicketBestDiscount($ticket, $ticket->getPromoCode(), $discount);
    }

    /**
     * Set the best (from promo code or standard discount) discount for ticket.
     *
     * @param Ticket    $ticket
     * @param PromoCode $promoCode
     * @param float|int $discount
     *
     * @return string
     */
    public function setTicketBestDiscount($ticket, $promoCode, $discount = -1): string
    {
        if (-1 === $discount) {
            $discount = (float) $this->paymentsConfig['discount'];
        }

        if ($promoCode instanceof PromoCode && $promoCode->getDiscountAmount() / 100 > $discount) {
            $this->setTicketPromoCode($ticket, $promoCode);
            $result = PromoCode::PROMOCODE_APPLIED;
        } else {
            $ticket->setPromoCode(null);
            $this->setTicketDiscount($ticket, $discount);
            $result = PromoCode::PROMOCODE_LOW_THAN_DISCOUNT;
        }

        return $result;
    }

    /**
     * Set Ticket promo-code.
     *
     * @param Ticket    $ticket
     * @param PromoCode $promoCode
     *
     * @return Ticket
     */
    public function setTicketPromoCode($ticket, $promoCode)
    {
        $ticket->setPromoCode($promoCode);
        $this->setTicketDiscount($ticket, $promoCode->getDiscountAmount() / 100);
        $promoCode->incTmpUsedCount();

        return $ticket;
    }

    /**
     * Set ticket discount.
     *
     * @param Ticket $ticket
     * @param float  $discount
     *
     * @return Ticket
     */
    public function setTicketDiscount($ticket, $discount)
    {
        $amountWithDiscount = $ticket->getAmountWithoutDiscount() - ($ticket->getAmountWithoutDiscount() * $discount);
        $ticket
            ->setAmount($amountWithDiscount)
            ->setHasDiscount($ticket->getAmount() !== $ticket->getAmountWithoutDiscount());
        $this->em->flush();

        return $ticket;
    }

    /**
     * Create ticket for User and Event.
     *
     * @param Event $event
     * @param User  $user
     *
     * @return Ticket
     */
    public function createTicket(Event $event, User $user): Ticket
    {
        $ticket = (new Ticket())
            ->setEvent($event)
            ->setUser($user)
            ->setAmountWithoutDiscount($event->getCost())
            ->setAmount($event->getCost());
        $this->em->persist($ticket);
        $this->em->flush();

        return $ticket;
    }

    /**
     * @param Event           $event
     * @param string          $position
     * @param TicketCost|null $ticketCost
     *
     * @return array
     */
    public function getTicketHtmlData($event, $position, $ticketCost)
    {
        $ticket = null;
        /** @var Payment $payment */
        $payment = null;

        $token = $this->tokenStorage->getToken();

        $user = $token instanceof TokenInterface && $token->getUser() instanceof User ? $token->getUser() : null;
        if ($user instanceof User) {
            /** @var PaymentRepository $paymentRepository */
            $paymentRepository = $this->em->getRepository('ApplicationDefaultBundle:Payment');
            $payment = $paymentRepository->findPaymentByUserAndEvent($user, $event);

            $ticket = $this->em->getRepository('ApplicationDefaultBundle:Ticket')
                ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);
        }

        $eventState = null;
        $ticketState = null;
        $isDiv = null;
        $data = null;
        $ticketClass = '';
        $href = null;
        $caption = '';
        $ticketCaption = '';
        $downloadUrl = false;

        if ($event->isActiveAndFuture()) {
            if ($ticket && $ticket->isPaid()) {
                $ticketState = self::CAN_DOWNLOAD_TICKET;
            }
            if ($ticket && !$event->getReceivePayments()) {
                $eventState = self::WAIT_FOR_PAYMENT_RECEIVE;
            } elseif ($payment && $payment->isPaid()) {
                $eventState = self::PAID_FOR_ANOTHER;
//            } elseif ($event->getTicketsCost()->count() > 0 && !$this->ticketCostService->isEventHaveTickets($event)) {
//                $eventState = self::TICKETS_SOLD_OUT;
            } elseif (!$event->getReceivePayments()) {
                $eventState = self::CAN_WANNA_VISIT;
            } elseif (!$event->isHaveFreeTickets()) {
                $eventState = self::TICKETS_SOLD_OUT;
            } elseif (!$payment || ($payment && $payment->isPending())) {
                $eventState = self::CAN_BUY_TICKET;
            } elseif ($payment && $payment->isReturned()) {
                $eventState = self::PAID_IS_RETURNED;
            }
        } else {
            $eventState = self::EVENT_DONE;
        }

        $states =
            [
                'row' => [
                        self::CAN_DOWNLOAD_TICKET => '',
                        self::EVENT_DONE => 'event-row__status',
                        self::EVENT_DEFAULT_STATE => 'event-row__btn btn btn--primary btn--sm',
                    ],
                'card' => [
                        self::CAN_DOWNLOAD_TICKET => 'event-card__download',
                        self::EVENT_DONE => 'event-card__status',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--sm event-card__btn',
                    ],
                'event_header' => [
                        self::CAN_DOWNLOAD_TICKET => 'event-card__download',
                        self::EVENT_DONE => 'event-header__status',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg event-header__btn',
                    ],
                'event_fix_header' => [
                        self::CAN_DOWNLOAD_TICKET => '',
                        self::EVENT_DONE => 'fix-event-header__status',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg fix-event-header__btn',
                    ],
                'event_fix_header_mob' => [
                        self::CAN_DOWNLOAD_TICKET => '',
                        self::EVENT_DONE => 'fix-event-header__status fix-event-header__status--mob',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg fix-event-header__btn fix-event-header__btn--mob',
                    ],
                'event_action_mob' => [
                        self::CAN_DOWNLOAD_TICKET => 'event-action-mob__download',
                        self::EVENT_DONE => 'event-action-mob__status',
                        self::EVENT_DEFAULT_STATE => 'go-to-block btn btn--primary btn--lg event-action-mob__btn',
                    ],
                'price_block_mob' => [
                        self::CAN_DOWNLOAD_TICKET => '',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg cost__buy cost__buy--mob',
                    ],
                'price_block' => [
                        self::CAN_DOWNLOAD_TICKET => '',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg cost__buy',
                    ],
            ];

        if (\in_array(
            $eventState,
            [
                self::EVENT_DONE,
                self::PAID_IS_RETURNED,
                self::PAID_FOR_ANOTHER,
                self::TICKETS_SOLD_OUT,
            ],
            true
        )) {
            $class = $states[$position][self::EVENT_DONE] ?? $states[$position][self::EVENT_DEFAULT_STATE];
        } else {
            $class = $states[$position][$eventState] ?? $states[$position][self::EVENT_DEFAULT_STATE];
        }

        $isMob = \in_array($position, ['event_fix_header_mob', 'price_block_mob']);

        if ($event->isActiveAndFuture()) {
            $data = $event->getSlug();

            if (self::CAN_DOWNLOAD_TICKET === $ticketState) {
                $ticketCaption = $this->translator->trans('ticket.status.download');
                $ticketClass = $states[$position][$ticketState] ?? $states[$position][self::EVENT_DEFAULT_STATE];
                if (!empty($ticketClass)) {
                    $downloadUrl = $this->router->generate('event_ticket_download', ['eventSlug' => $event->getSlug()]);
                }
            }

            if (self::WAIT_FOR_PAYMENT_RECEIVE === $eventState) {
                $caption = $this->translator->trans('ticket.status.event.add');
                $isDiv = true;
            } elseif (self::PAID_FOR_ANOTHER === $eventState) {
                if ($isMob) {
                    $caption = $this->translator->trans('ticket.status.paid_mob');
                } else {
                    $caption = $this->translator->trans('ticket.status.paid');
                }
            } elseif (self::TICKETS_SOLD_OUT === $eventState) {
                $isDiv = true;
                if ($isMob) {
                    $caption = $this->translator->trans('ticket.status.sold_mob');
                } else {
                    $caption = $this->translator->trans('ticket.status.sold');
                }
            } elseif (self::CAN_WANNA_VISIT === $eventState && (!$user || !$user->isEventInWants($event))) {
                $class .= ' add-wants-visit-event';
                $caption = $this->translator->trans('ticket.status.take_apart');
            } elseif (self::CAN_WANNA_VISIT === $eventState && ($user && $user->isEventInWants($event))) {
                $class .= ' sub-wants-visit-event';
                $caption = $this->translator->trans('ticket.status.not_take_apart');
            } elseif (self::CAN_BUY_TICKET === $eventState) {
                if ($isMob) {
                    $caption = $this->translator->trans('ticket.mob_status.pay');
                } elseif ('price_block' === $position) {
                    $amount = $ticketCost ? $ticketCost->getAmount() : $event->getBiggestTicketCost()->getAmount();
                    $altAmount = $ticketCost ? '≈$'.number_format($ticketCost->getAltAmount(), 0, ',', ' ') : '';
                    $caption = $this->translator->trans('ticket.status.pay_for').' '.$this->translator
                            ->trans(
                                'payment.price',
                                [
                                    '%summ%' => number_format($amount, 0, ',', ' '),
                                ]
                            );
                    if ($ticketCost && $ticketCost->getAltAmount()) {
                        $caption .= '<span class="cost__dollars">'.$altAmount.'</span>';
                    }
                } else {
                    $caption = $this->translator->trans('ticket.status.pay');
                }
                $href = $this->router->generate('event_pay', ['slug' => $event->getSlug()]);
            } elseif (self::PAID_IS_RETURNED === $eventState) {
                if ($isMob) {
                    $caption = $this->translator->trans('ticket.status.payment_returned_mob');
                } else {
                    $caption = $this->translator->trans('ticket.status.payment_returned');
                }
            }
        } else {
            $isDiv = true;
            if ($isMob) {
                $caption = $this->translator->trans('ticket.status.event_done_mob');
            } else {
                $caption = $this->translator->trans('ticket.status.event_done');
            }
        }

        return
            [
                'class' => $class,
                'caption' => $caption,
                'ticket_caption' => $ticketCaption,
                'ticket_class' => $ticketClass,
                'href' => $href,
                'isDiv' => $isDiv,
                'data' => $data,
                'id' => $position.'-'.$data,
                'download_url' => $downloadUrl,
            ];
    }

    /**
     * @param Event $event
     *
     * @return TicketCost|null
     */
    public function getCurrentEventTicketCost($event)
    {
        /** @var TicketCostRepository $ticketCostRepository */
        $ticketCostRepository = $this->em->getRepository('ApplicationDefaultBundle:TicketCost');
        $eventCosts = $ticketCostRepository->getEventEnabledTicketsCost($event);

        $currentTicketCost = null;

        /** @var TicketCost $cost */
        foreach ($eventCosts as $cost) {
            if ($cost->isHaveTemporaryCount()) {
                $currentTicketCost = $cost;
                break;
            }
        }

        return $currentTicketCost;
    }

    /**
     * @param Event $event
     *
     * @return int
     */
    public function getEventFreeTicketCount($event)
    {
        /** @var TicketCostRepository $ticketCostRepository */
        $ticketCostRepository = $this->em->getRepository('ApplicationDefaultBundle:TicketCost');
        $eventCosts = $ticketCostRepository->getEventEnabledTicketsCost($event);
        $count = 0;
        /** @var TicketCost $cost */
        foreach ($eventCosts as $cost) {
            if (!$cost->isUnlimited()) {
                $count += $cost->getCount() - $cost->getSoldCount();
            }
        }

        return $count;
    }

    /**
     * @param User|null $user
     * @param Event     $event
     *
     * @return bool
     */
    public function isUserHasPaidTicketForEvent(?User $user, Event $event): bool
    {
        if (!$user instanceof User) {
            return false;
        }
        /** @var Ticket|null $ticket */
        $ticket = $this->em->getRepository('ApplicationDefaultBundle:Ticket')
            ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);

        return $ticket instanceof Ticket && $ticket->isPaid();
    }

    /**
     * @param User|null $user
     * @param Ticket    $ticket
     */
    public function setNewUserToTicket(?User $user, Ticket $ticket): void
    {
        $oldUser = $ticket->getUser();
        if ($user instanceof User && !$user->isEqualTo($oldUser)) {
            $user->addTicket($ticket);
        }
    }
}
