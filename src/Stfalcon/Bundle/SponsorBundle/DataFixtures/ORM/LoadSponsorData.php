<?php
namespace Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Stfalcon\Bundle\SponsorBundle\Entity\Sponsor;

/**
 * Load Sponsor fixtures to database
 */
class LoadSponsorData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load($manager)
    {
        $sponsor = new Sponsor();
        $sponsor->setName('ServerGroove');
        $sponsor->setSlug('server-groove');
        $sponsor->setSite('http://www.servergrove.com/');
        $sponsor->setAbout('The PHP Hosting Company');
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
        return 1;
    }
}