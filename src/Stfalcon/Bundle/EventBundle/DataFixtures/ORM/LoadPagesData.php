<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Stfalcon\Bundle\EventBundle\Entity\Page;

class LoadPagesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load($manager)
    {
        $page = new Page();
        $page->setTitle('Страница события');
        $page->setSlug('eventPage');
        $page->setText('<p>Текст страницы</p>');
        $page->setEvent($manager->merge($this->getReference('event-zfday')));

        $manager->persist($page);
        $manager->flush();
    }

    public function getOrder()
    {
        return 4; // the order in which fixtures will be loaded
    }
}