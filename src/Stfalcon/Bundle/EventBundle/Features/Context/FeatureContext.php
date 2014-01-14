<?php

namespace Stfalcon\Bundle\EventBundle\Features\Context;

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

/**
 * Feature context for StfalconEventBundle
 */
class FeatureContext extends MinkContext implements KernelAwareInterface
{
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
        $payment = $em->getRepository('StfalconPaymentBundle:Payment')->findOneBy(array('user' => $user->getId()));
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
        $payment = $em->getRepository('StfalconPaymentBundle:Payment')->findOneBy(array('user' => $user->getId()));
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
        $this->visit($this->getTicketUrl($mail, $eventSlug));
    }

    /**
     * @param string $mail      E-mail Ticket owner
     * @param string $eventSlug Event slug
     *
     * @Given /^я перехожу на страницу регистрации билета с битым хешем для пользователя "([^"]*)" для события "([^"]*)"$/
     */
    public function goToTicketRegistrationPageWithWrongHash($mail, $eventSlug)
    {
        $this->visit($this->getTicketUrl($mail, $eventSlug) . 'fffuu');
    }

    /**
     * Generate URL for register ticket
     *
     * @param string $mail      E-mail Ticket owner
     * @param string $eventSlug Event slug
     *
     * @return string
     */
    public function getTicketUrl($mail, $eventSlug)
    {
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();

        $user  = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('username' => $mail));
        $event = $em->getRepository('StfalconEventBundle:Event')->findOneBy(array('slug' => $eventSlug));

        $ticket = $em->getRepository('StfalconEventBundle:Ticket')->findOneByUserAndEvent($user, $event);

        return $this->kernel->getContainer()->get('router')->generate('event_ticket_check',
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
}
