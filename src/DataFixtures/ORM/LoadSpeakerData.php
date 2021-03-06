<?php

namespace App\DataFixtures\ORM;

use App\Entity\Speaker;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * LoadSpeakerData Class.
 */
class LoadSpeakerData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    /** @var array */
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
</ul>',
    ];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    /**
     * Return fixture classes fixture is dependent on.
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadEventData::class,
        ];
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $eventJsDay = $manager->merge($this->getReference('event-jsday2018'));
        $eventPHPDay2017 = $manager->merge($this->getReference('event-phpday2017'));
        $eventPHPDay2018 = $manager->merge($this->getReference('event-phpday2018'));
        $eventHighLoad = $manager->merge($this->getReference('event-highload-day'));
        $eventNotActive = $manager->merge($this->getReference('event-not-active'));

        $speaker = (new Speaker())
            ->setName('Андрей Воробей')
            ->setEmail('a_s@test.com')
            ->setCompany('Stfalcon')
            ->setAbout((string) $this->abouts[0])
            ->setSlug('andrew-vorobey')
            ->setFile($this->generateUploadedFile('speaker-1.jpg'))
            ->setEvents(new ArrayCollection([$eventJsDay, $eventNotActive]))
            ->setCandidateEvents(new ArrayCollection([$eventPHPDay2017, $eventHighLoad]))
            ->setSortOrder(1);
        $manager->persist($speaker);
        $this->addReference('speaker', $speaker);

        for ($i = 0; $i < 5; ++$i) {
            $speaker = (new Speaker())
                ->setName('speaker'.$i)
                ->setEmail('test@test.com')
                ->setCompany('random')
                ->setAbout($this->abouts[2])
                ->setSlug('speaker'.$i)
                ->setFile($this->generateUploadedFile('speaker-'.($i + 4).'.jpg'))
                ->setEvents(new ArrayCollection([$eventPHPDay2017, $eventHighLoad]))
                ->setCandidateEvents(new ArrayCollection([$eventNotActive, $eventJsDay, $eventPHPDay2018]))
                ->setSortOrder(5);
            $manager->persist($speaker);
            $this->addReference('speaker-'.$i, $speaker);
        }
        $manager->flush();
    }

    /**
     * Generate UploadedFile object from local file. For VichUploader.
     *
     * @param string $filename
     *
     * @return UploadedFile|null
     */
    private function generateUploadedFile($filename): ?UploadedFile
    {
        $fullPath = \realpath($this->getKernelDir().'/../public/build/img/partners/'.$filename);
        $tmpFile = \tempnam(sys_get_temp_dir(), 'speaker');
        if (\file_exists($fullPath)) {
            \copy($fullPath, $tmpFile);

            return new UploadedFile($tmpFile, $filename, null, null, true);
        }

        return null;
    }

    /**
     * @return string
     */
    private function getKernelDir()
    {
        return $this->container->get('kernel')->getRootDir();
    }
}
