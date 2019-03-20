<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Stfalcon\Bundle\EventBundle\Entity\Ticket;

/**
 * LoadTicketData Class.
 */
class LoadTicketData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Return fixture classes fixture is dependent on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData',
            'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
            'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadPaymentData',
        );
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /**
         * @var \Application\Bundle\UserBundle\Entity\User
         * @var \Application\Bundle\UserBundle\Entity\User $userDefault2
         * @var \Application\Bundle\UserBundle\Entity\User $userDefault3
         * @var \Application\Bundle\UserBundle\Entity\User $userAdmin
         */
        $userDefault = $manager->merge($this->getReference('user-default'));
        $userDefault2 = $manager->merge($this->getReference('user-default2'));
        $userDefault3 = $manager->merge($this->getReference('user-default3'));
        $userAdmin = $manager->merge($this->getReference('user-admin'));

        $eventZfDay = $this->getReference('event-jsday2018');
        $eventPHPDay = $this->getReference('event-phpday2017');
        $eventNotActive = $this->getReference('event-not-active');

        // Ticket 1
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventZfDay));
        $ticket->setAmountWithoutDiscount($eventZfDay->getCost());
        $ticket->setAmount($eventZfDay->getCost());
        $ticket->setUser($userDefault);
        $ticket->setPayment($manager->merge($this->getReference('payment')));
        $manager->persist($ticket);
        $this->addReference('ticket-1', $ticket);
        $userDefault->addWantsToVisitEvents($eventZfDay);

        // Ticket 2
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventPHPDay));
        $ticket->setUser($userDefault);
        $ticket->setAmountWithoutDiscount($eventPHPDay->getCost());
        $ticket->setAmount($eventPHPDay->getCost());
        $ticket->setPayment($manager->merge($this->getReference('pending')));
        $manager->persist($ticket);
        $this->addReference('ticket-2', $ticket);
        $userDefault->addWantsToVisitEvents($eventPHPDay);

        // Ticket 3
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventNotActive));
        $ticket->setUser($userAdmin);
        $ticket->setAmountWithoutDiscount($eventNotActive->getCost());
        $ticket->setAmount($eventNotActive->getCost());
        $manager->persist($ticket);
        $this->addReference('ticket-3', $ticket);
        $userAdmin->addWantsToVisitEvents($eventNotActive);
        // Ticket 4: not used without payment
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventPHPDay));
        $ticket->setUser($userAdmin);
        $ticket->setAmountWithoutDiscount($eventPHPDay->getCost());
        $ticket->setAmount($eventPHPDay->getCost());
        $ticket->setCreatedAt(new \DateTime('2012-12-12 00:00:00'));
        $ticket->setUsed(false);
        $manager->persist($ticket);
        $userAdmin->addWantsToVisitEvents($eventPHPDay);

        // Ticket 5: not used with paid payment
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventZfDay));
        $ticket->setUser($userDefault2);
        $ticket->setAmountWithoutDiscount($eventPHPDay->getCost());
        $ticket->setAmount($eventPHPDay->getCost());
        $ticket->setCreatedAt(new \DateTime('2012-12-12 00:00:00'));
        $ticket->setUsed(false);
        $ticket->setPayment($manager->merge($this->getReference('payment2')));
        $manager->persist($ticket);
        $userDefault2->addWantsToVisitEvents($eventPHPDay);

        // Ticket 6: used with pending payment
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventPHPDay));
        $ticket->setUser($userDefault2);
        $ticket->setAmountWithoutDiscount($eventPHPDay->getCost());
        $ticket->setAmount($eventPHPDay->getCost());
        $ticket->setCreatedAt(new \DateTime('2012-12-12 00:00:00'));
        $ticket->setUsed(false);
        $ticket->setPayment($manager->merge($this->getReference('pending2')));
        $manager->persist($ticket);
        $userDefault2->addWantsToVisitEvents($eventPHPDay);

        // Ticket 7: used with paid payment
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventPHPDay));
        $ticket->setUser($userDefault3);
        $ticket->setAmountWithoutDiscount($eventPHPDay->getCost());
        $ticket->setAmount($eventPHPDay->getCost());
        $ticket->setCreatedAt(new \DateTime('2012-12-12 00:00:00'));
        $ticket->setUsed(true);
        $ticket->setPayment($manager->merge($this->getReference('payment2')));
        $manager->persist($ticket);
        $userDefault3->addWantsToVisitEvents($eventPHPDay);

        // Ticket 8: not used without payment
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventZfDay));
        $ticket->setUser($userDefault3);
        $ticket->setAmountWithoutDiscount($eventZfDay->getCost());
        $ticket->setAmount($eventZfDay->getCost());
        $ticket->setCreatedAt(new \DateTime('2012-12-12 00:00:00'));
        $ticket->setUsed(false);
        $manager->persist($ticket);
        $userDefault3->addWantsToVisitEvents($eventZfDay);
        for ($i = 1; $i <= 100; ++$i) {
            $ticket = new Ticket();
            $ticket->setEvent($manager->merge($eventZfDay));
            $ticket->setAmountWithoutDiscount($eventZfDay->getCost());
            $ticket->setAmount($eventZfDay->getCost());
            $user = $manager->merge($this->getReference('user-default-'.$i));
            $ticket->setUser($user);
            $ticket->setPayment($manager->merge($this->getReference('payment')));
            $manager->persist($ticket);
            $this->addReference('ticket-'.($i + 3), $ticket);
            $user->addWantsToVisitEvents($eventZfDay);
        }

        $manager->flush();
    }
}
