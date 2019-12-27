<?php

namespace App\DataFixtures\ORM;

use App\Entity\Sponsor;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Load Sponsor fixtures to database.
 */
class LoadSponsorData extends AbstractFixture implements ContainerAwareInterface
{
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
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        // Magento
        $magento = (new Sponsor())
            ->setName('Magento')
            ->setSite('http://ua.magento.com/')
            ->setFile($this->generateUploadedFile('partner-10.jpg'))
            ->setAbout('The Magento eCommerce platform serves more than 125,000 merchants worldwide and is supported by a global ecosystem of solution partners and third-party developers.')
            ->setSortOrder(10);
        $manager->persist($magento);
        $this->addReference('sponsor-magento', $magento);

        // oDesk
        $odesk = (new Sponsor())
            ->setName('oDesk')
            ->setSite('http://odesk.com/')
            ->setFile($this->generateUploadedFile('partner-11.jpg'))
            ->setAbout('oDesk is a global marketplace that helps employers hire, manage, and pay remote freelancers or teams. It\'s free to post a job and hire from over 1 million top professionals.')
            ->setSortOrder(20);
        $manager->persist($odesk);
        $this->addReference('sponsor-odesk', $odesk);

        // ePochta
        $epochta = (new Sponsor())
            ->setName('ePochta')
            ->setSite('http://www.epochta.ru/')
            ->setFile($this->generateUploadedFile('partner-12.jpg'))
            ->setSortOrder(15);
        $manager->persist($epochta);
        $this->addReference('sponsor-epochta', $epochta);

        for ($i = 0; $i < 3; ++$i) {
            $partner = (new Sponsor())
                ->setName('partner-'.$i)
                ->setSite('http://example.com/')
                ->setFile($this->generateUploadedFile('partner-'.($i + 1).'.jpg'))
                ->setAbout('oDesk is a global marketplace that helps employers hire, manage, and pay remote freelancers or teams. It\'s free to post a job and hire from over 1 million top professionals.')
                ->setSortOrder(20);
            $manager->persist($partner);
            $this->addReference('partner-'.$i, $partner);
        }

        for ($i = 4; $i < 10; ++$i) {
            $partner = (new Sponsor())
                ->setName('info partner-'.$i)
                ->setSite('http://example.com/')
                ->setFile($this->generateUploadedFile('partner-'.$i.'.jpg'))
                ->setAbout('oDesk is a global marketplace that helps employers hire, manage, and pay remote freelancers or teams. It\'s free to post a job and hire from over 1 million top professionals.')
                ->setSortOrder($i + 10);
            $manager->persist($partner);
            $this->addReference('info-partner-'.$i, $partner);
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
        if ('test' === $this->container->getParameter('kernel.environment')) {
            return null;
        }

        $fullPath = realpath($this->getKernelDir().'/../web/assets/img/partners/'.$filename);
        $tmpFile = tempnam(sys_get_temp_dir(), 'sponsor');
        if (file_exists($fullPath)) {
            copy($fullPath, $tmpFile);

            return new UploadedFile($tmpFile, $filename, null, null, false);
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
