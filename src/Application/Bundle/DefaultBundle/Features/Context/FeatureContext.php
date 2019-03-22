<?php

namespace Application\Bundle\DefaultBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;
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
     * @var \Symfony\Component\HttpKernel\KernelInterface
     */
    protected $kernel;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

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
    public function beforeScenario()
    {
        $loader = new Loader();
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData());
//        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadNewsData());
        $loader->addFixture(new \Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData());

        $this->em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $this->em->getConnection()->executeUpdate('SET foreign_key_checks = 0;');
        $purger = new ORMPurger();
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($this->em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures(), true);
        $this->em->getConnection()->executeUpdate('SET foreign_key_checks = 1;');
    }

    /**
     * @Given /^пользователь "([^"]*)" подписан на рассылку$/
     */
    public function userIsSubscribed($username)
    {
        $user = $this->em->getRepository('ApplicationUserBundle:User')
            ->findOneBy(['username' => $username]);

        Assert::assertTrue($user->isSubscribe());
    }

    /**
     * @Given /^пользователь "([^"]*)" перешел на ссылку отписаться от рассылки$/
     */
    public function userGoToLinkUnsubscribe($username)
    {
        $user = $this->em->getRepository('ApplicationUserBundle:User')
            ->findOneBy(['username' => $username]);

        $url = $this->kernel->getContainer()->get('router')->generate(
            'unsubscribe',
            [
                'hash' => $user->getSalt(),
                'userId' => $user->getId(),
            ]
        );

        $this->visit($url);
    }

    /**
     * @Given /^пользователь "([^"]*)" перешел на ссылку подписаться на рассылку$/
     */
    public function userGoToLinkSubscribe($username)
    {
        $user = $this->em->getRepository('ApplicationUserBundle:User')
            ->findOneBy(['username' => $username]);

        $url = $this->kernel->getContainer()->get('router')->generate(
            'subscribe',
            [
                'hash' => $user->getSalt(),
                'userId' => $user->getId(),
            ]
        );

        $this->visit($url);
    }

    /**
     * @Given /^пользователь "([^"]*)" должен быть подписан на рассылку$/
     */
    public function userShouldBeSubscribed($username)
    {
        $user = $this->em->getRepository('ApplicationUserBundle:User')
            ->findOneBy(['username' => $username]);
        $this->em->refresh($user);

        Assert::assertNotNull($user);
        Assert::assertTrue($user->isSubscribe());
    }

    /**
     * @Given /^пользователь "([^"]*)" должен быть отписан от рассылки$/
     */
    public function userIsUnsubscribed($username)
    {
        $user = $this->em->getRepository('ApplicationUserBundle:User')
            ->findOneBy(['username' => $username]);
        $this->em->refresh($user);

        Assert::assertNotNull($user);
        Assert::assertFalse($user->isSubscribe());
    }
}
