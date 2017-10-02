<?php

namespace Stfalcon\Bundle\EventBundle\Service;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Application\Bundle\UserBundle\Entity\User;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\DependencyInjection\Container;
use Stfalcon\Bundle\EventBundle\Entity\PromoCode;

/**
 * Сервис для работы с билетами
 */
class TicketService
{
    const CAN_BUY_TICKET = 'can buy ticket';
    const CAN_DOWNLOAD_TICKET = 'can download ticket';
    const CAN_WANNA_VISIT = 'can wanna visit';
    const WAIT_FOR_PAYMENT_RECEIVE = 'wit for payment receive';
    const PAID_FOR_ANOTHER = 'paid for another';
    const EVENT_DONE = 'event done';
    const EVENT_DEFAULT_STATE = 'event default state';

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
     * Check discount for ticket
     *
     * @param Ticket $ticket
     * @return bool
     */
    public function isMustBeDiscount($ticket)
    {
        $paidPayments = $this->em->getRepository('StfalconEventBundle:Payment')
            ->findPaidPaymentsForUser($ticket->getUser());
        return count($paidPayments) > 0 && $ticket->getEvent()->getUseDiscounts();
    }
    /**
     * Set Ticket Amount with recalculate discount
     *
     * @param Ticket $ticket
     * @param $amount
     * @param bool $isMustBeDiscount
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
     * Set the best (from promo code or standard discount) discount for ticket
     *
     * @param Ticket $ticket
     * @param PromoCode $promoCode
     * @param $discount
     *
     * @return Ticket
     */
    public function setTicketBestDiscount($ticket, $promoCode, $discount = -1)
    {
        if (-1 === $discount) {
            $paymentsConfig = $this->container->getParameter('stfalcon_event.config');
            $discount = (float)$paymentsConfig['discount'];
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
     * Set Ticket promo-code
     *
     * @param PromoCode $promoCode
     * @param Ticket $ticket
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
     * Set ticket discount
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
            ->setHasDiscount($ticket->getAmount() != $ticket->getAmountWithoutDiscount());
        $this->em->flush();

        return $ticket;
    }
    /**
     * Find ticket for event by current user
     *
     * @param Event $event
     *
     * @return Ticket|null
     */
    public function findTicketForEventByCurrentUser($event)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $ticket = null;
        if (is_object($user) && $user instanceof \FOS\UserBundle\Model\UserInterface) {
            // проверяем или у пользователя есть билет на этот ивент
            $ticket = $this->container->get('doctrine.orm.default_entity_manager')
                ->getRepository('StfalconEventBundle:Ticket')
                ->getTicketForEventByUser($event, $user);
        }

        return $ticket;
    }

    /**
     * Create ticket for User and Event
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
     * @param User $user
     * @param Event $event
     * @param string $position
     * @param TicketCost $ticketCost
     *
     * @return array
     */
    public function getTicketHtmlData($user, $event, $position, $ticketCost)
    {
        $result = [];
        $eventState = null;
        $ticket = $this->findTicketForEventByCurrentUser($event);
        /** @var Payment $payment */
        $payment = null;

        if ($user instanceof User) {
            $payment = $this->container->get('doctrine.orm.default_entity_manager')
                ->getRepository('StfalconEventBundle:Payment')
                ->findPaymentByUserAndEvent($user, $event);
        }

        $translator = $this->container->get('translator');
        $eventState = null;
        $isDiv = null;
        $data = null;
        $class = '';
        $href = null;
        $isMob = null;
        $caption = '';

        if ($event->isActiveAndFuture()) {

            if ($ticket && $ticket->isPaid()) {

                $eventState = self::CAN_DOWNLOAD_TICKET;

            } elseif ($ticket && !$event->getReceivePayments()) {

                $eventState = self::WAIT_FOR_PAYMENT_RECEIVE;

            } elseif ($payment && $payment->isPaid()) {

                $eventState = self::PAID_FOR_ANOTHER;

            } elseif (!$event->getReceivePayments()) {

                $eventState = self::CAN_WANNA_VISIT;

            } elseif (!$payment || ($payment && $payment->isPending())) {

                $eventState = self::CAN_BUY_TICKET;
            }
        } else {
            $eventState = self::EVENT_DONE;
        }

        $states =
            [
                'row' =>
                    [
                        self::CAN_DOWNLOAD_TICKET => 'event-row__btn btn btn--tertiary btn--sm',
                        self::PAID_FOR_ANOTHER => 'event-row__status',
                        self::EVENT_DONE => 'event-row__status',
                        self::EVENT_DEFAULT_STATE => 'event-row__btn btn btn--primary btn--sm',
                    ],
                'card' =>
                    [
                        self::CAN_DOWNLOAD_TICKET => 'btn btn--quaternary btn--sm event-card__btn',
                        self::PAID_FOR_ANOTHER => 'event-card__status',
                        self::EVENT_DONE => 'event-card__status',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--sm event-card__btn',
                    ],
                'event_header' =>
                    [
                        self::CAN_DOWNLOAD_TICKET => 'btn btn--quaternary btn--lg event-header__btn',
                        self::PAID_FOR_ANOTHER => 'event-header__status',
                        self::EVENT_DONE => 'event-header__status',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg event-header__btn',
                    ],
                'event_fix_header' =>
                    [
                        self::CAN_DOWNLOAD_TICKET => 'fix-event-header__download',
                        self::PAID_FOR_ANOTHER => 'fix-event-header__status',
                        self::EVENT_DONE => 'fix-event-header__status',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg fix-event-header__btn',
                    ],
                'event_fix_header_mob' =>
                    [
                        self::CAN_DOWNLOAD_TICKET => 'fix-event-header__download fix-event-header__download--mob',
                        self::PAID_FOR_ANOTHER => 'fix-event-header__status fix-event-header__status--mob',
                        self::EVENT_DONE => 'fix-event-header__status fix-event-header__status--mob',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg fix-event-header__btn fix-event-header__btn--mob',
                    ],
                'event_action_mob' =>
                    [
                        self::EVENT_DONE => 'event-action-mob__status',
                        self::CAN_DOWNLOAD_TICKET => 'btn btn--tertiary btn--lg event-action-mob__btn',
                        self::PAID_FOR_ANOTHER => 'event-action-mob__status',
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg event-action-mob__btn',
                    ],
                'price_block_mob' =>
                    [
                        self::EVENT_DEFAULT_STATE => 'btn btn--primary btn--lg cost__buy cost__buy--mob',
                    ],
                'price_block' =>
                    [
                        self::EVENT_DEFAULT_STATE =>'btn btn--primary btn--lg cost__buy',
                    ]
        ];

        $class = isset($states[$position][$eventState]) ? $states[$position][$eventState] : $states[$position][self::EVENT_DEFAULT_STATE];
        $isMob = in_array($position, ['event_fix_header_mob', 'price_block_mob']);

        if ($event->isActiveAndFuture()) {
            $data = $event->getSlug();
            if ($eventState === self::CAN_DOWNLOAD_TICKET) {
                $caption = $isMob ? $translator->trans('ticket.mob_status.download') : $translator->trans('ticket.status.download');
                $href = $this->container->get('router')->generate('event_ticket_download', ['event_slug' => $event->getSlug()]);

            } elseif ($eventState === self::WAIT_FOR_PAYMENT_RECEIVE) {
                $caption = $translator->trans('ticket.status.event.add');
                $isDiv = true;
            } elseif ($eventState === self::PAID_FOR_ANOTHER) {
                if ($isMob) {
                    $caption = $translator->trans('ticket.status.paid_mob');
                } else {
                    $caption = $translator->trans('ticket.status.paid');
                }

            } elseif ($eventState === self::CAN_WANNA_VISIT && (!$user || !$user->isEventInWants($event))) {
                $class .= ' set-modal-header add-wants-visit-event';
                $caption = $translator->trans('ticket.status.take_apart');

            } elseif ($eventState === self::CAN_WANNA_VISIT && $user->isEventInWants($event)) {
                $class .= ' set-modal-header sub-wants-visit-event';
                $caption = $translator->trans('ticket.status.not_take_apart');

            } elseif ($eventState === self::CAN_BUY_TICKET) {

                if ($isMob) {
                    $caption = $translator->trans('ticket.mob_status.pay');
                } elseif ($position === 'price_block') {
                    $caption = $translator->trans('ticket.status.pay_for').' '. $translator->trans('payment.price', ['%summ%' => number_format($ticketCost->getAmount(), 0,',',' ')]);
                    if ($ticketCost->getAltAmount()) {
                        $caption .= '<span class="cost__dollars">'.$ticketCost->getAltAmount().'</span>';
                    }
                } else {
                    $caption = $translator->trans('ticket.status.pay');
                }
                $class .=' get-payment';
            }
        } else {
            $isDiv = true;
            if ($isMob) {
                $caption = $translator->trans('ticket.status.event_done_mob');
            } else {
                $caption = $translator->trans('ticket.status.event_done');
            }
        }

        $result['class'] = $class;
        $result['caption'] = $caption;
        $result['href'] = $href;
        $result['isDiv'] = $isDiv;
        $result['data'] = $data;

        return $result;
    }
}
