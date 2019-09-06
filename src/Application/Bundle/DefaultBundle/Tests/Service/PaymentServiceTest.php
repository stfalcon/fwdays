<?php

namespace Application\Bundle\DefaultBundle\Tests\Listener;

use Application\Bundle\DefaultBundle\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Prophecy\Prophet;
use Application\Bundle\DefaultBundle\Entity\Event;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\Entity\Ticket;
use Application\Bundle\DefaultBundle\Repository\TicketRepository;
use Symfony\Component\BrowserKit\Client;
use Doctrine\ORM\EntityManager;

/**
 * Class ReferralServiceTest.
 */
class PaymentServiceTest extends WebTestCase
{
    /** @var Client */
    protected $client;

    /** @var EntityManager */
    protected $em;

    /** @var Prophet */
    protected $prophet;

    /** set up fixtures */
    public function setUp()
    {
        $this->prophet = new Prophet();
        $connection = $this->getContainer()->get('doctrine')->getConnection();

        $connection->exec('SET FOREIGN_KEY_CHECKS=0;');
        $connection->exec('DELETE FROM users;');
        $connection->exec('SET FOREIGN_KEY_CHECKS=1;');

        $this->loadFixtures(
            [
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadEventData',
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadUserData',
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadPaymentData',
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadTicketData',
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadTicketCostData',
            ],
            null,
            'doctrine',
            ORMPurger::PURGE_MODE_DELETE
        );
        $this->client = self::createClient();
        $this->em = $this->getContainer()->get('doctrine')->getManager();
    }

    /** destroy */
    public function tearDown()
    {
        $this->prophet->checkPredictions();
        parent::tearDown();
    }

    /**
     * Check Payment pay fwdays money.
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function testPayByFwdaysAmount()
    {
        $fwdaysAmount = 3000;

        /** @var User $user */
        $user = $this->em->getRepository('ApplicationDefaultBundle:User')->findOneBy(['email' => 'jack.sparrow@fwdays.com']);
        $user->setBalance($fwdaysAmount);

        /** @var Event $event */
        $event = $this->em->getRepository('ApplicationDefaultBundle:Event')->findOneBy(['slug' => 'zend-day-2017']);
        /** @var TicketRepository $ticketRepository */
        $ticketRepository = $this->em->getRepository('ApplicationDefaultBundle:Ticket');
        /** @var Ticket $ticket */
        $ticket = $ticketRepository->findOneByUserAndEventWithPayment($user, $event);
        /** @var Payment $payment */
        $payment = $ticket->getPayment();

        $referralBalance = $user->getBalance();
        $paymentService = $this->getContainer()->get('app.payment.service');

        $paymentService->setPaidByBonusMoney($payment, $event);

        $this->assertEquals($payment->getAmount(), 0);
        $this->assertEquals($payment->getBaseAmount(), $ticket->getAmountWithoutDiscount());
        $this->assertEquals($payment->getFwdaysAmount(), $ticket->getAmount());
        $this->assertTrue($payment->isPaid());

        $this->assertEquals($referralBalance - $ticket->getAmount(), $user->getBalance());
    }
}
