<?php

namespace Stfalcon\Bundle\EventBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\DataFixtures\DependentFixtureInterface,
    Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Stfalcon\Bundle\EventBundle\Entity\Speaker;

/**
 * LoadSpeakerData Class
 */
class LoadSpeakerData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    private $abouts = [
        '<ul class="presenter-facts">
<li>Senior Developer</li>
<li>ZF2 Component Maintainer и Contributor</li><li>Phing Contributor</li>
<li>Разработчик Phrozn</li>
<li>Ведущий подкаста <a href="http://zf.rpod.ru/">zftalk.dev</a></li>
</ul>',
        '<ul class="presenter-facts">
    <li>CTO at Attendify;</li>
    <li>Mostly Clojure engineer with years and years of production experience;</li>
    <li>Passionate about distributed systems, smart compilers, and useful type systems;</li>
    <li>Author of a few library in functional programming and concurrency;</li>
<li><a href="https://twitter.com/kachayev">Twitter</a></li>
</ul>',
        '<ul>
<li>Front/back developer</li>
<li>Tech. lead</li>
<li><a href="https://twitter.com/nimnull">Twitter</a></li>
</ul>'
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

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
        $eventJsDay  = $manager->merge($this->getReference('event-jsday2018'));
        $eventPHPDay2017 = $manager->merge($this->getReference('event-phpday2017'));
        $eventPHPDay2018 = $manager->merge($this->getReference('event-phpday2018'));
        $eventHighLoad = $manager->merge($this->getReference('event-highload-day'));
        $eventNotActive = $manager->merge($this->getReference('event-not-active'));

        $speaker = (new Speaker())
            ->setName('Андрей Воробей')
            ->setEmail('a_s@test.com')
            ->setCompany('Stfalcon')
            ->setAbout($this->abouts[0])
            ->setSlug('andrew-vorobey')
            ->setFile($this->_generateUploadedFile('speaker-1.jpg'))
            ->setEvents([$eventJsDay, $eventNotActive])
            ->setCandidateEvents([$eventPHPDay2017, $eventHighLoad]);
        $manager->persist($speaker);
        $this->addReference('speaker-shkodyak', $speaker);

        $speaker = (new Speaker())
            ->setName('Валера Питерский')
            ->setEmail('v_r@test.com')
            ->setCompany('ZZZ')
            ->setAbout($this->abouts[1])
            ->setSlug('valeriy-pitersky')
            ->setFile($this->_generateUploadedFile('speaker-1.jpg'))
            ->setEvents([$eventPHPDay2018, $eventNotActive])
            ->setCandidateEvents([$eventPHPDay2017]);
        $manager->persist($speaker);
        $this->addReference('speaker-rabievskiy', $speaker);

        for ($i = 0; $i < 4; $i++ ) {
            $speaker = (new Speaker())
                ->setName('speaker'.$i)
                ->setEmail('test@test.com')
                ->setCompany('random')
                ->setAbout($this->abouts[2])
                ->setSlug('speaker'.$i)
                ->setFile($this->_generateUploadedFile('speaker-'.($i+4).'.jpg'))
                ->setEvents([$eventPHPDay2017, $eventHighLoad])
                ->setCandidateEvents([$eventNotActive, $eventJsDay, $eventPHPDay2018]);
            $manager->persist($speaker);
            $this->addReference('speaker-'.$i, $speaker);
        }




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
        $fullPath = realpath($this->getKernelDir() . '/../web/assets/img/speakers/' . $filename);
        $tmpFile = tempnam(sys_get_temp_dir(), 'speaker');
        copy($fullPath, $tmpFile);

        return new UploadedFile($tmpFile,
            $filename, null, null, null, true
        );
    }

    private function getKernelDir()
    {
        return $this->container->get('kernel')->getRootDir();
    }
}
