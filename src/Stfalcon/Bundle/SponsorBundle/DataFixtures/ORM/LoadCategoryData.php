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
        // ePochta
        $category = new Category();
        $category->setName('Golden');

        $manager->persist($category);

        unset($category);

        // Magento
        $category = new Category();
        $category->setName('Wooden');

        $manager->persist($category);

        unset($category);

        // Smart Me

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
