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
     * Загружаем необходимые фикстуры перед выполнением сценария
     *
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
        $executor->execute($loader->getFixtures());
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
     * @Given /^я на странице регистрации$/
     */
    public function iAmOnTheRegistrationPage()
    {
        $this->visit('/register/');
        $this->assertPageAddress('/register/');
        $this->assertResponseStatus(200);
    }

    /**
     * Заполнить форму регистрации
     *
     * @param string $name     User name
     * @param string $email    Email
     * @param string $password Password
     *
     * @Given /^я заполняю обязательные поля формы: имя - "([^"]*)", имейл - "([^"]*)", пароль - "([^"]*)"$/
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
     * @Given /^я должен быть на странице подтверждения имейла$/
     */
    public function checkThatCurrentPageIsCheckMailPage()
    {
        $this->assertPageAddress('/register/check-email');
        $this->assertResponseStatus(200);
    }

    /**
     * @Given /^я должен видеть сообщение, чтоб пользователь успешно создан$/
     */
    public function iShouldSeeMessageThatUserCreatedSuccessfully()
    {
        $this->assertPageContainsText('Пользователь успешно создан');
    }

    /**
     * @param string $emailToConfirm
     *
     * @Given /^я должен видеть сообщение, что на почту "([^"]*)" выслано письмо для подтверждения регистрации$/
     */
    public function iShouldSeeMessageThatConfirmationMailWasSent($emailToConfirm)
    {
        $this->assertPageContainsText(
            "На электронную почту {$emailToConfirm} выслано письмо с ссылкой для подтверждения регистрации."
        );
    }

    /**
     * @Given /^я на странице логина$/
     */
    public function iAmOnLoginPage()
    {
        $this->visit('/login');
        $this->assertPageAddress('/login');
        $this->assertResponseStatus(200);
    }

    /**
     * @Given /^я должен быть на главной странице$/
     */
    public function iShouldBeOnTheMainPage()
    {
        $this->assertPageAddress('/');
        $this->assertResponseStatus(200);
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
     * Вход в учетную запись по логину и паролю
     *
     * Разнице между этим методом и методом "login" в том, что в этом методы выполняются все нужные дествий для входа
     * в учетную запись: переход на страницу входа, заполнение формы, проверка адреса и кода страницы.
     *
     * @param string $username Имя пользователя
     * @param string $password Пароль учетной записи
     *
     * @Given /^я вошел в учетную запись с именем "([^"]*)" и паролем "([^"]*)"$/
     */
    public function goToLoginPageAndLogin($username, $password)
    {
        $this->iAmOnLoginPage();
        $this->login($username, $password);
        $this->iShouldBeOnTheMainPage();
    }

    /**
     * @Given /^я перехожу на страницу редактирования профиля$/
     */
    public function goToTheProfileEditPage()
    {
        $this->visit('/profile/edit');
        $this->assertPageAddress('/profile/edit');
        $this->assertResponseStatus(200);
    }

    /**
     * @param string $email
     *
     * @Given /^я должен видеть свой имейл "([^"]*)"$/
     */
    public function iShouldSeeMyEmail($email)
    {
        $this->assertFieldContains('fos_user_profile_form_email', $email);
    }

    /**
     * @param string $username
     *
     * @Given /^я должен видеть свое имя "([^"]*)"$/
     */
    public function iShouldSeeMyName($username)
    {
        $this->assertFieldContains('fos_user_profile_form_fullname', $username);
    }

    /**
     * @param string $country
     *
     * @Given /^я должен видеть название своей страны "([^"]*)"$/
     */
    public function iShouldSeeMyCountry($country)
    {
        $this->assertFieldContains('fos_user_profile_form_country', $country);
    }

    /**
     * @param string $city
     *
     * @Given /^я должен видеть название своего города "([^"]*)"$/
     */
    public function iShouldSeeMyCity($city)
    {
        $this->assertFieldContains('fos_user_profile_form_city', $city);
    }

    /**
     * @param string $company
     *
     * @Given /^я должен видеть название своей компании "([^"]*)"$/
     */
    public function iShouldSeeMyCompany($company)
    {
        $this->assertFieldContains('fos_user_profile_form_company', $company);
    }

    /**
     * @param string $post
     *
     * @Given /^я должен видеть название своей должности "([^"]*)"$/
     */
    public function iShouldSeeMyPost($post)
    {
        $this->assertFieldContains('fos_user_profile_form_post', $post);
    }

    /**
     * @Given /^дополнительные поля должны быть пустыми$/
     */
    public function additionalFieldsShouldBeEmpty()
    {
        // Find fields on page
        $country = $this->getSession()->getPage()->find('css', '#fos_user_profile_form_country');
        $city    = $this->getSession()->getPage()->find('css', '#fos_user_profile_form_city');
        $company = $this->getSession()->getPage()->find('css', '#fos_user_profile_form_company');
        $post    = $this->getSession()->getPage()->find('css', '#fos_user_profile_form_post');
        // Check that they are empty
        assertEmpty($country->getText(), 'Поле "Страна" непустое');
        assertEmpty($city->getText(), 'Поле "Город" непустое');
        assertEmpty($company->getText(), 'Поле "Компания" непустое');
        assertEmpty($post->getText(), 'Поле "Должность" непустое');
    }
}
