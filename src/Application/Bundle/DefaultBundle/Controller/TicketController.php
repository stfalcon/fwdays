<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Application\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TicketController  extends Controller
{
    /**
     * Show event ticket status (for current user)
     *
     * @param Event $event
     * @param string $position
     * @param TicketCost $ticketCost
     *
     * @return Response
     */
    public function statusAction(Event $event, $position = 'card', TicketCost $ticketCost = null)
    {
        $ticket = $this->container->get('stfalcon_event.ticket.service')
            ->findTicketForEventByCurrentUser($event);
        /** @var Payment $payment */
        $payment = null;
        /* @var  User $user */
        $user = $this->getUser();
        if ($user instanceof User) {
            $payment = $this->container->get('doctrine.orm.default_entity_manager')
                ->getRepository('StfalconEventBundle:Payment')
                ->findPaymentByUserAndEvent($user, $event);
        }

        $getTicket = $ticket && $ticket->isPaid();
        $translator = $this->get('translator');

        $isDiv = null;
        $data = null;
        $class = '';
        $href = null;
        $isMob = null;
        $caption = '';
        if ($event->isActiveAndFuture() ) {
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
            } elseif (!$user && $event->getReceivePayments()) {
                if ($isMob) {
                    $caption = $translator->trans('ticket.mob_status.pay');
                } elseif ($position === 'price_block') {
                    $caption = $translator->trans('ticket.status.pay_for').' '. $translator->trans('payment.price', ['%summ%' => $ticketCost->getAmount()]);
                    if ($ticketCost->getAltAmount()) {
                        $caption .= '<span class="cost__dollars">'.$ticketCost->getAltAmount().'</span>';
                    }
                } else {
                    $caption = $translator->trans('ticket.status.pay');
                }

                    ($position === 'price_block' ?
                        : $translator->trans('ticket.status.pay'));

                //$href = "#modal-payment";
                $class .=' set-modal-header get-payment';
            }  elseif ($ticket && !$event->getReceivePayments()) {
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
                $caption = $isMob ? $translator->trans('ticket.mob_status.pay') : $translator->trans('ticket.status.pay');
                //$href = "#modal-payment";
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

        return $this->render('@ApplicationDefault/Redesign/event.ticket.status.html.twig', [
            'class'   => $class,
            'caption' => $caption,
            'href'    => $href,
            'isDiv'   => $isDiv,
            'data'    => $data,
        ]);
    }
}
