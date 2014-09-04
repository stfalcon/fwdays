<?php

namespace Stfalcon\Bundle\EventBundle\Features\Context;

use Behat\Behat\Event\StepEvent;
use Behat\Mink\Driver\Selenium2Driver;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Constraints\File;

use Behat\Symfony2Extension\Context\KernelAwareInterface,
    Behat\MinkExtension\Context\MinkContext,
    Behat\CommonContexts\DoctrineFixturesContext,
    Behat\CommonContexts\MinkRedirectContext,
    Behat\CommonContexts\SymfonyMailerContext;

use Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;

use Application\Bundle\UserBundle\Features\Context\UserContext as ApplicationUserBundleUserContext;

use PSS\Behat\Symfony2MockerExtension\Context\ServiceMockerAwareInterface;
use PSS\Behat\Symfony2MockerExtension\ServiceMocker;
/**
 * Feature context for StfalconEventBundle
 */
class FeatureContext extends MinkContext implements KernelAwareInterface, ServiceMockerAwareInterface
{
    /**
     * @var ServiceMocker $mocker
     */
    private $mocker = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->useContext('DoctrineFixturesContext', new DoctrineFixturesContext());
        $this->useContext('MinkRedirectContext', new MinkRedirectContext());
        $this->useContext('SymfonyMailerContext', new SymfonyMailerContext());
        $this->useContext('ApplicationUserBundleUserContext', new ApplicationUserBundleUserContext($this));
    }

    /**
     * @param ServiceMocker $mocker
     * @return null|void
     */
    public function setServiceMocker(ServiceMocker $mocker)
    {
        $this->mocker = $mocker;
    }

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
        $this->getMainContext()
            ->getSubcontext('DoctrineFixturesContext')
            ->loadFixtureClasses($loader, array(
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadNewsData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadPagesData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadReviewData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadMailQueueData',
                'Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadPromoCodeData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadPaymentData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadMailQueueData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadTicketData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadMailQueueData'
            ));

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $em->getConnection()->executeUpdate("SET foreign_key_checks = 0;");
        $purger = new ORMPurger();
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures(), true);
        $em->getConnection()->executeUpdate("SET foreign_key_checks = 1;");
        /** Maximize browser window */
        $driver = $this->getSession()->getDriver();
        if ($driver instanceof Selenium2Driver) {
            $driver->maximizeWindow();
        }
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
            if (strpos($currentUrl, $this->getMinkParameter('base_url')) !== false) {
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
        $filename = rtrim($this->getMinkParameter('show_tmp_dir'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . uniqid() . '.html';
        file_put_contents($filename, $this->getSession()->getPage()->getContent());

        $pdfFileConstraint = new File();
        $pdfFileConstraint->mimeTypes = array("application/pdf", "application/x-pdf");

        /** @var \Symfony\Component\Validator\Validator $validator */
        $validator = $this->kernel->getContainer()->get('validator');
        $errorList = $validator->validateValue(
            $filename,
            $pdfFileConstraint
        );

        assertCount(0, $errorList, "Это не PDF-файл");
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
        $user    = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('username' => $user));
        $ticket  = $em->getRepository('StfalconEventBundle:Ticket')->findOneBy(array('user' => $user->getId()));
        $payment = $em->getRepository('StfalconEventBundle:Payment')->findOneBy(array('user' => $user->getId()));
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
        $user    = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('username' => $user));
        $ticket  = $em->getRepository('StfalconEventBundle:Ticket')->findOneBy(array('user' => $user->getId()));
        $payment = $em->getRepository('StfalconEventBundle:Payment')->findOneBy(array('user' => $user->getId()));
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
        $user = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('username' => $mail));
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
        $this->visit($this->getTicketRegistrationUrl($mail, $eventSlug) . 'fffuu');
    }

    /**
     * Generate URL for check ticket
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

        $user  = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('username' => $mail));
        $event = $em->getRepository('StfalconEventBundle:Event')->findOneBy(array('slug' => $eventSlug));

        $ticket = $em->getRepository('StfalconEventBundle:Ticket')->findOneByUserAndEvent($user, $event);

        return $this->kernel->getContainer()->get('router')->generate('event_ticket_registration',
            array(
                'ticket' => $ticket->getId(),
                'hash'   => $ticket->getHash()
            ),
            true
        );
    }

    /**
     * Проверка что имейл не был отправлен тем, кому не положено (т.е. не админам)
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
                    if (array_key_exists($to, $message->getTo())) {
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
            if (is_array($token)) {
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
        assertEquals(1, mb_substr_count($result->getHtml(), $userName));
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
     * Проверяем дату по формату
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
     *
     * @return null
     */
    public function interkassaApiIsAvailable()
    {
        $this->mocker->mockService('stfalcon_event.interkassa.service', 'Stfalcon\Bundle\EventBundle\Service\InterkassaService')
            ->shouldReceive('checkPayment')
            ->andReturn(true);
    }


    /**
     * @param integer $paymentId
     *
     * @Given /^я перехожу на страницу обработки платежа "([^"]*)"$/
     */
    public function goToInterkassaInteraction($paymentId)
    {
        $params = [
            'ik_pm_no' => $paymentId
        ];

        $this->visit('/payment/interaction?' . http_build_query($params));
    }

    /**
     * @param integer $paymentId
     *
     * @Given /^платеж "([^"]*)" должен быть помечен как оплачен/
     */
    public function paymentEqualTo($paymentId)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();

        /** @var Payment $payment */
        $payment = $em->getRepository('StfalconEventBundle:Payment')->find($paymentId);

        assertTrue($payment->isPaid());
    }


}
