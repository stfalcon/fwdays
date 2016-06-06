<?php

namespace Stfalcon\Bundle\EventBundle\Service;

use Application\Bundle\UserBundle\Entity\User;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;
use Symfony\Component\DependencyInjection\Container;

/**
 * Сервис для работы с билетами
 */
class TicketService
{

    /**
     * @var Container $container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
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
     * @param Event $event
     * @param User  $user
     *
     * @return Ticket
     */
    public function createTicket($event, $user)
    {
        // @todo це ще треба передивитись і поправити

        $em= $this->container->get('doctrine.orm.default_entity_manager');
        // Вытягиваем скидку из конфига
        $paymentsConfig = $this->container->getParameter('stfalcon_event.config');
        $discount = (float) $paymentsConfig['discount'];

        $ticket = new Ticket();
        $ticket->setEvent($event);
        $ticket->setUser($user);
        $ticket->setAmountWithoutDiscount($event->getCost());
        $paidPayments = $em->getRepository('StfalconEventBundle:Payment')
            ->findPaidPaymentsForUser($user);

        // если пользователь имеет оплаченные события,
        // то он получает скидку (если для события разрешена такая скидка)
        if (count($paidPayments) > 0 && $event->getUseDiscounts()) {
            $cost = $event->getCost() - $event->getCost() * $discount;
            $hasDiscount = true;
        } else {
            $cost = $event->getCost();
            $hasDiscount = false;
        }
        $ticket->setAmount($cost);
        $ticket->setHasDiscount($hasDiscount);

        $em->persist($ticket);
        $em->flush();

        return $ticket;
    }
}
