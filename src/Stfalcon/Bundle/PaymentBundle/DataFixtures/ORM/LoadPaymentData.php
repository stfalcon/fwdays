<?php

namespace Stfalcon\Bundle\PaymentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\PaymentBundle\Entity\Payment;

/**
 * LoadPaymentData Class
 */
class LoadPaymentData extends AbstractFixture implements DependentFixtureInterface
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
        );
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $userDefault = $manager->merge($this->getReference('user-default'));

        $payment = new Payment($userDefault, 100500);
        $payment->setAmountWithoutDiscount(100500);
        $payment->setStatus(Payment::STATUS_PAID);
        $manager->persist($payment);
        $this->addReference('payment', $payment);

        $manager->flush();
    }
}
