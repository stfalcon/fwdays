<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\Page;

class LoadPagesData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $page = new Page();
        $page->setTitle('Страница события');
        $page->setSlug('eventPage');
        $page->setText('<p>Текст страницы</p>');
        $page->setEvent($manager->merge($this->getReference('event-zfday')));
        $page->setSortOrder(1);

        $manager->persist($page);
        $manager->flush();
    }

    public function getOrder()
    {
        return 4; // the order in which fixtures will be loaded
    }
}
