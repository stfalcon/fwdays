<?php

namespace App\DataFixtures\ORM;

use App\Entity\TicketCost;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class LoadTicketCostData.
 */
class LoadTicketCostData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Return fixture classes fixture is dependent on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadEventData::class,
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $ticketCost = new TicketCost();

        $eventJsDay = $manager->merge($this->getReference('event-jsday2018'));
        $eventPHPDay2017 = $manager->merge($this->getReference('event-phpday2017'));

        $ticketCost->setName('early')
            ->setAmount(1000)
            ->setAltAmount(40)
            ->setCount(50)
            ->setEnabled(true)
            ->setType(TicketCost::TYPE_STANDARD)
            ->setEvent($eventJsDay);
        $manager->persist($ticketCost);

        $ticketCost1 = new TicketCost();
        $ticketCost1->setName('standart')
            ->setAmount(3000)
            ->setAltAmount(120)
            ->setUnlimited(true)
            ->setEnabled(true)
            ->setType(TicketCost::TYPE_STANDARD)
            ->setEvent($eventJsDay);
        $manager->persist($ticketCost1);

        $ticketCost2 = new TicketCost();
        $ticketCost2->setName('next')
            ->setAmount(2000)
            ->setAltAmount(80)
            ->setCount(50)
            ->setEnabled(true)
            ->setType(TicketCost::TYPE_STANDARD)
            ->setEvent($eventJsDay);
        $manager->persist($ticketCost2);

        $ticketCost3 = new TicketCost();
        $ticketCost3->setName('standart')
            ->setAmount(3000)
            ->setAltAmount(120)
            ->setUnlimited(true)
            ->setEnabled(true)
            ->setType(TicketCost::TYPE_STANDARD)
            ->setEvent($eventPHPDay2017);
        $manager->persist($ticketCost3);

        $ticketCost4 = new TicketCost();
        $ticketCost4->setName('next')
            ->setAmount(2000)
            ->setAltAmount(80)
            ->setCount(50)
            ->setEnabled(true)
            ->setType(TicketCost::TYPE_STANDARD)
            ->setEvent($eventPHPDay2017);
        $manager->persist($ticketCost4);

        $manager->flush();
    }
}
