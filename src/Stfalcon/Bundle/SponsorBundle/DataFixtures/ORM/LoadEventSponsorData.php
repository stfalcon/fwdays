<?php

namespace Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\SponsorBundle\Entity\EventSponsor;

/**
 * Load Sponsor fixtures to database
 */
class LoadEventSponsorData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // oDesk is Golden sponsor of PHP Frameworks Day 2012
        $phpdayGoldenOdesk = new EventSponsor();
        $phpdayGoldenOdesk->setCategory($manager->merge($this->getReference('golden-sponsor')));
        $phpdayGoldenOdesk->setEvent($manager->merge($this->getReference('event-phpday')));
        $phpdayGoldenOdesk->setSponsor($manager->merge($this->getReference('sponsor-odesk')));
        $manager->persist($phpdayGoldenOdesk);

        // Magento is Golden sponsor of Zend Framework Day 2011
        $zfdayGoldenMagento = new EventSponsor();
        $zfdayGoldenMagento->setCategory($manager->merge($this->getReference('golden-sponsor')));
        $zfdayGoldenMagento->setEvent($manager->merge($this->getReference('event-zfday')));
        $zfdayGoldenMagento->setSponsor($manager->merge($this->getReference('sponsor-magento')));
        $manager->persist($zfdayGoldenMagento);

        // ePochta is Silver sponsor of Zend Framework Day 2011
        $zfdaySilverEpochta = new EventSponsor();
        $zfdaySilverEpochta->setCategory($manager->merge($this->getReference('silver-sponsor')));
        $zfdaySilverEpochta->setEvent($manager->merge($this->getReference('event-phpday')));
        $zfdaySilverEpochta->setSponsor($manager->merge($this->getReference('sponsor-epochta')));
        $manager->persist($zfdaySilverEpochta);

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
