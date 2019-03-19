<?php

namespace Application\Bundle\DefaultBundle\Service;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
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
        $eventCosts = $this->em->getRepository('ApplicationDefaultBundle:TicketCost')
            ->getEventEnabledTicketsCost($event);

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
}
