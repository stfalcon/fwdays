<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\Mail;
use Stfalcon\Bundle\EventBundle\Entity\MailQueue;


class LoadMailQueueData extends AbstractFixture implements DependentFixtureInterface
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

    public function load(ObjectManager $manager)
    {
        $userDefault = $manager->merge($this->getReference('user-default'));

        $mail=new Mail();
        $mail->setTitle('test');
        $mail->setText('test');
        $mail->setPaymentStatus($manager->merge($this->getReference('payment')));
        $mail->setEvent($manager->merge($this->getReference('event-zfday')));

        $manager->persist($mail);

        $mq = new MailQueue();
        $mq->setMail($mail);
        $mq->setUser($userDefault);
        $manager->persist($mq);
        $manager->flush();

        $this->addReference('mail_queue', $mq);
    }
}
