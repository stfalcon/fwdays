<?php

namespace Stfalcon\Bundle\EventBundle\Service;

use Stfalcon\Bundle\EventBundle\Entity\Ticket,
    Stfalcon\Bundle\EventBundle\Entity\Event,
    Stfalcon\Bundle\EventBundle\Entity\PromoCode,
    Application\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\ORM\EntityManager;

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
     * @var EntityManager $em
     */
    protected $em;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine.orm.default_entity_manager');
    }

    /**
     * Find ticket for event by current user
     *
     * @param Event $event
     *
     * @return Ticket|null
     */
    public function findTicketForEventByCurrentUser(Event $event) {
        // @todo глянути де юзається метод. може простіше лишити findTicket, а юзера передавати в аргументах
        $user = $this->container->get('security.context')->getToken()->getUser();

        return $this->findTicket($event, $user);
    }

    /**
     * Find ticket for event by user
     *
     * @param Event $event
     * @param User $user
     *
     * @return Ticket|null
     */
    public function findTicket(Event $event, User $user) {
        return $this->em->getRepository('StfalconEventBundle:Ticket')
            ->getTicketForEventByUser($event, $user);
    }

    /**
     * Создать билет на событие для пользователя
     *
     * @param Event $event
     * @param User $user
     *
     * @return Ticket
     */
    public function createTicket(Event $event, User $user)
    {
        // если билет на событие уже есть, тогда что-то не то
        if ($ticket = $this->findTicket($event, $user)) {
            throw new \Exception('У пользователя ' . $user->getFullname() . ' уже есть билет на участие в ' . $event->getName());
        }

        $ticket = new Ticket();
        $ticket->setEvent($event);
        $ticket->setUser($user);
        $ticket->setCost($event->getCost());
        $ticket->applyDiscount($this->getDiscount($event, $user));

        $this->em->persist($ticket);
        $this->em->flush();

        return $ticket;
    }

    /**
     * Расчитываем какую скидку на участие в событии можно сделать для пользователя
     *
     * @param Event $event
     * @param User $user
     * @return int
     */
    private function getDiscount(Event $event, User $user)
    {
        // если у события вобще отключены скидки, тогда даем нулевую скидку
        if (!$event->getUseDiscounts()) {
            return 0;
        }

        // если есть оплаченные платежи, тогда даем скидку для постоянных участников
        $paidPayments = $this->em->getRepository('StfalconEventBundle:Payment')
                ->findPaidPaymentsForUser($user);
        if (count($paidPayments) > 0) {
            // процент скидки тянем с конфига
            $paymentsConfig = $this->container->getParameter('stfalcon_event.config');
            return (int) $paymentsConfig['discount'];
        }

        return 0;
    }

    public function setPromocode(Ticket $ticket, PromoCode $promocode)
    {
        if ($promocode) {
            $ticket->setPromoCode($promocode);
            $this->em->flush();

            return true;
        }

        return false;
    }
}