<?php

namespace Application\Bundle\DefaultBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Behat\MinkExtension\Context\MinkContext;
use Behat\CommonContexts\SymfonyMailerContext;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Application\Bundle\DefaultBundle\Features\Context\UserContext as ApplicationDefaultBundleUserContext;
use PHPUnit_Framework_Assert as Assert;

/**
 * Feature context for ApplicationDefaultBundle.
 */
class UserFeatureContext extends MinkContext implements KernelAwareInterface
{
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
     * Constructor.
     */
    public function __construct()
    {
        $this->useContext('symfony_mailer_context', new SymfonyMailerContext());
        $this->useContext('ApplicationDefaultBundleUserContext', new ApplicationDefaultBundleUserContext($this));
    }

    /**
     * Загружаем необходимые фикстуры перед выполнением сценария.
     *
     * @BeforeScenario
     */
    public function beforeScen()
    {
        $loader = new Loader();
        $loader->addFixture(new \Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadUserData());
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();

        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures());
    }

    /**
     * @Then /^у меня должна быть подписка на все активные ивенты$/
     */
    public function iMustHaveTicketForAllEvents()
    {
        $activeEvents = $this->kernel->getContainer()->get('doctrine')->getManager()
            ->getRepository('ApplicationDefaultBundle:Event')
            ->findBy(array('active' => true));

        $user = $this->kernel->getContainer()->get('fos_user.user_manager')->findUserByEmail('test@fwdays.com');
        $tickets = $this->kernel->getContainer()->get('doctrine')->getManager()
            ->getRepository('ApplicationDefaultBundle:Ticket')->findBy(array('user' => $user->getId()));

        Assert::assertEquals(count($tickets), count($activeEvents));
    }

    /**
     * Заполнить форму регистрации.
     *
     * @param string $name     User name
     * @param string $email    Email
     * @param string $password Password
     *
     * @Given /^я заполняю обязательные поля формы: имя - "([^"]*)", e-mail - "([^"]*)", пароль - "([^"]*)"$/
     */
    public function fillRequiredFields($name, $email, $password)
    {
        $this->fillField('fos_user_registration_form_fullname', $name);
        $this->fillField('fos_user_registration_form_email', $email);
        $this->fillField('fos_user_registration_form_plainPassword', $password);
    }

    /**
     * Заполнить дополнительные поля на форме регистрации.
     *
     * @param string $country Страна
     * @param string $city    Город
     * @param string $company Компания
     * @param string $post    Должность
     *
     * @Given /^я заполняю дополнительные поля формы: страна - "([^"]*)", город - "([^"]*)", компания - "([^"]*)", должность - "([^"]*)"$/
     */
    public function fillAdditionalFields($country, $city, $company, $post)
    {
        $this->fillField('fos_user_registration_form_country', $country);
        $this->fillField('fos_user_registration_form_city', $city);
        $this->fillField('fos_user_registration_form_company', $company);
        $this->fillField('fos_user_registration_form_post', $post);
    }

    /**
     * Проверка, что отображается меню для авторизированого пользователя.
     *
     * @param string $username
     *
     * @Then /^я должен видеть меню для пользователя "([^"]*)"$/
     */
    public function iShouldSeeMenuForUser($username)
    {
        $this->assertElementOnPage('div.user-nav');
        $this->assertElementContainsText('div.user-menu a.username', $username);
    }

    /**
     * Отключаем редирект страниц.
     *
     * Это нужно для того, чтоб бы словить в профайлере количество отправленных имейлов.
     *
     * @Given /^редирект страниц отключен$/
     */
    public function followRedirectsFalse()
    {
        $this->getSession()->getDriver()->getClient()->followRedirects(false);
    }
}
