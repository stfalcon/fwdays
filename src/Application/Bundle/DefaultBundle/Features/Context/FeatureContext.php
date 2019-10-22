<?php

namespace Application\Bundle\DefaultBundle\Features\Context;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareInterface;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use PHPUnit_Framework_Assert as Assert;
use Symfony\Component\HttpKernel\KernelInterface;

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
        $loader->addFixture(new \Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadEventData());
        $loader->addFixture(new \Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadUserData());

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
        $user = $this->em->getRepository('ApplicationDefaultBundle:User')
            ->findOneBy(['username' => $username]);

        Assert::assertTrue($user->isSubscribe());
    }

    /**
     * @Given /^пользователь "([^"]*)" перешел на ссылку отписаться от рассылки$/
     */
    public function userGoToLinkUnsubscribe($username)
    {
        $user = $this->em->getRepository('ApplicationDefaultBundle:User')
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
        $user = $this->em->getRepository('ApplicationDefaultBundle:User')
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
        $user = $this->em->getRepository('ApplicationDefaultBundle:User')
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
        $user = $this->em->getRepository('ApplicationDefaultBundle:User')
            ->findOneBy(['username' => $username]);
        $this->em->refresh($user);

        Assert::assertNotNull($user);
        Assert::assertFalse($user->isSubscribe());
    }

    /**
     * Check that some element contains image from some source.
     *
     * @param string $src     Source of image
     * @param string $element Selector engine name
     *
     * @Given /^я должен видеть картинку "([^"]*)" внутри элемента "([^"]*)"$/
     */
    public function elementContainsImageWithSrc($src, $element)
    {
        Assert::assertTrue($this->_findImageWithSrc($src, $element));
    }

    /**
     * Check that some element not contains image from some source.
     *
     * @param string $src     Source of image
     * @param string $element Selector engine name
     *
     * @Given /^я не должен видеть картинку "([^"]*)" внутри элемента "([^"]*)"$/
     */
    public function documentNotContainsImageWithSrc($src, $element)
    {
        Assert::assertTrue(!$this->_findImageWithSrc($src, $element));
    }

    private function _findImageWithSrc($src, $element)
    {
        $rawImages = $this->getSession()->getPage()->findAll('css', $element);

        foreach ($rawImages as $rawImage) {
            if (strstr($rawImage->getAttribute('src'), $src)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Wait while jQuery finished on page
     * Works only with Selenium2Driver.
     *
     * @param StepEvent $event
     *
     * @AfterStep
     */
    public function checkFinishJS(StepEvent $event)
    {
        $driver = $this->getSession()->getDriver();
        if ($driver instanceof Selenium2Driver) {
            $currentUrl = $this->getSession()->getCurrentUrl();
            if (false !== strpos($currentUrl, $this->getMinkParameter('base_url'))) {
                $this->getSession()->wait(
                    10000,
                    '(typeof window.jQuery == "function" && 0 === jQuery.active && 0 === jQuery(\':animated\').length)'
                );
            }
        }
    }

    /**
     * @Then /^я жду$/
     */
    public function iWait()
    {
        $this->getSession()->wait(5000);
    }

    /**
     * Проверка или файл является PDF-файлом
     *
     * @Then /^это PDF-файл$/
     */
    public function thisIsPdfFile()
    {
        $filename = rtrim($this->getMinkParameter('show_tmp_dir'), \DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR.uniqid().'.html';
        file_put_contents($filename, $this->getSession()->getPage()->getContent());

        $pdfFileConstraint = new File();
        $pdfFileConstraint->mimeTypes = ['application/pdf', 'application/x-pdf'];

        /** @var \Symfony\Component\Validator\Validator $validator */
        $validator = $this->kernel->getContainer()->get('validator');
        $errorList = $validator->validateValue(
            $filename,
            $pdfFileConstraint
        );

        Assert::assertCount(0, $errorList, 'Это не PDF-файл');
    }

    /**
     * @param string $user E-mail Ticket owner
     *
     * @Given /^я оплатил билет для "([^"]*)"$/
     */
    public function iPayTicket($user)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('ApplicationDefaultBundle:User')->findOneBy(['username' => $user]);
        $ticket = $em->getRepository('ApplicationDefaultBundle:Ticket')->findOneBy(['user' => $user->getId()]);
        $payment = $em->getRepository('ApplicationDefaultBundle:Payment')->findOneBy(['user' => $user->getId()]);
        $payment->setStatus('paid');
        $ticket->setPayment($payment);

        $em->persist($ticket);
        $em->flush();
    }

    /**
     * @param string $user E-mail Ticket owner
     *
     * @Given /^я не оплатил билет для "([^"]*)"$/
     */
    public function iDontPayTicket($user)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('ApplicationDefaultBundle:User')->findOneBy(['username' => $user]);
        $ticket = $em->getRepository('ApplicationDefaultBundle:Ticket')->findOneBy(['user' => $user->getId()]);
        $payment = $em->getRepository('ApplicationDefaultBundle:Payment')->findOneBy(['user' => $user->getId()]);
        $payment->setStatus('pending');
        $ticket->setPayment($payment);

        $em->persist($ticket);
        $em->flush();
    }

    /**
     * @param string $mail E-mail Ticket owner
     *
     * @Given /^я должен видеть полное имя для "([^"]*)"$/
     */
    public function iMustSeeFullname($mail)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('ApplicationDefaultBundle:User')->findOneBy(['username' => $mail]);
        $this->assertPageContainsText($user->getFullname());
    }

    /**
     * @param string $mail      E-mail Ticket owner
     * @param string $eventSlug Event slug
     *
     * @Given /^я перехожу на страницу регистрации билета для пользователя "([^"]*)" для события "([^"]*)"$/
     */
    public function goToTicketRegistrationPage($mail, $eventSlug)
    {
        $this->visit($this->getTicketRegistrationUrl($mail, $eventSlug));
    }

    /**
     * @param string $mail      E-mail Ticket owner
     * @param string $eventSlug Event slug
     *
     * @Given /^я перехожу на страницу регистрации билета с битым хешем для пользователя "([^"]*)" для события "([^"]*)"$/
     */
    public function goToTicketRegistrationPageWithWrongHash($mail, $eventSlug)
    {
        $this->visit($this->getTicketRegistrationUrl($mail, $eventSlug).'fffuu');
    }

    /**
     * Generate URL for check ticket.
     *
     * @param string $mail      E-mail Ticket owner
     * @param string $eventSlug Event slug
     *
     * @return string
     */
    public function getTicketRegistrationUrl($mail, $eventSlug)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();

        $user = $em->getRepository('ApplicationDefaultBundle:User')->findOneBy(['username' => $mail]);
        $event = $em->getRepository('ApplicationDefaultBundle:Event')->findOneBy(['slug' => $eventSlug]);

        $ticket = $em->getRepository('ApplicationDefaultBundle:Ticket')->findOneByUserAndEventWithPendingPayment($user, $event);

        return $this->kernel->getContainer()->get('router')->generate('event_ticket_registration',
            [
                'ticket' => $ticket->getId(),
                'hash' => $ticket->getHash(),
            ],
            true
        );
    }

    /**
     * Проверка что имейл не был отправлен тем, кому не положено (т.е. не админам).
     *
     * @param string $subject Subject
     * @param string $to      Receiver
     *
     * @Then /^email with subject "([^"]*)" should have not been sent(?: to "([^"]+)")?$/
     *
     * @throws \RuntimeException
     */
    public function emailWithSubjectShouldHaveBeenSent($subject, $to)
    {
        /** @var \Swift_Mailer $mailer */
        $mailer = $this->loadProfile()->getCollector('swiftmailer');
        if ($mailer->getMessageCount() > 0) {
            foreach ($mailer->getMessages() as $message) {
                if (trim($subject) === trim($message->getSubject())) {
                    if (\array_key_exists($to, $message->getTo())) {
                        throw new \RuntimeException(sprintf('Message with subject "%s" and receiver "%s" was sent, but should not', $subject, $to));
                    }
                }
            }
        }
    }

    /**
     * Loads the profiler's profile.
     *
     * If no token has been given, the debug token of the last request will be used
     *
     * @param string $token
     *
     * @return \Symfony\Component\HttpKernel\Profiler\Profile
     *
     * @throws \RuntimeException
     */
    public function loadProfile($token = null)
    {
        if (null === $token) {
            $headers = $this->getSession()->getResponseHeaders();

            if (!isset($headers['X-Debug-Token']) && !isset($headers['x-debug-token'])) {
                throw new \RuntimeException('Debug-Token not found in response headers. Have you turned on the debug flag?');
            }
            $token = isset($headers['X-Debug-Token']) ? $headers['X-Debug-Token'] : $headers['x-debug-token'];
            if (\is_array($token)) {
                $token = end($token);
            }
        }

        return $this->kernel->getContainer()->get('profiler')->loadProfile($token);
    }

    /**
     * @param string $userName
     *
     * @Given /^пользователь "([^"]*)" должен быть в списке только один раз$/
     */
    public function singleUser($userName)
    {
        $result = $this->getSession()->getPage()->find('css', '.table.table-bordered.table-striped');
        Assert::assertEquals(1, mb_substr_count($result->getHtml(), $userName));
    }

    /**
     * @Given /^я перехожу на страницу со списком рассылок$/
     */
    public function iGoToTheMailListPage()
    {
        $this->visit('/admin/stfalcon/event/mail/list');
    }

    /**
     * @Then /^я на странице создания рассылки$/
     */
    public function iAmOnTheMailCreatePage()
    {
        $this->visit('/admin/stfalcon/event/mail/create');
    }

    /**
     * @Given /^я перехожу на страницу со списком промо кодов$/
     */
    public function iGoToThePromoCodesListPage()
    {
        $this->visit('/admin/stfalcon/event/promocode/list');
    }

    /**
     * @Then /^я на странице создания промо кодов$/
     */
    public function iAmOnThePromoCodeCreatePage()
    {
        $this->visit('/admin/stfalcon/event/promocode/create');
    }

    /**
     * Проверяем дату по формату.
     *
     * @Then /^я должен видеть в елементе "([^"]*)" дату "([^"]*)" в формате "([^"]*)"$/
     */
    public function iShouldSeeDateInFormat($elem, $date, $format)
    {
        $date = new \DateTime($date);

        $this->assertFieldContains($elem, $date->format($format));
    }

    /**
     * @Given /^Interkassa API is available$/
     */
    public function interkassaApiIsAvailable()
    {
        $this->mocker->mockService('application.interkassa.service', 'Application\Bundle\DefaultBundle\Service\InterkassaService')
            ->shouldReceive('checkPayment')
            ->andReturn(true);
    }

    /**
     * @param int $paymentId
     *
     * @Given /^я перехожу на страницу обработки платежа "([^"]*)"$/
     */
    public function goToInterkassaInteraction($paymentId)
    {
        $params = [
            'ik_pm_no' => $paymentId,
        ];

        $this->visit('/payment/interaction?'.http_build_query($params));
    }

    /**
     * @param int $paymentId
     *
     * @Given /^платеж "([^"]*)" должен быть помечен как оплачен/
     */
    public function paymentEqualTo($paymentId)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();

        /** @var Payment $payment */
        $payment = $em->getRepository('ApplicationDefaultBundle:Payment')->find($paymentId);

        Assert::assertTrue($payment->isPaid());
    }
}
