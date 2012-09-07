<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\News;

class LoadNewsData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $news = new News();
        $news->setTitle('Определились с датой и местом проведения Zend Framework Day');
        $news->setSlug('date-and-place');
        $news->setPreview('Zend Framework Day посвящен популярному PHP фреймворку Zend Framework и является наследником конференции ZFConf Ukraine 2010.');
        $news->setText('<p>Конференция состоится 12 ноября 2011 года, в конференц зале отеля "Казацкий" (ул. Михайловская 1/3, рядом с Площадью Независимости).</p>');
        $news->setEvent($manager->merge($this->getReference('event-zfday')));
        $news->setCreatedAt(new \DateTime("2012-04-18"));

        $manager->persist($news);
        $manager->flush();
    }

    public function getOrder()
    {
        return 2; // the order in which fixtures will be loaded
    }
}
