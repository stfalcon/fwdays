<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Stfalcon\Bundle\EventBundle\Entity\Review;

class LoadReviewData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load($manager)
    {
        $review = new Review();
        $review->setTitle('Review title');
        $review->setSlug('reviewSlug');
        $review->setText('Review text');
        $review->setEvent($manager->merge($this->getReference('event-zfday')));
        $review->setSpeaker(array($manager->merge($this->getReference('speaker-one'))));

        $manager->persist($review);
        $manager->flush();
    }

    public function getOrder()
    {
        return 6; // the order in which fixtures will be loaded
    }
}