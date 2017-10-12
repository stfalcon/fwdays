<?php

namespace Application\Bundle\DefaultBundle\Tests\Listener;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\EventListener\PaymentListener;
use Symfony\Component\BrowserKit\Client;
use Doctrine\ORM\EntityManager;

class PaymentListenerTest extends WebTestCase
{
    /** @var Client */
    protected $client;
    /** @var EntityManager */
    protected $em;
    /** set up fixtures */
    public function setUp()
    {
        $connection = $this->getContainer()->get('doctrine')->getConnection();

        $connection->exec("DELETE FROM event__tickets;");
        $connection->exec("ALTER TABLE event__tickets AUTO_INCREMENT = 1;");

        $this->loadFixtures(
            [
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
                'Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadPaymentData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadTicketData',
            ]
        );
        $this->client = $this->createClient();
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->translator = $this->getContainer()->get('translator');
    }

    /** destroy */
    public function tearDown()
    {
        parent::tearDown();
    }

    public function testPostUpdate()
    {
        $user = $this->loginUser('user@fwdays.com', 'qwerty');
        $this->client->request('GET', '/uk', ['_locale' => 'uk']);

        $eventPHPDay = $this->em->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => 'php-day-2017']);
        $ticket = $this->em->getRepository('StfalconEventBundle:Ticket')
            ->findOneBy(['user' => $user->getId(), 'event' => $eventPHPDay->getId()]);
        /**
         * @var Payment $payment
         */
        $payment = $ticket->getPayment();
        $payment->markedAsPaid();

        $event = new LifecycleEventArgs($payment, $this->em);

        $listener = new PaymentListener($this->getContainer());
        $listener->postUpdate($event);
    }

    /**
     * @param string $userName
     * @param string $userPass
     *
     * @return User $user
     */
    private function loginUser($userName, $userPass)
    {
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['email' => $userName]);

        /** start Login */
        $crawler = $this->client->request('GET', '/login');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('<button class="btn btn--primary btn--lg form-col__btn" type="submit">Login</button>', $crawler->html());
        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = $user->getEmail();
        $form['_password'] = $userPass;
        $this->client->followRedirects();
        $this->client->submit($form);
        /** end Login */
        $crawler = $this->client->request('GET', '/');
        $this->assertGreaterThan(0, $crawler->filter('a:contains(" Сabinet")')->count());

        return $user;
    }
    /**
     * Ищем в спуле письмо с определенным получателем и текстом
     *
     * @param string $text
     * @param string $recipient
     */
    private function findEmailWithTextAndRecipientInSpoolFolder($text, $recipient)
    {
        $finder = $this->getFilesFromSpoolFolder();

        $this->assertGreaterThan(0, count($finder), "no emails in spool folder");
        $found = false;
        foreach ($finder as $file) {
            $message = quoted_printable_decode(unserialize(file_get_contents($file)));
            if (strpos($message, $text) && strpos($message, $recipient)) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, sprintf('In spool folder not found email with text "%s" for recipient "%s"', $text, $recipient));
    }
    /**
     * Очистка спула
     */
    private function clearSpoolFolder()
    {
        $fs = new Filesystem();
        $files = $this->getFilesFromSpoolFolder();

        $fs->remove($files);
    }
    /**
     * @return Finder
     */
    private function getFilesFromSpoolFolder()
    {
        $finder = new Finder();
        $finder->files()->in($this->getContainer()->getParameter('spool_path'));

        return $finder;
    }
}