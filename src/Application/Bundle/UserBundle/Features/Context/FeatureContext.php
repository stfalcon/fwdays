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
     * Loads the profiler's profile.
     *
     * If no token has been given, the debug token of the last request will
     * be used.
     *
     * @param string $token
     *
     * @throws \RuntimeException
     *
     * @return \Symfony\Component\HttpKernel\Profiler\Profile
     */
    public function loadProfile($token = null)
    {
        if (null === $token) {
            $headers = $this->getSession()->getResponseHeaders();

            if (!isset($headers['X-Debug-Token']) && !isset($headers['x-debug-token'])) {
                throw new \RuntimeException('Debug-Token not found in response headers. Have you turned on the debug flag?');
            }
            $token = isset($headers['X-Debug-Token']) ? $headers['X-Debug-Token'] : $headers['x-debug-token'];
        }

        return $this->kernel->getContainer()->get('profiler')->loadProfile($token);
    }

    /**
     * @Then /^у меня должна быть подписка на все активные ивенты$/
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
     * @Then /^я должен быть на странице подтверждения имейла$/
     */
    public function checkThatCurrentPageIsCheckMailPage()
    {
        $this->assertPageAddress('/register/check-email');
        $this->assertResponseStatus(200);
    }

    /**
     * @Then /^я должен видеть сообщение, что пользователь успешно создан$/
     */
    public function iShouldSeeMessageThatUserCreatedSuccessfully()
    {
        $this->assertPageContainsText('Пользователь успешно создан');
    }

    /**
     * @param string $emailToConfirm
     *
     * @Then /^я должен видеть сообщение, что на почту "([^"]*)" выслано письмо для подтверждения регистрации$/
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
        $this->iAmOnHomepage();
    }

    /**
     * @param string $sendTo
     *
     * @Then /^письмо для подтверждения регистрации должно быть выслано на e-mail "([^"]*)"$/
     */
    public function emailWithSubjectShouldHaveBeenSent($sendTo)
    {
        $mailer = $this->loadProfile()->getCollector('swiftmailer');
        $this->getSession()->getDriver()->getClient()->followRedirects(true);

        if (0 === $mailer->getMessageCount()) {
            throw new \RuntimeException('No emails have been sent.');
        }

        $subject= "Добро пожаловать {$sendTo}!";

        $foundToAddresses = null;
        $foundSubjects = array();
        foreach ($mailer->getMessages() as $message) {
            $foundSubjects[] = $message->getSubject();

            if ($subject === trim($message->getSubject())) {
                $foundToAddresses = implode(', ', array_keys($message->getTo()));

                if (null !== $sendTo) {
                    $toAddresses = $message->getTo();
                    if (array_key_exists($sendTo, $toAddresses)) {
                        // found, and to address matches
                        return;
                    }

                    // check next message
                    continue;
                } else {
                    // found, and to email isn't checked
                    return;
                }
            }
        }

        if (!$foundToAddresses) {
            if (!empty($foundSubjects)) {
                throw new \RuntimeException(sprintf('Subject "%s" was not found, but only these subjects: "%s"', $subject, implode('", "', $foundSubjects)));
            }

            // not found
            throw new \RuntimeException(sprintf('No message with subject "%s" found.', $subject));
        }

        throw new \RuntimeException(sprintf('Subject found, but "%s" is not among to-addresses: %s', $sendTo, $foundToAddresses));
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
     * @Then /^я должен видеть свой имейл "([^"]*)"$/
     */
    public function iShouldSeeMyEmail($email)
    {
        $this->assertFieldContains('fos_user_profile_form_email', $email);
    }

    /**
     * @param string $username
     *
     * @Then /^я должен видеть свое имя "([^"]*)"$/
     */
    public function iShouldSeeMyName($username)
    {
        $this->assertFieldContains('fos_user_profile_form_fullname', $username);
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
}
