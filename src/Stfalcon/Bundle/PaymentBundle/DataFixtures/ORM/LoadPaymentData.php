<?php

namespace Stfalcon\Bundle\PaymentBundle\DataFixtures\ORM;

use Application\Bundle\UserBundle\Entity\User;
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
        /** @var User $userDefault */
        $userDefault = $manager->merge($this->getReference('user-default'));

        $payment = new Payment();
        $payment->setUser($userDefault);
        $payment->setAmount(0);
        $payment->setStatus(Payment::STATUS_PAID);
        $manager->persist($payment);
        $this->addReference('payment', $payment);

        $payment = new Payment();
        $payment->setUser($userDefault);
        $payment->setAmount(0);
        $payment->setStatus(Payment::STATUS_PENDING);
        $manager->persist($payment);
        $this->addReference('pending', $payment);

        /** @var User $userDefault2 */
        $userDefault2 = $manager->merge($this->getReference('user-default2'));

        $payment = new Payment();
        $payment->setUser($userDefault2);
        $payment->setAmount(0);
        $payment->setStatus(Payment::STATUS_PAID);
        $manager->persist($payment);
        $this->addReference('payment2', $payment);

        $payment = new Payment();
        $payment->setUser($userDefault2);
        $payment->setAmount(0);
        $payment->setStatus(Payment::STATUS_PENDING);
        $manager->persist($payment);
        $this->addReference('pending2', $payment);

        $manager->flush();
    }
}
