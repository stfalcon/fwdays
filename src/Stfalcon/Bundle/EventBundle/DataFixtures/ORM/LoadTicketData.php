<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
Doctrine\Common\DataFixtures\OrderedFixtureInterface,
Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\Ticket;

/**
 * LoadTicketData Class
 */
class LoadTicketData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $ticket = new Ticket(
            $manager->merge($this->getReference('event-zfday')),
            $manager->merge($this->getReference('user-default'))
        );

        $manager->persist($ticket);
        $this->addReference('ticket-1', $ticket);

        unset($ticket);

        $ticket = new Ticket(
            $manager->merge($this->getReference('event-phpday')),
            $manager->merge($this->getReference('user-default'))
        );

        $manager->persist($ticket);
        $this->addReference('ticket-2', $ticket);

        unset($ticket);

        $ticket = new Ticket(
            $manager->merge($this->getReference('event-not-active')),
            $manager->merge($this->getReference('user-default'))
        );

        $manager->persist($ticket);
        $this->addReference('ticket-3', $ticket);

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}
