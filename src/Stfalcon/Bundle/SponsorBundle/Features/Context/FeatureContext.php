<?php

namespace Stfalcon\Bundle\SponsorBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;

use Behat\Symfony2Extension\Context\KernelAwareInterface,
    Behat\MinkExtension\Context\MinkContext;

use Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context for StfalconSponsorBundle
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    protected $kernel;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     *
     * @return null
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @BeforeScenario
     */
    public function beforeScen()
    {
        $loader = new Loader();
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadNewsData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadPagesData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadSpeakerData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadReviewData());
        $loader->addFixture(new \Stfalcon\Bundle\PaymentBundle\DataFixtures\ORM\LoadPaymentData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadTicketData());
        $loader->addFixture(new \Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM\LoadSponsorData());
        $loader->addFixture(new \Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM\LoadEventSponsorData());
        $loader->addFixture(new \Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM\LoadCategoryData());
        $loader->addFixture(new \Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData());
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures(), true);
    }

    /**
     * Check that some element contains image from some source
     *
     * @param string $src     Source of image
     * @param string $element Selector engine name
     *
     * @Given /^я должен видеть картинку "([^"]*)" внутри элемента "([^"]*)"$/
     */
    public function elementContainsImageWithSrc($src, $element)
    {
        assertTrue($this->_findImageWithSrc($src, $element));
    }

    /**
     * Check that some element not contains image from some source
     *
     * @param string $src     Source of image
     * @param string $element Selector engine name
     *
     * @Given /^я не должен видеть картинку "([^"]*)" внутри элемента "([^"]*)"$/
     */
    public function documentNotContainsImageWithSrc($src, $element)
    {
        assertTrue(!$this->_findImageWithSrc($src, $element));
    }

    private function _findImageWithSrc($src, $element)
    {
        $rawImages = $this->getSession()->getPage()->findAll('css', $element);

        foreach ($rawImages as $rawImage) {
            if (strstr($rawImage->getAttribute('src'), $src)) {
                return true;
            }
        }

        return false;
    }

}
