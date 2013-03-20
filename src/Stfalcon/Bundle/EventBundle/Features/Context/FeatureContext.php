<?php

namespace Stfalcon\Bundle\EventBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;

use Behat\Symfony2Extension\Context\KernelAwareInterface,
    Behat\MinkExtension\Context\MinkContext,
    Behat\CommonContexts\DoctrineFixturesContext;

use Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;

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
            ));

        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures(), true);
    }

    /**
     * @param string $mail E-mail Ticket owner
     *
     * @Given /^я оплатил билет для "([^"]*)"$/
     */
    public function iPayTicket($mail)
    {
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $user    = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('username' => $mail));
        $ticket  = $em->getRepository('StfalconEventBundle:Ticket')->findOneBy(array('user' => $user->getId()));
        $payment = $em->getRepository('StfalconPaymentBundle:Payment')->findOneBy(array('user' => $user->getId()));
        $payment->setStatus('paid');
        $ticket->setPayment($payment);

        $em->persist($ticket);
        $em->flush();
    }

    /**
     * @param string $mail E-mail Ticket owner
     *
     * @Given /^я не оплатил билет для "([^"]*)"$/
     */
    public function iDontPayTicket($mail)
    {
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $user    = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('username' => $mail));
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
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('username' => $mail));
        $this->assertPageContainsText($user->getFullname());
    }

    /**
     * @param string $mail E-mail Ticket owner
     *
     * @Given /^я перехожу на страницу регистрации для "([^"]*)"$/
     */
    public function goToTicketRegistrationPage($mail)
    {
        $this->visit($this->getTicketUrl($mail));
    }

    /**
     * @param string $mail E-mail Ticket owner
     *
     * @Given /^я перехожу на страницу регистрации для "([^"]*)" с битым хешем$/
     */
    public function goToTicketRegistrationPageWithWrongHash($mail)
    {
        $this->visit($this->getTicketUrl($mail) . 'fffuu');
    }

    /**
     * Generate URL for register ticket
     *
     * @param string $mail E-mail Ticket owner
     *
     * @return string
     */
    public function getTicketUrl($mail)
    {
        $em = $this->kernel->getContainer()->get('doctrine')->getManager();
        $user   = $em->getRepository('ApplicationUserBundle:User')->findOneBy(array('username' => $mail));
        $ticket = $em->getRepository('StfalconEventBundle:Ticket')->findOneBy(array('user' => $user->getId()));

        return $this->kernel->getContainer()->get('router')->generate('event_ticket_check',
            array(
                'ticket' => $ticket->getId(),
                'hash'   => $ticket->getHash()
            ),
            true
        );
    }
}
