<?php

namespace App\DataFixtures\ORM;

use App\Entity\Ticket;
use App\Entity\UserEventRegistration;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

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
        return [
            LoadUserData::class,
            LoadEventData::class,
            LoadPaymentData::class,
        ];
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $userDefault = $manager->merge($this->getReference('user-default'));
        $userDefault2 = $manager->merge($this->getReference('user-default2'));
        $userDefault3 = $manager->merge($this->getReference('user-default3'));
        $userAdmin = $manager->merge($this->getReference('user-admin'));

        $eventZfDay = $this->getReference('event-jsday2018');
        $eventPHPDay = $this->getReference('event-phpday2017');
        $eventPHP18 = $this->getReference('event-phpday2018');
        $eventNotActive = $this->getReference('event-not-active');

        // Ticket 1
        $ticket = (new Ticket())
            ->setEvent($manager->merge($eventZfDay))
            ->setAmountWithoutDiscount($eventZfDay->getCost())
            ->setAmount($eventZfDay->getCost())
            ->setUser($userDefault)
            ->setPayment($manager->merge($this->getReference('payment')))
        ;
        $manager->persist($ticket);
        $this->addReference('ticket-1', $ticket);
        $registration = new UserEventRegistration($userDefault, $eventZfDay);
        $userDefault->addUserEventRegistration($registration);
        $manager->persist($registration);
        // Ticket 2
        $ticket = (new Ticket())
            ->setEvent($manager->merge($eventPHPDay))
            ->setUser($userDefault)
            ->setAmountWithoutDiscount($eventPHPDay->getCost())
            ->setAmount($eventPHPDay->getCost())
            ->setPayment($manager->merge($this->getReference('pending')))
        ;
        $manager->persist($ticket);
        $this->addReference('ticket-2', $ticket);

        $registration = new UserEventRegistration($userDefault, $eventPHPDay);
        $userDefault->addUserEventRegistration($registration);
        $manager->persist($registration);
        // Ticket 3
        $ticket = (new Ticket())
            ->setEvent($manager->merge($eventNotActive))
            ->setUser($userAdmin)
            ->setAmountWithoutDiscount($eventNotActive->getCost())
            ->setAmount($eventNotActive->getCost())
        ;
        $manager->persist($ticket);
        $this->addReference('ticket-3', $ticket);

        $registration = new UserEventRegistration($userAdmin, $eventNotActive);
        $userDefault->addUserEventRegistration($registration);
        $manager->persist($registration);
        // Ticket 4: not used without payment
        $ticket = (new Ticket())
            ->setEvent($manager->merge($eventPHPDay))
            ->setUser($userAdmin)
            ->setAmountWithoutDiscount($eventPHPDay->getCost())
            ->setAmount($eventPHPDay->getCost())
            ->setCreatedAt(new \DateTime('2012-12-12 00:00:00'))
        ;
        $manager->persist($ticket);

        $registration = new UserEventRegistration($userAdmin, $eventPHPDay);
        $userDefault->addUserEventRegistration($registration);
        $manager->persist($registration);
        // Ticket 5: not used with paid payment
        $ticket = (new Ticket())
            ->setEvent($manager->merge($eventZfDay))
            ->setUser($userDefault2)
            ->setAmountWithoutDiscount($eventZfDay->getCost())
            ->setAmount($eventZfDay->getCost())
            ->setCreatedAt(new \DateTime('2012-12-12 00:00:00'))
            ->setPayment($manager->merge($this->getReference('payment2')))
        ;
        $manager->persist($ticket);

        $registration = new UserEventRegistration($userDefault2, $eventZfDay);
        $userDefault->addUserEventRegistration($registration);
        $manager->persist($registration);
        // Ticket 6: used with pending payment
        $ticket = (new Ticket())
            ->setEvent($manager->merge($eventPHPDay))
            ->setUser($userDefault2)
            ->setAmountWithoutDiscount($eventPHPDay->getCost())
            ->setAmount($eventPHPDay->getCost())
            ->setCreatedAt(new \DateTime('2012-12-12 00:00:00'))
            ->setPayment($manager->merge($this->getReference('pending2')))
        ;
        $manager->persist($ticket);

        $registration = new UserEventRegistration($userDefault2, $eventPHPDay);
        $userDefault->addUserEventRegistration($registration);
        $manager->persist($registration);
        // Ticket : used with pending payment
        $ticket = (new Ticket())
            ->setEvent($manager->merge($eventPHP18))
            ->setUser($userDefault2)
            ->setAmountWithoutDiscount($eventPHP18->getCost())
            ->setAmount($eventPHP18->getCost())
            ->setCreatedAt(new \DateTime('2018-12-12 00:00:00'))
            ->setPayment($manager->merge($this->getReference('pending3')))
        ;
        $manager->persist($ticket);

        // Ticket 7: used with paid payment
        $ticket = (new Ticket())
            ->setEvent($manager->merge($eventPHPDay))
            ->setUser($userDefault3)
            ->setAmountWithoutDiscount($eventPHPDay->getCost())
            ->setAmount($eventPHPDay->getCost())
            ->setCreatedAt(new \DateTime('2012-12-12 00:00:00'))
            ->setUsed(true)
            ->setPayment($manager->merge($this->getReference('payment2')))
        ;
        $manager->persist($ticket);

        $registration = new UserEventRegistration($userDefault3, $eventPHPDay);
        $userDefault->addUserEventRegistration($registration);
        $manager->persist($registration);
        // Ticket 8: not used without payment
        $ticket = (new Ticket())
            ->setEvent($manager->merge($eventZfDay))
            ->setUser($userDefault3)
            ->setAmountWithoutDiscount($eventZfDay->getCost())
            ->setAmount($eventZfDay->getCost())
            ->setCreatedAt(new \DateTime('2012-12-12 00:00:00'))
        ;
        $manager->persist($ticket);

        $registration = new UserEventRegistration($userDefault3, $eventZfDay);
        $userDefault->addUserEventRegistration($registration);
        $manager->persist($registration);
        for ($i = 1; $i <= 100; ++$i) {
            $user = $manager->merge($this->getReference('user-default-'.$i));
            $ticket = (new Ticket())
                ->setEvent($manager->merge($eventZfDay))
                ->setAmountWithoutDiscount($eventZfDay->getCost())
                ->setAmount($eventZfDay->getCost())
                ->setUser($user)
                ->setPayment($manager->merge($this->getReference('payment')))
            ;
            $manager->persist($ticket);
            $this->addReference('ticket-'.($i + 3), $ticket);

            $registration = new UserEventRegistration($user, $eventZfDay);
            $userDefault->addUserEventRegistration($registration);
            $manager->persist($registration);
        }

        $manager->flush();
    }
}
