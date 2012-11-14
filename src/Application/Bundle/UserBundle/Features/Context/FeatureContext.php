<?php

namespace Application\Bundle\UserBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;

use Behat\Symfony2Extension\Context\KernelAwareInterface,
    Behat\MinkExtension\Context\MinkContext;

use Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context for ApplicationUserBundle
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
        $loader->addFixture(new \Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData());
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $purger   = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures(), true);
    }

    /**
     * Активация профиля пользователя
     *
     * @Given /^я активирую свой "([^"]*)" профиль$/
     */
    public function profileActivation($mail)
    {
        $em = $this->kernel->getContainer()->get('doctrine')->getEntityManager();
        $user = $em->getRepository('ApplicationUserBundle:User')
                   ->findOneBy(array('username' => $mail ));

        if (!$user) {
            throw new \Behat\Gherkin\Exception\Exception('user not found');
        }

        $user   ->setEnabled(true);
        $em     ->persist($user);
        $em     ->flush();
    }


    /**
     * @Given /^у меня должна быть подписка на все активные ивенты$/
     */
    public function iMustHaveTicketForAllEvents()
    {
        $activeEvents = $this->kernel->getContainer()->get('doctrine')->getEntityManager()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(array('active' => true ));

        $user = $this->kernel->getContainer()->get('fos_user.user_manager')->findUserByEmail('test@fwdays.com');
        $tickets = $this->kernel->getContainer()->get('doctrine')->getEntityManager()
            ->getRepository('StfalconEventBundle:Ticket')->findBy(array('user' => $user->getId()));

        assertEquals(count($tickets), count($activeEvents));
    }

    /**
     * @param string $mail
     * @Given /^обязательные поля должны быть заполнены у "([^"]*)"$/
     */
    public function requireFieldsMustBeFilled($mail)
    {
        $user = $this->kernel->getContainer()->get('fos_user.user_manager')->findUserByEmail($mail);

        assertTrue(count($user->getFullname()) > 0);
        assertContains($user->getEmail(),$mail);

    }

    /**
     * @param string $mail
     * @Given /^не обязательные поля должны быть заполнены у "([^"]*)"$/
     */
    public function AllFieldsMustBeFilled($mail)
    {
        $user = $this->kernel->getContainer()->get('fos_user.user_manager')->findUserByEmail($mail);

        assertTrue(count($user->getCompany()) > 0);
        assertTrue(count($user->getCity()) > 0);
        assertTrue(count($user->getPost()) > 0);
        assertTrue(count($user->getCountry()) > 0);
    }
}
