<?php

namespace Application\Bundle\DefaultBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;

use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Exception\PendingException;

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

use Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\ScenarioEvent;

use Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;

/**
 * Feature context.
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
    /**
     * @var \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    private $kernel;

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
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures(), true);
    }

}
