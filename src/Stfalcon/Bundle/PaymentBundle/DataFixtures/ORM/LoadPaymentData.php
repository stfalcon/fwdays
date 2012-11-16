<?php

namespace Stfalcon\Bundle\PaymentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

/**
 * LoadTicketData Class
 */
class LoadPaymentData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $payment = new Payment(
            $manager->merge($this->getReference('user-default')),
            100500
        );
        $payment->setAmountWithoutDiscount(100500);
        $payment->setStatus(Payment::STATUS_PAID);
        $manager->persist($payment);
        $this->addReference('payment', $payment);

        unset($payment);

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
