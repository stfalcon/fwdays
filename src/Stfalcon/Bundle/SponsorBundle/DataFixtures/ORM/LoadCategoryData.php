<?php

namespace Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\SponsorBundle\Entity\Category;

/**
 * LoadCategoryData class
 */
class LoadCategoryData extends AbstractFixture
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $golden = new Category();
        $golden->setName('Golden sponsor');
        $golden->setSortOrder(30);
        $manager->persist($golden);
        $this->addReference('golden-sponsor', $golden);

        $silver = new Category();
        $silver->setName('Silver sponsor');
        $silver->setSortOrder(20);
        $manager->persist($silver);
        $this->addReference('silver-sponsor', $silver);

        $manager->flush();
    }
}
