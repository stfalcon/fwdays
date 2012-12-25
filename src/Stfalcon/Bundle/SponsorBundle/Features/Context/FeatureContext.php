<?php

namespace Stfalcon\Bundle\SponsorBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;

use Behat\Symfony2Extension\Context\KernelAwareInterface,
    Behat\MinkExtension\Context\MinkContext,
    Behat\CommonContexts\DoctrineFixturesContext;

use Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Application\Bundle\DefaultBundle\Features\Context\LoadFixturesContext;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context for StfalconSponsorBundle
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->useContext('DoctrineFixturesContext', new DoctrineFixturesContext());
    }

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
        $this->getMainContext()
            ->getSubcontext('DoctrineFixturesContext')
            ->loadFixtureClass($loader, 'Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM\LoadEventSponsorData');

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
