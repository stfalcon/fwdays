<?php

namespace Application\Bundle\DefaultBundle\Features\Context;

use Behat\BehatBundle\Context\BehatContext,
    Behat\BehatBundle\Context\MinkContext;
use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Feature context.
 */
//class FeatureContext extends BehatContext //MinkContext if you want to test web
class FeatureContext extends MinkContext //MinkContext if you want to test web
{

// Place your definition and hook methods here:

    /**
     * @Given /^I have done something with "([^"]*)"$/
     */
    public function iHaveDoneSomethingWith($argument)
    {
        $container = $this->getContainer();
        $container->get('some_service')->doSomethingWith($argument);
    }

}
