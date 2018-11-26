<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Stfalcon\Bundle\EventBundle\Entity\Mail;
use Stfalcon\Bundle\EventBundle\Entity\MailQueue;

/**
 * Class LoadMailQueueData.
 */
class LoadMailQueueData extends AbstractFixture implements DependentFixtureInterface
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
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $userDefault = $manager->merge($this->getReference('user-default'));

        $mail = new Mail();
        $mail->setTitle('test');
        $mail->setText('test');
        $mail->setPaymentStatus($manager->merge($this->getReference('payment')));
        $mail->addEvent($manager->merge($this->getReference('event-jsday2018')));
        $manager->persist($mail);

        $mq = new MailQueue();
        $mq->setMail($mail);
        $mq->setUser($userDefault);
        $manager->persist($mq);

        $manager->flush();

        $this->addReference('mail_queue', $mq);
    }
}
