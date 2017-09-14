<?php

namespace Stfalcon\Bundle\EventBundle\Service;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Application\Bundle\UserBundle\Entity\User;
use Stfalcon\Bundle\EventBundle\Entity\Event;
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
}
