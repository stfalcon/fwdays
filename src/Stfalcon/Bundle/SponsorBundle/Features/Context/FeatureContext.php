<?php

namespace Application\Bundle\DefaultBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;
use Behat\CommonContexts\DoctrineFixturesContext;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use PHPUnit_Framework_Assert as Assert;

/**
 * Feature context for ApplicationDefaultBundle.
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->useContext('DoctrineFixturesContext', new DoctrineFixturesContext());
    }

    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    protected $kernel;

    /**
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
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
            ->loadFixtureClass($loader, 'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadEventSponsorData');

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures(), true);
    }

    /**
     * Check that some element contains image from some source.
     *
     * @param string $src     Source of image
     * @param string $element Selector engine name
     *
     * @Given /^я должен видеть картинку "([^"]*)" внутри элемента "([^"]*)"$/
     */
    public function elementContainsImageWithSrc($src, $element)
    {
        Assert::assertTrue($this->_findImageWithSrc($src, $element));
    }

    /**
     * Check that some element not contains image from some source.
     *
     * @param string $src     Source of image
     * @param string $element Selector engine name
     *
     * @Given /^я не должен видеть картинку "([^"]*)" внутри элемента "([^"]*)"$/
     */
    public function documentNotContainsImageWithSrc($src, $element)
    {
        Assert::assertTrue(!$this->_findImageWithSrc($src, $element));
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
