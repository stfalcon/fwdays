<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\Review;

/**
 * LoadReviewData Class
 */
class LoadReviewData extends AbstractFixture implements DependentFixtureInterface
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
            'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadSpeakerData',
        );
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Get references for event fixtures
        $eventZFDay  = $manager->merge($this->getReference('event-zfday'));
        $eventPHPDay = $manager->merge($this->getReference('event-phpday'));

        // Get references for speaker fixtures
        $rabievskiy = $manager->merge($this->getReference('speaker-rabievskiy'));
        $shkodyak   = $manager->merge($this->getReference('speaker-shkodyak'));

        $review = new Review();
        $review->setTitle('ZF first steps');
        $review->setSlug('zf-first-steps');
        $review->setText('Zend Framework 2.0 is amazing');
        $review->setEvent($eventZFDay);
        $review->setSpeaker(array($rabievskiy));
        $manager->persist($review);

        $review = new Review();
        $review->setTitle('Symfony 2.1 first steps');
        $review->setSlug('symfony-2.1-first-steps');
        $review->setText('Symfony 2.1 is amazing');
        $review->setEvent($eventPHPDay);
        $review->setSpeaker(array($rabievskiy));
        $manager->persist($review);

        $review = new Review();
        $review->setTitle('Simple API via Zend Framework');
        $review->setSlug('simple-api-via-zend-framework');
        $review->setText('How to do simple API via Zend Framework');
        $review->setEvent($eventZFDay);
        $review->setSpeaker(array($shkodyak));
        $manager->persist($review);

        $review = new Review();
        $review->setTitle('Symfony Forever');
        $review->setSlug('symfony-forever');
        $review->setText('Why we using and will use Symfony');
        $review->setEvent($eventPHPDay);
        $review->setSpeaker(array($shkodyak));
        $manager->persist($review);

        $manager->flush();
    }
}
