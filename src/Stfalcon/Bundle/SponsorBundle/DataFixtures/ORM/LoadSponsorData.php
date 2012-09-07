<?php

namespace Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Stfalcon\Bundle\SponsorBundle\Entity\Sponsor;

/**
 * Load Sponsor fixtures to database
 */
class LoadSponsorData extends AbstractFixture implements OrderedFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $sponsor1 = new Sponsor();
        $sponsor1->setName('ePochta');
        $sponsor1->setSlug('epochta');
        $sponsor1->setSite('http://www.epochta.ru/');
        $sponsor1->setLogo('/images/partners/epochta.png');
        $sponsor1->setAbout('About ePochta');
        $sponsor1->setEvents(array($manager->merge($this->getReference('event-zfday'))));
        $manager->persist($sponsor1);

        $sponsor2 = new Sponsor();
        $sponsor2->setName('Magento');
        $sponsor2->setSlug('magento');
        $sponsor2->setSite('http://ua.magento.com/');
        $sponsor2->setLogo('/images/partners/magento/small_logo.png');
        $sponsor2->setAbout('Magento – це компанія №1 в світі в сегменті Open Source рішень для електронної комерції.');
        $sponsor2->setEvents(array($manager->merge($this->getReference('event-zfday'))));
        $manager->persist($sponsor2);

        $sponsor3 = new Sponsor();
        $sponsor3->setName('Symfony Camp');
        $sponsor3->setSlug('symfony-camp');
        $sponsor3->setSite('http://2011.symfonycamp.org.ua/');
        $sponsor3->setLogo('/images/partners/symfonycamp.png');
        $sponsor3->setAbout('About Symfony Camp');
        $sponsor3->setEvents(array($manager->merge($this->getReference('event-zfday'))));
        $manager->persist($sponsor3);

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