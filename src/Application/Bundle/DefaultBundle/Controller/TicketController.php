<?php

namespace Application\Bundle\DefaultBundle\Controller;

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
     *
     * @return Response
     */
    public function statusAction(Event $event, $position = 'card')
    {
        $ticket = $this->container->get('stfalcon_event.ticket.service')
            ->findTicketForEventByCurrentUser($event);
        /** @var Payment $payment */
        $payment = null;
        /* @var  User $user */
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if ($user instanceof User) {
            $payment = $this->container->get('doctrine.orm.default_entity_manager')
                ->getRepository('StfalconEventBundle:Payment')
                ->findPaymentByUserAndEvent($user, $event);
        }

        return $this->render('@ApplicationDefault/Redesign/event.ticket.status.html.twig', [
            'user' => $user,
            'event'  => $event,
            'ticket' => $ticket,
            'payment' => $payment,
            'position' => $position,
        ]);
    }
}