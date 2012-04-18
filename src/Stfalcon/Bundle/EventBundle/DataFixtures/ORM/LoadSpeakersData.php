<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Stfalcon\Bundle\EventBundle\Entity\Speaker;

class LoadSpeakersData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load($manager)
    {
        $speaker = new Speaker();
        $speaker->setName('Name');
        $speaker->setEmail('speakerOne@wtfzf.com');
        $speaker->setCompany('Oracle');
        $speaker->setAbout('Short about info');
        $speaker->setSlug('speakerOne');
        $speaker->setPhoto('test/photo');
        $speaker->setEvents(array($manager->merge($this->getReference('event-zfday'))));

        $manager->persist($speaker);
        $manager->flush();
        $this->addReference('speaker-one', $speaker);
    }

    public function getOrder()
    {
        return 5; // the order in which fixtures will be loaded
    }
}