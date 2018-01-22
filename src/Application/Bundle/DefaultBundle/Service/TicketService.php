<?php

namespace Application\Bundle\DefaultBundle\Service;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Application\Bundle\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Stfalcon\Bundle\EventBundle\Entity\PromoCode;

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
    protected $em;

    protected $paymentsConfig;

    protected $translator;

    protected $router;

    /** @var TicketCostService */
    protected $ticketCostService;

    /**
     * TicketService constructor.
     *
     * @param EntityManager $em
     * @param array         $paymentsConfig
     * @param $translator
     * @param $router
     * @param TicketCostService $ticketCostService
     */
    public function __construct($em, $paymentsConfig, $translator, $router, $ticketCostService)
    {
        $this->em = $em;
        $this->paymentsConfig = $paymentsConfig;
        $this->translator = $translator;
        $this->router = $router;
        $this->ticketCostService = $ticketCostService;
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
        $paidPayments = $this->em->getRepository('StfalconEventBundle:Payment')
            ->findPaidPaymentsForUser($ticket->getUser());

        if (0 === count($paidPayments)) {
            $paidPayments = $this->em->getRepository('StfalconEventBundle:Payment')
                ->findPaidPaymentsForUserInPayment($ticket->getUser());
        }

        return count($paidPayments) > 0 && $ticket->getEvent()->getUseDiscounts();
    }

    /**
     * Set Ticket Amount with recalculate discount.
     *
     * @param Ticket $ticket
     * @param $amount
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
     * @param $discount
     *
     * @return Ticket
     */
    public function setTicketBestDiscount($ticket, $promoCode, $discount = -1)
    {
        if (-1 === $discount) {
            $discount = (float) $this->paymentsConfig['discount'];
        }
        if ($promoCode instanceof PromoCode && $promoCode->getDiscountAmount() / 100 > $discount) {
            $this->setTicketPromoCode($ticket, $promoCode);
        } else {
            $ticket->setPromoCode(null);
            $this->setTicketDiscount($ticket, $discount);
        }

        return $ticket;
    }

    /**
     * Set Ticket promo-code.
     *
     * @param PromoCode $promoCode
     * @param Ticket    $ticket
     *
     * @return Ticket
     */
    public function setTicketPromoCode($ticket, $promoCode)
    {
        $ticket->setPromoCode($promoCode);
        $this->setTicketDiscount($ticket, $promoCode->getDiscountAmount() / 100);

        return $ticket;
    }

    /**
     * Set ticket discount.
     *
     * @param $discount
     * @param Ticket $ticket
     *
     * @return Ticket
     */
    public function setTicketDiscount($ticket, $discount)
    {
        $amountWithDiscount = $ticket->getAmountWithoutDiscount() - ($ticket->getAmountWithoutDiscount() * $discount);
        $ticket
            ->setAmount($amountWithDiscount)
            ->setHasDiscount((float) $ticket->getAmount() !== (float) $ticket->getAmountWithoutDiscount());
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
    public function createTicket($event, $user)
    {
        $ticket = new Ticket();
        $ticket->setEvent($event);
        $ticket->setUser($user);
        $ticket->setAmountWithoutDiscount($event->getCost());
        $ticket->setAmount($event->getCost());
        $this->em->persist($ticket);
        $this->em->flush();

        return $ticket;
    }

    /**
     * @param User       $user
     * @param Event      $event
     * @param string     $position
     * @param TicketCost $ticketCost
     *
     * @return array
     */
    public function getTicketHtmlData($user, $event, $position, $ticketCost, $local = 'uk')
    {
        $eventState = null;
        $ticket = null;
        /** @var Payment $payment */
        $payment = null;

        if ($user instanceof User) {
            $payment = $this->em
                ->getRepository('StfalconEventBundle:Payment')
                ->findPaymentByUserAndEvent($user, $event);

            $ticket = $this->em->getRepository('StfalconEventBundle:Ticket')
                ->findOneBy(['event' => $event->getId(), 'user' => $user->getId()]);
        }

        $eventState = null;
        $isDiv = null;
        $data = null;
        $class = '';
        $href = null;
        $isMob = null;
        $caption = '';
        $onClick = null;

        if ($event->isActiveAndFuture()) {
            if ($ticket && $ticket->isPaid()) {
                $eventState = self::CAN_DOWNLOAD_TICKET;
            } elseif ($ticket && !$event->getReceivePayments()) {
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
                        self::CAN_DOWNLOAD_TICKET => 'event-row__btn btn btn--tertiary btn--sm',
                        self::EVENT_DONE => 'event-row__status',
                        self::EVENT_DEFAULT_STATE => 'event-row__btn btn btn--primary btn--sm',
                    ],
                'card' => [
                        self::CAN_DOWNLOAD_TICKET => 'btn btn--quaternary btn--sm event-card__btn',
                        self::EVENT_DONE => 'event-card__status',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--sm event-card__btn',
                    ],
                'event_header' => [
                        self::CAN_DOWNLOAD_TICKET => 'btn btn--quaternary btn--lg event-header__btn',
                        self::EVENT_DONE => 'event-header__status',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg event-header__btn',
                    ],
                'event_fix_header' => [
                        self::CAN_DOWNLOAD_TICKET => 'fix-event-header__download',
                        self::EVENT_DONE => 'fix-event-header__status',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg fix-event-header__btn',
                    ],
                'event_fix_header_mob' => [
                        self::CAN_DOWNLOAD_TICKET => 'fix-event-header__download fix-event-header__download--mob',
                        self::EVENT_DONE => 'fix-event-header__status fix-event-header__status--mob',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg fix-event-header__btn fix-event-header__btn--mob',
                    ],
                'event_action_mob' => [
                        self::EVENT_DONE => 'event-action-mob__status',
                        self::CAN_DOWNLOAD_TICKET => 'btn btn--tertiary btn--lg event-action-mob__btn',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg event-action-mob__btn',
                    ],
                'price_block_mob' => [
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg cost__buy cost__buy--mob',
                    ],
                'price_block' => [
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg cost__buy',
                    ],
            ];

        if (self::CAN_BUY_TICKET === $eventState) {
            $addUserSign = $user instanceof User ? '_user' : '';
            $mainGaPart = "ga('send', 'button', 'buy',";
            switch ($position) {
                case 'row':
                case 'card':
                    $onClick = $mainGaPart." 'main".$addUserSign."')";
                    break;
                case 'event_header':
                case 'event_fix_header':
                    $onClick = $mainGaPart." 'event".$addUserSign."')";
                    break;
                case 'event_fix_header_mob':
                case 'event_action_mob':
                    $onClick = $mainGaPart." 'event_mob".$addUserSign."')";
                    break;
                case 'price_block_mob':
                    $onClick = $mainGaPart." 'event_pay_mob".$addUserSign."')";
                    break;
                case 'price_block':
                    $onClick = $mainGaPart." 'event_pay".$addUserSign."')";
                    break;
            }
        }

        if (in_array(
            $eventState,
            [
                self::EVENT_DONE,
                self::PAID_IS_RETURNED,
                self::PAID_FOR_ANOTHER,
                self::TICKETS_SOLD_OUT,
            ]
        )) {
            $class = isset($states[$position][self::EVENT_DONE]) ? $states[$position][self::EVENT_DONE]
                : $states[$position][self::EVENT_DEFAULT_STATE];
        } else {
            $class = isset($states[$position][$eventState]) ? $states[$position][$eventState]
                : $states[$position][self::EVENT_DEFAULT_STATE];
        }

        $isMob = in_array($position, ['event_fix_header_mob', 'price_block_mob']);

        if ($event->isActiveAndFuture()) {
            $data = $event->getSlug();

            if (self::CAN_DOWNLOAD_TICKET === $eventState) {
                $caption = $isMob ? $this->translator->trans('ticket.mob_status.download')
                    : $this->translator->trans('ticket.status.download');
                $href = $this->router->generate('event_ticket_download', ['eventSlug' => $event->getSlug()]);
            } elseif (self::WAIT_FOR_PAYMENT_RECEIVE === $eventState) {
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
                $class .= ' set-modal-header add-wants-visit-event';
                $caption = $this->translator->trans('ticket.status.take_apart');
            } elseif (self::CAN_WANNA_VISIT === $eventState && $user->isEventInWants($event)) {
                $class .= ' set-modal-header sub-wants-visit-event';
                $caption = $this->translator->trans('ticket.status.not_take_apart');
            } elseif (self::CAN_BUY_TICKET === $eventState) {
                if ($isMob) {
                    $caption = $this->translator->trans('ticket.mob_status.pay');
                } elseif ('price_block' === $position) {
                    if ('uk' === $local) {
                        $amount = $ticketCost ? $ticketCost->getAmount() : $event->getBiggestTicketCost();
                        $altAmount = '≈$'.number_format($ticketCost->getAltAmount(), 0, ',', ' ');
                    } else {
                        $amount = $ticketCost->getAltAmount();
                        $altAmount = $ticketCost ? $ticketCost->getAmount() : $event->getBiggestTicketCost();
                        $altAmount = '≈'.number_format($altAmount, 0, ',', ' ').' UAH';
                    }
                    $caption = $this->translator->trans(
                        'ticket.status.pay_for').' '.
                        $this->translator->trans(
                            'payment.price.multi',
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
                $class .= ' get-payment';
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

        $result =
            [
                'class' => $class,
                'caption' => $caption,
                'href' => $href,
                'isDiv' => $isDiv,
                'data' => $data,
                'onClick' => $onClick,
            ];

        return $result;
    }
}
