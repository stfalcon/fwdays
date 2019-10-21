<?php

namespace Application\Bundle\DefaultBundle\DataFixtures\ORM;

use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadPaymentData Class.
 */
class LoadPaymentData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Return fixture classes fixture is dependent on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadUserData',
        ];
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
        $payment->setBaseAmount(0);
        $payment->setStatus(Payment::STATUS_PAID);
        $manager->persist($payment);
        $this->addReference('payment', $payment);

        $payment = new Payment();
        $payment->setUser($userDefault);
        $payment->setAmount(0);
        $payment->setBaseAmount(0);
        $payment->setStatus(Payment::STATUS_PENDING);
        $manager->persist($payment);
        $this->addReference('pending', $payment);

        /** @var User $userDefault2 */
        $userDefault2 = $manager->merge($this->getReference('user-default2'));

        $payment = new Payment();
        $payment->setUser($userDefault2);
        $payment->setAmount(0);
        $payment->setBaseAmount(0);
        $payment->setStatus(Payment::STATUS_PAID);
        $manager->persist($payment);
        $this->addReference('payment2', $payment);

        $payment = new Payment();
        $payment->setUser($userDefault2);
        $payment->setAmount(0);
        $payment->setBaseAmount(0);
        $payment->setStatus(Payment::STATUS_PENDING);
        $manager->persist($payment);
        $this->addReference('pending2', $payment);

        $manager->flush();
    }
}
