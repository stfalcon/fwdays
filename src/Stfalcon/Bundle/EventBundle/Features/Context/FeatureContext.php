<?php

namespace Stfalcon\Bundle\EventBundle\Features\Context;

use Behat\BehatBundle\Context\BehatContext,
    Behat\BehatBundle\Context\MinkContext;
use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\ScenarioEvent;
use Symfony\Bundle\DoctrineFixturesBundle\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

/**
 * Feature context.
 */
class FeatureContext extends MinkContext //MinkContext if you want to test web
{

    /**
     * @BeforeScenario
     */
    public function beforeScen()
    {
        $loader = new Loader($this->getContainer());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadNewsData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadPagesData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadSpeakersData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadReviewData());
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures(), true);
    }

}