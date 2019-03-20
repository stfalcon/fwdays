<?php

namespace Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Stfalcon\Bundle\SponsorBundle\Entity\Sponsor;

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
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Magento
        $magento = (new Sponsor())
            ->setName('Magento')
            ->setSite('http://ua.magento.com/')
            ->setFile($this->generateUploadedFile('partner-10.jpg'))
            ->setSortOrder(10)
            ;
        $manager->persist($magento);
        $this->addReference('sponsor-magento', $magento);

        // oDesk
        $odesk = (new Sponsor())
            ->setName('oDesk')
            ->setSite('http://odesk.com/')
            ->setFile($this->generateUploadedFile('partner-11.jpg'))
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
                ->setSortOrder(20);
            $manager->persist($partner);
            $this->addReference('partner-'.$i, $partner);
        }

        for ($i = 0; $i < 6; ++$i) {
            $partner = (new Sponsor())
                ->setName('info partner-'.$i)
                ->setSite('http://example.com/')
                ->setFile($this->generateUploadedFile('partner-'.($i + 5).'.jpg'))
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
     * @return UploadedFile
     */
    private function generateUploadedFile($filename)
    {
        $fullPath = realpath($this->getKernelDir().'/../web/assets/img/partners/'.$filename);
        $tmpFile = tempnam(sys_get_temp_dir(), 'sponsor');
        if (file_exists($fullPath)) {
            copy($fullPath, $tmpFile);

            return new UploadedFile($tmpFile, $filename, null, null, null, true);
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
