<?php

namespace Application\Bundle\DefaultBundle\Service;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Application\Bundle\DefaultBundle\Repository\TicketCostRepository;
use Doctrine\ORM\EntityManager;
use Stfalcon\Bundle\EventBundle\Entity\Event;

/**
 * Class TicketCostService.
 */
class TicketCostService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * TicketCostService constructor.
     *
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * @param Event $event
     *
     * @return TicketCost
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
}
