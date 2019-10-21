<?php

namespace Application\Bundle\DefaultBundle\DataFixtures\ORM;

use Application\Bundle\DefaultBundle\Entity\Category;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * LoadCategoryData class.
 */
class LoadCategoryData extends AbstractFixture
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $golden = (new Category())
            ->setName('Golden sponsor')
            ->setSortOrder(30)
            ->setIsWideContainer(true);
        $manager->persist($golden);
        $this->addReference('golden-sponsor', $golden);

        $silver = (new Category())
            ->setName('Silver sponsor')
            ->setSortOrder(20)
            ->setIsWideContainer(true);
        $manager->persist($silver);
        $this->addReference('silver-sponsor', $silver);

        $partner = (new Category())
            ->setName('Партнеры')
            ->setSortOrder(20)
            ->setIsWideContainer(false);
        $manager->persist($partner);
        $this->addReference('partner-sponsor', $partner);

        $partner = (new Category())
            ->setName('Инфо Партнеры')
            ->setSortOrder(20)
            ->setIsWideContainer(false);
        $manager->persist($partner);
        $this->addReference('info-partner-sponsor', $partner);

        $manager->flush();
    }
}
