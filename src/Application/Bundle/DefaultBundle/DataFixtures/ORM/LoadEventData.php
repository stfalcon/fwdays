<?php

namespace Application\Bundle\DefaultBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Stfalcon\Bundle\EventBundle\Entity\Event;

class LoadEventData implements FixtureInterface
{
    public function load($manager)
    {
        $event = new Event();
        $event->setName('Zend Framework Day');
        $event->setSlug('zend-framework-day-2011');
        $event->setDescription('Zend Framework Day посвящен популярному PHP фреймворку Zend Framework и является наследником конференции ZFConf Ukraine 2010.');
        $event->setLogo('/tmp/logo.jpg');

        $manager->persist($event);
        $manager->flush();
    }
}