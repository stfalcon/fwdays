<?php

namespace Application\Bundle\DefaultBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;

use Behat\Symfony2Extension\Context\KernelAwareInterface,
    Behat\MinkExtension\Context\MinkContext;

use Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context for ApplicationDefaultBundle
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
        $loader->addFixture(new \Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData());

        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');
        /** @var $em \Doctrine\ORM\EntityManager */
        $connection = $em->getConnection();

        $connection->beginTransaction();

        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        $connection->commit();

        $purger   = new ORMPurger();
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();

        $connection->beginTransaction();
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
        $connection->commit();

        $executor->execute($loader->getFixtures(), true);
    }

    /**
     * @Given /^пользователь "([^"]*)" подписан на рассылку$/
     */
    public function userIsSubscribed($username)
    {
        /**
         * @var \Doctrine\ORM\EntityManager
         */
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('ApplicationUserBundle:User')
            ->findOneBy(['username' => $username]);

        assertTrue($user->isSubscribe());
    }

    /**
     * @Given /^пользователь "([^"]*)" перешел на ссылку отписаться от рассылки$/
     */
    public function userGoToLinkUnsubscribe($username)
    {
        /**
         * @var \Doctrine\ORM\EntityManager
         */
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('ApplicationUserBundle:User')
            ->findOneBy(['username' => $username]);

        $url = $this->kernel->getContainer()->get('router')->generate(
            'unsubscribe',
            [
                'hash' => $user->getSalt(),
                'userId' => $user->getId()
            ]
        );

        $this->visit($url);
    }

    /**
     * @Given /^пользователь "([^"]*)" должен быть отписан от рассылки$/
     */
    public function userIsUnsubscribed($username)
    {
        /**
         * @var \Doctrine\ORM\EntityManager
         */
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('ApplicationUserBundle:User')
            ->findOneBy(['username' => $username]);

        assertFalse($user->isSubscribe());
    }

}