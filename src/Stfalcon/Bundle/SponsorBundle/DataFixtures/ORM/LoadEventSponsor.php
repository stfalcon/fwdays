<?php

namespace Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\EventSponsor;

/**
 * Load Sponsor fixtures to database
 */
class LoadEventSponsor extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $evSponsor = new EventSponsor();
        $evSponsor->setCategory($manager->merge($this->getReference('golden-sponsor')));
        $evSponsor->setEvent($manager->merge($this->getReference('event-phpday')));
        $evSponsor->setSponsor($manager->merge($this->getReference('sponsor-ePochta')));

        $manager->persist($evSponsor);
        unset($evSponsor);


        $evSponsor = new EventSponsor();
        $evSponsor->setCategory($manager->merge($this->getReference('silver-sponsor')));
        $evSponsor->setEvent($manager->merge($this->getReference('event-zfday')));
        $evSponsor->setSponsor($manager->merge($this->getReference('sponsor-Magento')));

        $manager->persist($evSponsor);
        unset($evSponsor);


        $evSponsor = new EventSponsor();
        $evSponsor->setCategory($manager->merge($this->getReference('golden-sponsor')));
        $evSponsor->setEvent($manager->merge($this->getReference('event-phpday')));
        $evSponsor->setSponsor($manager->merge($this->getReference('sponsor-Magento')));

        $manager->persist($evSponsor);
        unset($evSponsor);

        $manager->flush();
    }

    /**
     * Return the order in which fixtures will be loaded
     *
     * @return integer The order in which fixtures will be loaded
     */
    public function getOrder()
    {
        return 4;
    }
}
