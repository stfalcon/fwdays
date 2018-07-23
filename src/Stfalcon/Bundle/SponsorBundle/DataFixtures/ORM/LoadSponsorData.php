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
            ->setSlug('magento')
            ->setSite('http://ua.magento.com/')
            ->setFile($this->_generateUploadedFile('partner-10.jpg'))
            ->setAbout('The Magento eCommerce platform serves more than 125,000 merchants worldwide and is supported by a global ecosystem of solution partners and third-party developers.')
            ->setSortOrder(10)
            ->setOnMain(true);
        $manager->persist($magento);
        $this->addReference('sponsor-magento', $magento);

        // oDesk
        $odesk = (new Sponsor())
            ->setName('oDesk')
            ->setSlug('odesk')
            ->setSite('http://odesk.com/')
            ->setFile($this->_generateUploadedFile('partner-11.jpg'))
            ->setAbout('oDesk is a global marketplace that helps employers hire, manage, and pay remote freelancers or teams. It\'s free to post a job and hire from over 1 million top professionals.')
            ->setSortOrder(20)
            ->setOnMain(true);
        $manager->persist($odesk);
        $this->addReference('sponsor-odesk', $odesk);

        // ePochta
        $epochta = (new Sponsor())
            ->setName('ePochta')
            ->setSlug('epochta')
            ->setSite('http://www.epochta.ru/')
            ->setFile($this->_generateUploadedFile('partner-12.jpg'))
            ->setOnMain(false)
            ->setSortOrder(15);
        $manager->persist($epochta);
        $this->addReference('sponsor-epochta', $epochta);

        for ($i = 0; $i < 3; ++$i) {
            $partner = (new Sponsor())
                ->setName('partner-'.$i)
                ->setSlug('partner-'.$i)
                ->setSite('http://example.com/')
                ->setFile($this->_generateUploadedFile('partner-'.($i + 1).'.jpg'))
                ->setAbout('oDesk is a global marketplace that helps employers hire, manage, and pay remote freelancers or teams. It\'s free to post a job and hire from over 1 million top professionals.')
                ->setSortOrder(20)
                ->setOnMain(true);
            $manager->persist($partner);
            $this->addReference('partner-'.$i, $partner);
        }

        for ($i = 0; $i < 6; ++$i) {
            $partner = (new Sponsor())
                ->setName('info partner-'.$i)
                ->setSlug('info-partner-'.$i)
                ->setSite('http://example.com/')
                ->setFile($this->_generateUploadedFile('partner-'.($i + 5).'.jpg'))
                ->setAbout('oDesk is a global marketplace that helps employers hire, manage, and pay remote freelancers or teams. It\'s free to post a job and hire from over 1 million top professionals.')
                ->setSortOrder($i + 10)
                ->setOnMain(true);
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
    private function _generateUploadedFile($filename)
    {
        $fullPath = realpath($this->getKernelDir().'/../web/assets/img/partners/'.$filename);
        $tmpFile = tempnam(sys_get_temp_dir(), 'sponsor');
        if (file_exists($fullPath)) {
            copy($fullPath, $tmpFile);

            return new UploadedFile($tmpFile, $filename, null, null, null, true);
        }

        return null;
    }

    private function getKernelDir()
    {
        return $this->container->get('kernel')->getRootDir();
    }
}
