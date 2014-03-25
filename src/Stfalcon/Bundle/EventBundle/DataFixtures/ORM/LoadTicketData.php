<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\Ticket;

/**
 * LoadTicketData Class
 */
class LoadTicketData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Return fixture classes fixture is dependent on
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData',
            'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
            'Stfalcon\Bundle\PaymentBundle\DataFixtures\ORM\LoadPaymentData',
        );
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /**
         * @var \Application\Bundle\UserBundle\Entity\User $userDefault
         * @var \Application\Bundle\UserBundle\Entity\User $userDefault2
         * @var \Application\Bundle\UserBundle\Entity\User $userDefault3
         * @var \Application\Bundle\UserBundle\Entity\User $userAdmin
         */
        $userDefault  = $manager->merge($this->getReference('user-default'));
        $userDefault2 = $manager->merge($this->getReference('user-default2'));
        $userDefault3 = $manager->merge($this->getReference('user-default3'));
        $userAdmin    = $manager->merge($this->getReference('user-admin'));

        $eventZfDay = $this->getReference('event-zfday');
        $eventPHPDay = $this->getReference('event-phpday');
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

        // Ticket 2
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventPHPDay));
        $ticket->setUser($userDefault);
        $ticket->setAmountWithoutDiscount($eventPHPDay->getCost());
        $ticket->setAmount($eventPHPDay->getCost());
        $ticket->setPayment($manager->merge($this->getReference('pending')));
        $manager->persist($ticket);
        $this->addReference('ticket-2', $ticket);

        // Ticket 3
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventNotActive));
        $ticket->setUser($userAdmin);
        $ticket->setAmountWithoutDiscount($eventNotActive->getCost());
        $ticket->setAmount($eventNotActive->getCost());
        $manager->persist($ticket);
        $this->addReference('ticket-3', $ticket);

        // Ticket 4: not used without payment
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventPHPDay));
        $ticket->setUser($userDefault2);
        $ticket->setAmountWithoutDiscount($eventPHPDay->getCost());
        $ticket->setAmount($eventPHPDay->getCost());
        $ticket->setCreatedAt(new \DateTime('2012-12-12 00:00:00'));
        $ticket->setUsed(false);
        $manager->persist($ticket);

        // Ticket 5: not used with paid payment
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventPHPDay));
        $ticket->setUser($userDefault2);
        $ticket->setAmountWithoutDiscount($eventPHPDay->getCost());
        $ticket->setAmount($eventPHPDay->getCost());
        $ticket->setCreatedAt(new \DateTime('2012-12-12 00:00:00'));
        $ticket->setUsed(false);
        $ticket->setPayment($manager->merge($this->getReference('payment2')));
        $manager->persist($ticket);

        // Ticket 6: used with pending payment
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventPHPDay));
        $ticket->setUser($userDefault2);
        $ticket->setAmountWithoutDiscount($eventPHPDay->getCost());
        $ticket->setAmount($eventPHPDay->getCost());
        $ticket->setCreatedAt(new \DateTime('2012-12-12 00:00:00'));
        $ticket->setUsed(true);
        $ticket->setPayment($manager->merge($this->getReference('pending2')));
        $manager->persist($ticket);

        // Ticket 7: used with paid payment
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventPHPDay));
        $ticket->setUser($userDefault2);
        $ticket->setAmountWithoutDiscount($eventPHPDay->getCost());
        $ticket->setAmount($eventPHPDay->getCost());
        $ticket->setCreatedAt(new \DateTime('2012-12-12 00:00:00'));
        $ticket->setUsed(true);
        $ticket->setPayment($manager->merge($this->getReference('payment2')));
        $manager->persist($ticket);

        // Ticket 8: not used without payment
        $ticket = new Ticket();
        $ticket->setEvent($manager->merge($eventZfDay));
        $ticket->setUser($userDefault3);
        $ticket->setAmountWithoutDiscount($eventZfDay->getCost());
        $ticket->setAmount($eventZfDay->getCost());
        $ticket->setCreatedAt(new \DateTime('2012-12-12 00:00:00'));
        $ticket->setUsed(false);
        $manager->persist($ticket);

        for ($i = 1; $i <= 100; $i++) {
            $ticket = new Ticket();
            $ticket->setEvent($manager->merge($eventZfDay));
            $ticket->setAmountWithoutDiscount($eventZfDay->getCost());
            $ticket->setAmount($eventZfDay->getCost());
            $ticket->setUser($manager->merge($this->getReference('user-default-' . $i)));
            $ticket->setPayment($manager->merge($this->getReference('payment')));
            $manager->persist($ticket);
            $this->addReference('ticket-' . ($i + 3), $ticket);
        }

        $manager->flush();
    }
}
