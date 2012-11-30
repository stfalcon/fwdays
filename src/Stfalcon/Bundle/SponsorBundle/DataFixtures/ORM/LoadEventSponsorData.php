<?php

namespace Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\SponsorBundle\Entity\EventSponsor;

/**
 * LoadEventSponsorData class
 */
class LoadEventSponsorData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Return fixture classes fixture is dependent on
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM\LoadCategoryData',
            'Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM\LoadSponsorData',
            'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
        );
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Get references for category fixtures
        $goldenSponsor = $manager->merge($this->getReference('golden-sponsor'));
        $silverSponsor = $manager->merge($this->getReference('silver-sponsor'));

        // Get references for event fixtures
        $eventZFDay  = $manager->merge($this->getReference('event-zfday'));
        $eventPHPDay = $manager->merge($this->getReference('event-phpday'));

        // Get references for sponsor fixtures
        $sponsorODesk   = $manager->merge($this->getReference('sponsor-odesk'));
        $sponsorMagento = $manager->merge($this->getReference('sponsor-magento'));
        $sponsorEpochta = $manager->merge($this->getReference('sponsor-epochta'));

        // oDesk is Golden sponsor of PHP Frameworks Day 2012
        $eventSponsor = new EventSponsor();
        $eventSponsor->setCategory($goldenSponsor);
        $eventSponsor->setEvent($eventPHPDay);
        $eventSponsor->setSponsor($sponsorODesk);
        $manager->persist($eventSponsor);

        // Magento is Golden sponsor of Zend Framework Day 2011
        $eventSponsor = new EventSponsor();
        $eventSponsor->setCategory($goldenSponsor);
        $eventSponsor->setEvent($eventZFDay);
        $eventSponsor->setSponsor($sponsorMagento);
        $manager->persist($eventSponsor);

        // ePochta is Silver sponsor of PHP Frameworks Day 2012
        $eventSponsor = new EventSponsor();
        $eventSponsor->setCategory($silverSponsor);
        $eventSponsor->setEvent($eventPHPDay);
        $eventSponsor->setSponsor($sponsorEpochta);
        $manager->persist($eventSponsor);

        $manager->flush();
    }
}
