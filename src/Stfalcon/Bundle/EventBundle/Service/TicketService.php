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

        $ticket = $this->findTicketForEventByCurrentUser($event);
        /** @var Payment $payment */
        $payment = null;

        if ($user instanceof User) {
            $payment = $this->container->get('doctrine.orm.default_entity_manager')
                ->getRepository('StfalconEventBundle:Payment')
                ->findPaymentByUserAndEvent($user, $event);
        }

        $getTicket = $ticket && $ticket->isPaid();
        $translator = $this->container->get('translator');

        $isDiv = null;
        $data = null;
        $class = '';
        $href = null;
        $isMob = null;
        $caption = '';
        if ($event->isActiveAndFuture()) {
            switch ($position) {
                case 'row':
                    $class = $getTicket ? 'event-row__download' :'event-row__btn btn btn--primary btn--sm';
                    break;
                case 'card':
                    $class = $getTicket ? 'event-card__download' : 'btn btn--primary btn--sm event-card__btn';
                    break;
                case 'event_header':
                    $class = $getTicket ? 'event-header__download' : 'btn btn--primary btn--lg event-header__btn';
                    break;
                case 'event_fix_header':
                    $class = $getTicket ? 'fix-event-header__download'
                        : 'btn btn--primary btn--lg fix-event-header__btn';
                    break;
                case 'event_fix_header_mob':
                    $class = $getTicket ? 'fix-event-header__download fix-event-header__download--mob'
                        : 'btn btn--primary btn--lg fix-event-header__btn fix-event-header__btn--mob';
                    $isMob = true;
                    break;
                case 'event_action_mob':
                    $class = 'btn btn--primary btn--lg event-action-mob__btn';
                    $isMob = true;
                    break;
                case 'price_block_mob':
                    $class = 'btn btn--primary btn--lg cost__buy cost__buy--mob';
                    $isMob = true;
                    break;
                case 'price_block':
                    $class = 'btn btn--primary btn--lg cost__buy';
                    break;
            }
            $data = $event->getSlug();
            if ($getTicket) {
                $caption = $isMob ? $translator->trans('ticket.mob_status.download') : $translator->trans('ticket.status.download');
                $href = $this->generateUrl('event_ticket_download', ['event_slug' => $event->getSlug()]);
            } elseif ($ticket && !$event->getReceivePayments()) {
                $caption = $translator->trans('ticket.status.event.add');
            } elseif ($payment && $payment->isPaid()) {
                $caption = $translator->trans('ticket.status.paid');
            } elseif (!$event->getReceivePayments() && (!$user || !$user->isEventInWants($event))) {
                $class .= ' set-modal-header add-wants-visit-event';
                $caption = $translator->trans('ticket.status.take_apart');
            } elseif (!$event->getReceivePayments() && $user->isEventInWants($event)) {
                $class .= ' set-modal-header sub-wants-visit-event';
                $caption = $translator->trans('ticket.status.not_take_apart');
            } elseif (!$payment || ($payment && $payment->isPending())) {
                if ($isMob) {
                    $caption = $translator->trans('ticket.mob_status.pay');
                } elseif ($position === 'price_block') {
                    $caption = $translator->trans('ticket.status.pay_for').' '. $translator->trans('payment.price', ['%summ%' => number_format($ticketCost->getAmount(), 0,',','')]);
                    if ($ticketCost->getAltAmount()) {
                        $caption .= '<span class="cost__dollars">'.$ticketCost->getAltAmount().'</span>';
                    }
                } else {
                    $caption = $translator->trans('ticket.status.pay');
                }
                $class .=' set-modal-header get-payment';
            }
        } else {
            $isDiv = true;
            $caption = $translator->trans('ticket.status.event_done');
            switch ($position) {
                case 'row':
                    $class = 'event-row__done';
                    break;
                case 'card':
                    $class = 'event-card__done';
                    break;
                case 'event_fix_header':
                    $class = 'fix-event-header__download';
                    break;
                case 'event_fix_header_mob':
                    $class = 'fix-event-header__download fix-event-header__download--mob';
                    break;
                default:
                    $class = 'event-header__download';
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
