<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\Review;

/**
 * LoadReviewData Class
 */
class LoadReviewData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $review = new Review();
        $review->setTitle('ZF first steps');
        $review->setSlug('zf-first-steps');
        $review->setText('Zend Framework 2.0 is amazing');
        $review->setEvent($manager->merge($this->getReference('event-zfday')));
        $review->setSpeaker(array($manager->merge($this->getReference('speaker-rabievskiy'))));

        $manager->persist($review);

        unset($review);

        $review = new Review();
        $review->setTitle('Symfony 2.1 first steps');
        $review->setSlug('symfony-2.1-first-steps');
        $review->setText('Symfony 2.1 is amazing');
        $review->setEvent($manager->merge($this->getReference('event-phpday')));
        $review->setSpeaker(array($manager->merge($this->getReference('speaker-rabievskiy'))));

        $manager->persist($review);

        unset($review);

        $review = new Review();
        $review->setTitle('Simple API via Zend Framework');
        $review->setSlug('simple-api-via-zend-framework');
        $review->setText('How to do simple API via Zend Framework');
        $review->setEvent($manager->merge($this->getReference('event-zfday')));
        $review->setSpeaker(array($manager->merge($this->getReference('speaker-shkodyak'))));

        $manager->persist($review);

        unset($review);

        $review = new Review();
        $review->setTitle('Symfony Forever');
        $review->setSlug('symfony-forever');
        $review->setText('Why we using and will use Symfony');
        $review->setEvent($manager->merge($this->getReference('event-phpday')));
        $review->setSpeaker(array($manager->merge($this->getReference('speaker-shkodyak'))));

        $manager->persist($review);

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 6; // the order in which fixtures will be loaded
    }
}
