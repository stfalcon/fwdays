<?php

namespace App\DataFixtures\ORM;

use App\Entity\Option;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

/**
 * LoadPaymentData Class.
 */
class LoadOptionsData extends AbstractFixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $option = (new Option())
            ->setKey('SHOW_TSHIT_BANNER')
            ->setValue('false')
            ->setType(Option::TYPE_BOOL)
        ;

        $manager->persist($option);
        $manager->flush();
    }
}
