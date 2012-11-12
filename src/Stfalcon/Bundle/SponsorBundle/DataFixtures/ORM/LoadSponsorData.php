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
        // Magento
        $magento = new Sponsor();
        $magento->setName('Magento');
        $magento->setSlug('magento');
        $magento->setSite('http://ua.magento.com/');
        $magento->setLogo('magento.png');
        $this->copyImage('magento.png');
        $magento->setAbout('The Magento eCommerce platform serves more than 125,000 merchants worldwide and is supported by a global ecosystem of solution partners and third-party developers.');
        $magento->setSortOrder(10);
        $magento->setOnMain(true);
        $manager->persist($magento);

        $this->addReference('sponsor-magento', $magento);

        // oDesk
        $odesk = new Sponsor();
        $odesk->setName('oDesk');
        $odesk->setSlug('odesk');
        $odesk->setSite('http://odesk.com/');
        $odesk->setLogo('odesk.jpg');
        $this->copyImage('odesk.jpg');
        $odesk->setAbout('About Smart Me');
        $odesk->setSortOrder(20);
        $odesk->setOnMain(true);
        $manager->persist($odesk);

        $this->addReference('sponsor-odesk', $odesk);

        // ePochta
        $epochta = new Sponsor();
        $epochta->setName('ePochta');
        $epochta->setSlug('epochta');
        $epochta->setSite('http://www.epochta.ru/');
        $epochta->setLogo('epochta.png');
        $this->copyImage('epochta.png');
        $epochta->setOnMain(false);
        $epochta->setSortOrder(15);
        $manager->persist($epochta);

        $this->addReference('sponsor-epochta', $epochta);

        $manager->flush();
    }

    /**
     * copy image from fixtures location to web folder
     * @param $image
     */
    public function copyImage($image){
        $source = realpath(dirname(__FILE__) .'/../Images/' . $image);
        $dest = realpath(dirname(__FILE__) .'/../../../../../../web/uploads/sponsors') . '/' . $image;
        copy($source, $dest);
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
