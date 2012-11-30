<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Stfalcon\Bundle\EventBundle\Entity\Speaker;

/**
 * LoadSpeakerData Class
 */
class LoadSpeakerData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * Return fixture classes fixture is dependent on
     *
     * @return array
     */
    public function getDependencies()
    {
        return array(
            'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
        );
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Get references for event fixtures
        $eventZFDay  = $manager->merge($this->getReference('event-zfday'));
        $eventPHPDay = $manager->merge($this->getReference('event-phpday'));

        $speaker = new Speaker();
        $speaker->setName('Андрей Шкодяк');
        $speaker->setEmail('a_s@test.com');
        $speaker->setCompany('Stfalcon');
        $speaker->setAbout('About Andrew');
        $speaker->setSlug('andrew-shkodyak');
        $speaker->setFile($this->_generateUploadedFile('andrew.png'));
        $speaker->setEvents(array($eventZFDay, $eventPHPDay));
        $manager->persist($speaker);
        $this->addReference('speaker-shkodyak', $speaker);

        $speaker = new Speaker();
        $speaker->setName('Валерий Рабиевский');
        $speaker->setEmail('v_r@test.com');
        $speaker->setCompany('Stfalcon');
        $speaker->setAbout('About Valeriy');
        $speaker->setSlug('valeriy-rabievskiy');
        $speaker->setFile($this->_generateUploadedFile('valeriy.png'));
        $speaker->setEvents(array($eventZFDay, $eventPHPDay));
        $manager->persist($speaker);
        $this->addReference('speaker-rabievskiy', $speaker);

        $manager->flush();
    }

    /**
     * Generate UploadedFile object from local file. For VichUploader
     *
     * @param string $filename
     *
     * @return UploadedFile
     */
    private function _generateUploadedFile($filename)
    {
        $fullPath = realpath(dirname(__FILE__) . '/images/speakers/' . $filename);
        $tmpFile = tempnam(sys_get_temp_dir(), 'speaker');
        copy($fullPath, $tmpFile);

        return new UploadedFile($tmpFile,
            $filename, null, null, null, true
        );
    }
}
