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
        $userDefault = $manager->merge($this->getReference('user-default'));

        // Ticket 1
        $ticket = new Ticket($manager->merge($this->getReference('event-zfday')), $userDefault);
        $ticket->setPayment($manager->merge($this->getReference('payment')));
        $manager->persist($ticket);
        $this->addReference('ticket-1', $ticket);

        // Ticket 2
        $ticket = new Ticket($manager->merge($this->getReference('event-phpday')), $userDefault);
        $ticket->setPayment($manager->merge($this->getReference('pending')));
        $manager->persist($ticket);
        $this->addReference('ticket-2', $ticket);

        // Ticket 3
        $ticket = new Ticket($manager->merge($this->getReference('event-not-active')), $manager->merge($this->getReference('user-admin')));
        $manager->persist($ticket);
        $this->addReference('ticket-3', $ticket);

        // Ticket 4 inactive - no payment
        $ticket = new Ticket($manager->merge($this->getReference('event-phpday')), $manager->merge($this->getReference('user-admin')));
        $ticket->setUsed(false);
//        $ticket->setPayment(null);
        $manager->persist($ticket);
        $this->addReference('ticket-4', $ticket);

        // Ticket 5 inactive - have payment
        $ticket = new Ticket($manager->merge($this->getReference('event-phpday')), $manager->merge($this->getReference('user-admin')));
        $ticket->setUsed(false);
        $ticket->setPayment($manager->merge($this->getReference('payment')));
        $manager->persist($ticket);
        $this->addReference('ticket-5', $ticket);

        // Ticket 6 inactive - pending
        $ticket = new Ticket($manager->merge($this->getReference('event-phpday')), $manager->merge($this->getReference('user-admin')));
        $ticket->setUsed(true);
        $ticket->setPayment($manager->merge($this->getReference('pending')));
        $manager->persist($ticket);
        $this->addReference('ticket-6', $ticket);

        // Ticket 7 active - have payment
        $ticket = new Ticket($manager->merge($this->getReference('event-phpday')), $manager->merge($this->getReference('user-admin')));
        $ticket->setUsed(true);
        $ticket->setPayment($manager->merge($this->getReference('payment')));
        $manager->persist($ticket);
        $this->addReference('ticket-7', $ticket);

        $manager->flush();
    }
}
