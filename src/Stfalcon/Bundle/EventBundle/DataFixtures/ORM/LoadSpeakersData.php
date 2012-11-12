<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\OrderedFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;

use Stfalcon\Bundle\EventBundle\Entity\Speaker;

/**
 * LoadSpeakersData Class
 */
class LoadSpeakersData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $speaker = new Speaker();
        $speaker->setName('Андрей Шкодяк');
        $speaker->setEmail('a_s@test.com');
        $speaker->setCompany('Stfalcon');
        $speaker->setAbout('About Andrew');
        $speaker->setSlug('andrew-shkodyak');

        $source  = realpath(dirname(__FILE__) .'/../Images/speakers/andrew.png');
        $dest    = realpath(dirname(__FILE__) .'/../../../../../../web/uploads/speakers/') . '/andrew.png';
        copy($source, $dest);

        $speaker->setPhoto('andrew.png');
        $speaker->setEvents(
            array(
                 $manager->merge($this->getReference('event-zfday')),
                 $manager->merge($this->getReference('event-phpday')),
            )
        );

        $manager->persist($speaker);
        $this->addReference('speaker-shkodyak', $speaker);

        unset($speaker);

        $speaker = new Speaker();
        $speaker->setName('Валерий Рабиевский');
        $speaker->setEmail('v_r@test.com');
        $speaker->setCompany('Stfalcon');
        $speaker->setAbout('About Valeriy');
        $speaker->setSlug('valeriy-rabievskiy');

        $source  = realpath(dirname(__FILE__) .'/../Images/speakers/valeriy.png');
        $dest    = realpath(dirname(__FILE__) .'/../../../../../../web/uploads/speakers/') . '/valeriy.png';
        copy($source, $dest);

        $speaker->setPhoto('valeriy.png');
        $speaker->setEvents(
            array(
                 $manager->merge($this->getReference('event-zfday')),
                 $manager->merge($this->getReference('event-phpday')),
            )
        );

        $manager->persist($speaker);
        $this->addReference('speaker-rabievskiy', $speaker);

        $manager->flush();
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 5; // the order in which fixtures will be loaded
    }
}
