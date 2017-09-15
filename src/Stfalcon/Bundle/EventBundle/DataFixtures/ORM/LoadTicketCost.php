<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Application\Bundle\DefaultBundle\Entity\TicketCost;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadTicketCost extends AbstractFixture
{
    /**
     * Return fixture classes fixture is dependent on
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
        );
    }

    public function load(ObjectManager $manager)
    {
        $ticketCost = new TicketCost();

        $event1 = $this->getReference('event-zfday');
        $event2 = $this->getReference('event-not-active');

        $ticketCost->setName('early')
            ->setAmount(1000)
            ->setAltAmount('= 40$')
            ->setCount(2)
            ->setEnabled(true)
            ->setEvent($manager->merge($event1));
        $manager->persist($ticketCost);

        $ticketCost1 = new TicketCost();
        $ticketCost1->setName('standart')
            ->setAmount(3000)
            ->setAltAmount('= 120$')
            ->setUnlimited(true)
            ->setEnabled(true)
            ->setEvent($manager->merge($event1));
        $manager->persist($ticketCost1);

        $ticketCost2 = new TicketCost();
        $ticketCost2->setName('next')
            ->setAmount(2000)
            ->setAltAmount('= 80$')
            ->setCount(1)
            ->setEnabled(true)
            ->setEvent($manager->merge($event1));
        $manager->persist($ticketCost2);

        $ticketCost3 = new TicketCost();
        $ticketCost3->setName('standart')
            ->setAmount(3000)
            ->setAltAmount('= 120$')
            ->setUnlimited(true)
            ->setEnabled(true)
            ->setEvent($manager->merge($event2));
        $manager->persist($ticketCost3);

        $ticketCost4 = new TicketCost();
        $ticketCost4->setName('next')
            ->setAmount(2000)
            ->setAltAmount('= 80$')
            ->setCount(1)
            ->setEnabled(true)
            ->setEvent($manager->merge($event2));
        $manager->persist($ticketCost4);
    }
}