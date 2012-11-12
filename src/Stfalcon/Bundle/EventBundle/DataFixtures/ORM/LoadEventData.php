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
        $this->copyImage('zend-framework-day.png');
        $event->setLogo('zend-framework-day.png');
        $event->setCity('Киев');
        $event->setPlace('отель "Казацкий"');
        $event->setAbout("Описание события");
        $event->setDate(new \DateTime("2012-04-19", new \DateTimeZone('Europe/Kiev')));
        $event->setReceivePayments(true);
        $event->setCost(100);

        $manager->persist($event);
        $this->addReference('event-zfday', $event);

        unset($event);

        $event = new Event();
        $event->setName('PHP Frameworks Day');
        $event->setSlug('php-frameworks-day-2012');
        $event->setDescription('PHP frameworks day это конференция по современным PHP фреймворкам (Zend Framework 2, Symfony 2, Silex, Lithium и др.)');
        $this->copyImage('php-frameworks-day-2012.png');
        $event->setLogo('php-frameworks-day-2012.png');
        $event->setCity('Киев');
        $event->setPlace('Пока неизвестно');
        $event->setAbout("Описание события");
        $event->setDate(new \DateTime("2012-11-18", new \DateTimeZone('Europe/Kiev')));
        $event->setCost(100);

        $manager->persist($event);
        $this->addReference('event-phpday', $event);

        unset($event);

        $event = new Event();
        $event->setName('Not Active Frameworks Day');
        $event->setSlug('not-active-frameworks-day');
        $event->setDescription('Это событие тестовое, но должно быть неактивным');
        $this->copyImage('smile-lol-icon.png');
        $event->setLogo('smile-lol-icon.png');
        $event->setCity('Где-то там');
        $event->setPlace('Пока неизвестно');
        $event->setAbout("Описание события");
        $event->setActive(false);
        $event->setDate(new \DateTime("2012-12-12", new \DateTimeZone('Europe/Kiev')));
        $event->setCost(100);

        $manager->persist($event);
        $this->addReference('event-not-active', $event);

        $manager->flush();
    }

    /**
     * copy image from fixtures location to web folder
     * @param $image
     */
    public function copyImage($image){
        $source = realpath(dirname(__FILE__) .'/../Images/events/' . $image);
        $dest = realpath(dirname(__FILE__) .'/../../../../../../web/uploads/events') . '/' . $image;
        copy($source, $dest);
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}
