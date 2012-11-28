<?php

namespace Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture,
    Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Stfalcon\Bundle\SponsorBundle\Entity\Sponsor;

/**
 * Load Sponsor fixtures to database
 */
class LoadSponsorData extends AbstractFixture
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // Magento
        $magento = new Sponsor();
        $magento->setName('Magento');
        $magento->setSlug('magento');
        $magento->setSite('http://ua.magento.com/');
        $magento->setFile($this->_generateUploadedFile('magento.png'));
        $magento->setAbout('The Magento eCommerce platform serves more than 125,000 merchants worldwide and is supported by a global ecosystem of solution partners and third-party developers.');
        $magento->setSortOrder(10);
        $magento->setOnMain(true);
        $manager->persist($magento);
        $this->addReference('sponsor-magento', $magento);

        // oDesk
        $odesk = new Sponsor();
        $odesk->setName('oDesk');
        $odesk->setSlug('odesk');
        $odesk->setSite('http://odesk.com/');
        $odesk->setFile($this->_generateUploadedFile('odesk.jpg'));
        $odesk->setAbout('oDesk is a global marketplace that helps employers hire, manage, and pay remote freelancers or teams. It\'s free to post a job and hire from over 1 million top professionals.');
        $odesk->setSortOrder(20);
        $odesk->setOnMain(true);
        $manager->persist($odesk);
        $this->addReference('sponsor-odesk', $odesk);

        // ePochta
        $epochta = new Sponsor();
        $epochta->setName('ePochta');
        $epochta->setSlug('epochta');
        $epochta->setSite('http://www.epochta.ru/');
        $epochta->setFile($this->_generateUploadedFile('epochta.png'));
        $epochta->setOnMain(false);
        $epochta->setSortOrder(15);
        $manager->persist($epochta);
        $this->addReference('sponsor-epochta', $epochta);

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
        $fullPath = realpath(dirname(__FILE__) . '/images/' . $filename);
        $tmpFile = tempnam(sys_get_temp_dir(), 'sponsor');
        copy($fullPath, $tmpFile);

        return new UploadedFile($tmpFile,
            $filename, null, null, null, true
        );
    }
}
