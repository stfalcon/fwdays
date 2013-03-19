<?php

namespace Application\Bundle\UserBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;

use Behat\Symfony2Extension\Context\KernelAwareInterface,
    Behat\MinkExtension\Context\MinkContext,
    Behat\CommonContexts\SymfonyMailerContext;

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
     * Constructor
     */
    public function __construct()
    {
        $this->useContext('symfony_mailer_context', new SymfonyMailerContext());
    }

    /**
     * Загружаем необходимые фикстуры перед выполнением сценария
     *
     * @BeforeScenario
     */
    public function beforeScen()
    {
        $loader = new Loader();
        $loader->addFixture(new \Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData());
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();

        $purger   = new ORMPurger();
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
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(array('active' => true ));

        $user = $this->kernel->getContainer()->get('fos_user.user_manager')->findUserByEmail('test@fwdays.com');
        $tickets = $this->kernel->getContainer()->get('doctrine')->getManager()
            ->getRepository('StfalconEventBundle:Ticket')->findBy(array('user' => $user->getId()));

        assertEquals(count($tickets), count($activeEvents));
    }

    /**
     * Заполнить форму регистрации
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
     * Заполнить дополнительные поля на форме регистрации
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
     * Вход в учетную запись по логину и паролю
     *
     * В этом методе заполняются поля: логин и пароль, после чего нажимается кнопка "Вход"
     *
     * @param string $username Имя пользователя
     * @param string $password Пароль учетной записи
     *
     * @Given /^я вхожу в учетную запись с именем "([^"]*)" и паролем "([^"]*)"$/
     */
    public function login($username, $password)
    {
        $this->fillField('username', $username);
        $this->fillField('password', $password);
        $this->pressButton('Войти');
    }

    /**
     * Проверка, что отображается меню для авторизированого пользователя
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
     * Отключаем редирект страниц
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
