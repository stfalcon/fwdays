<?php

namespace App\DataFixtures\ORM;

use App\Entity\Mail;
use App\Entity\MailQueue;
use App\Entity\Payment;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

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
        return [
            LoadUserData::class,
            LoadEventData::class,
            LoadPaymentData::class,
        ];
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $userDefault = $manager->merge($this->getReference('user-default'));

        $mail = new Mail();
        $mail->setTitle('test');
        $mail->setText('test');
        $mail->setPaymentStatus(Payment::STATUS_PAID);
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
