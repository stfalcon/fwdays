<?php

namespace Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\SponsorBundle\Entity\Category;

/**
 * Load Sponsor fixtures to database
 */
class LoadCategoryData extends AbstractFixture implements OrderedFixtureInterface
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

        $this->addReference('golden-sponsor',$golden);

        $silver = new Category();
        $silver->setName('Silver sponsor');
        $silver->setSortOrder(20);
        $manager->persist($silver);

        $this->addReference('silver-sponsor',$silver);

        $manager->flush();
    }

    /**
     * Return the order in which fixtures will be loaded
     *
     * @return integer The order in which fixtures will be loaded
     */
    public function getOrder()
    {
        return 2;
    }
}
