<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\News;

/**
 * LoadNewsData Class
 */
class LoadNewsData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Return fixture classes fixture is dependent on
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
        );
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
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
}
