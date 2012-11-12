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
        $this->copyImage('andrew.png');
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
        $this->copyImage('valeriy.png');
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
     * copy image from fixtures location to web folder
     * @param $image
     */
    public function copyImage($image){
        $source = realpath(dirname(__FILE__) .'/../Images/speakers/' . $image);
        $dest = realpath(dirname(__FILE__) .'/../../../../../../web/uploads/speakers') . '/' . $image;
        copy($source, $dest);
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return 5; // the order in which fixtures will be loaded
    }
}
