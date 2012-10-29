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
        $sponsorCategoryGolden = $this->getReference('sponsor-category-golden');
        $sponsorCategoryWooden = $this->getReference('sponsor-category-wooden');

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 4; // the order in which fixtures will be loaded
    }
}
