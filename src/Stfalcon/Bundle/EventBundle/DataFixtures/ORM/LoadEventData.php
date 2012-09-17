<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\Event;

/**
 * LoadEventData Class
 */
class LoadEventData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $event = new Event();
        $event->setName('Zend Framework Day');
        $event->setSlug('zend-framework-day-2011');
        $event->setDescription('Zend Framework Day посвящен популярному PHP фреймворку Zend Framework и является наследником конференции ZFConf Ukraine 2010.');
        $event->setLogo('/tmp/logo.jpg');
        $event->setCity('Киев');
        $event->setPlace('отель "Казацкий"');
        $event->setAbout("Описание события");
        $event->setDate(new \DateTime("2012-04-19", new \DateTimeZone('Europe/Kiev')));
        $event->setReceivePayments(true);

        $manager->persist($event);
        $this->addReference('event-zfday', $event);

        unset($event);

        $event = new Event();
        $event->setName('PHP Frameworks Day');
        $event->setSlug('php-frameworks-day-2012');
        $event->setDescription('PHP frameworks day это конференция по современным PHP фреймворкам (Zend Framework 2, Symfony 2, Silex, Lithium и др.)');
        $event->setLogo('/tmp/logo.jpg');
        $event->setCity('Киев');
        $event->setPlace('Пока неизвестно');
        $event->setAbout("Описание события");
        $event->setDate(new \DateTime("2012-11-18", new \DateTimeZone('Europe/Kiev')));

        $manager->persist($event);
        $this->addReference('event-phpday', $event);

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}
