<?php

namespace Stfalcon\Bundle\SponsorBundle\Features\Context;

use Behat\BehatBundle\Context\BehatContext,
    Behat\BehatBundle\Context\MinkContext;
use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\Behat\Event\SuiteEvent,
    Behat\Behat\Event\ScenarioEvent;
use Doctrine\Bundle\FixturesBundle\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;

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
        $loader->addFixture(new \Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM\LoadSponsorData());
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures(), true);
    }

    /**
     * Find sponsor image by src
     * @param string $src
     *
     * @Given /^я должен видеть картинку с исходником "([^"]*)"$/
     */
    public function documentContainsImageWithSrc($src)
    {
        $rawImages = $this->getSession()->getPage()->findAll('css', 'div.partner img');

        $founded = false;
        foreach ($rawImages as $rawImage) {
            if ($rawImage->getAttribute('src') == $src) {
                $founded = true;
                break;
            }
        }
        assertTrue($founded);
    }
}