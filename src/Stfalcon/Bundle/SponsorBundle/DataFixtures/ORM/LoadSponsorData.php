<?php

namespace Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\SponsorBundle\Entity\Sponsor;

/**
 * Load Sponsor fixtures to database
 */
class LoadSponsorData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // ePochta
        $sponsor = new Sponsor();
        $sponsor->setName('ePochta');
        $sponsor->setSlug('epochta');
        $sponsor->setSite('http://www.epochta.ru/');
        $sponsor->setLogo('/images/partners/epochta.png');
        $sponsor->setAbout('About ePochta');
        $sponsor->setEvents(array($manager->merge($this->getReference('event-zfday'))));
        $manager->persist($sponsor);

        unset($sponsor);

        // Magento
        $sponsor = new Sponsor();
        $sponsor->setName('Magento');
        $sponsor->setSlug('magento');
        $sponsor->setSite('http://ua.magento.com/');
        $sponsor->setLogo('/images/partners/magento/small_logo.png');
        $sponsor->setAbout('Magento – це компанія №1 в світі в сегменті Open Source рішень для електронної комерції.');
        $sponsor->setEvents(
            array(
                 $manager->merge($this->getReference('event-zfday')),
                 $manager->merge($this->getReference('event-phpday'))
            )
        );
        $manager->persist($sponsor);

        unset($sponsor);

        // Symfony Camp
        $sponsor = new Sponsor();
        $sponsor->setName('Symfony Camp');
        $sponsor->setSlug('symfony-camp');
        $sponsor->setSite('http://2011.symfonycamp.org.ua/');
        $sponsor->setLogo('/images/partners/symfonycamp.png');
        $sponsor->setAbout('About Symfony Camp');
        $sponsor->setEvents(
            array(
                 $manager->merge($this->getReference('event-zfday')),
                 $manager->merge($this->getReference('event-phpday'))
            )
        );
        $manager->persist($sponsor);
        unset($sponsor);

        // Smart Me
        $sponsor = new Sponsor();
        $sponsor->setName('SmartMe');
        $sponsor->setSlug('smart-me');
        $sponsor->setSite('http://www.smartme.com.ua/');
        $sponsor->setLogo('/images/partners/smartme.png');
        $sponsor->setAbout('About Smart Me');
        $sponsor->setEvents(array($manager->merge($this->getReference('event-phpday'))));
        $manager->persist($sponsor);

        $manager->flush();
    }

    /**
     * Return the order in which fixtures will be loaded
     *
     * @return integer The order in which fixtures will be loaded
     */
    public function getOrder()
    {
        return 3;
    }
}
