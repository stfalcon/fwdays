<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\Page;

/**
 * LoadPagesData Class
 */
class LoadPagesData extends AbstractFixture implements DependentFixtureInterface
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
        $page = new Page();
        $page->setTitle('Страница события');
        $page->setSlug('eventPage');
        $page->setText('<p>Текст страницы</p>');
        $page->setEvent($manager->merge($this->getReference('event-zfday')));
        $page->setSortOrder(1);

        $manager->persist($page);
        $manager->flush();
    }
}
