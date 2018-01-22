<?php

namespace Application\Bundle\DefaultBundle\Tests\Listener;

use Application\Bundle\UserBundle\Entity\User;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Stfalcon\Bundle\EventBundle\Entity\Payment;
use Stfalcon\Bundle\EventBundle\EventListener\PaymentListener;
use Symfony\Component\BrowserKit\Client;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class PaymentListenerTest extends WebTestCase
{
    const INTERKASSA_MAIL_MSG_HELLO_UK = 'Вітаємо, <br/>%s.';
    const INTERKASSA_MAIL_MSG_THANKS_UK = 'Дякуємо Вам за оплату участі у конференції %s.';
    const INTERKASSA_MAIL_MSG_REMEMBER_UK = 'Нагадуємо, що конференція відбудеться';
    const INTERKASSA_MAIL_MSG_REMEMBER1_UK = 'року,';
    const INTERKASSA_MAIL_MSG_TICKET_UK = 'Ваш квиток знаходиться у вкладенні.';

    const INTERKASSA_MAIL_MSG_HELLO_EN = 'Hello, <br/>%s';
    const INTERKASSA_MAIL_MSG_THANKS_EN = 'Thank you for paying for the %s conference.';
    const INTERKASSA_MAIL_MSG_REMEMBER_EN = 'We remind that the conference will be held';
    const INTERKASSA_MAIL_MSG_REMEMBER1_EN = 'year,';
    const INTERKASSA_MAIL_MSG_TICKET_EN = 'Find your ticket attached.';

    /** @var Client */
    protected $client;
    /** @var EntityManager */
    protected $em;

    /** set up fixtures */
    public function setUp()
    {
        $connection = $this->getContainer()->get('doctrine')->getConnection();

        $connection->exec('DELETE FROM event__tickets;');
        $connection->exec('ALTER TABLE event__tickets AUTO_INCREMENT = 1;');

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
        $this->clearSpoolFolder();
    }

    /**
     * Test payment listener with send email.
     */
    public function testPostUpdate()
    {
        $this->getEmailWithLocal('uk');
        /** check email with ticket pdf file */
        $this->findEmailWithText('ticket-php-day-2017.pdf');
        /** check email with string */
        $this->findEmailWithText('Вітаємо, <br/>Michael Jordan');
    }

    /**
     * Test uk translate in email.
     */
    public function testEmailUkTranslate()
    {
        $this->getEmailWithLocal('uk');
        $this->findEmailWithText(sprintf(self::INTERKASSA_MAIL_MSG_HELLO_UK, 'Michael Jordan'));
        $this->findEmailWithText(self::INTERKASSA_MAIL_MSG_TICKET_UK);
        $this->findEmailWithText(sprintf(self::INTERKASSA_MAIL_MSG_THANKS_UK, 'PHP Day'));
        $this->findEmailWithText(self::INTERKASSA_MAIL_MSG_REMEMBER_UK);
        $this->findEmailWithText(self::INTERKASSA_MAIL_MSG_REMEMBER1_UK);
    }

    /**
     * Get email from listener.
     *
     * @param string $lang
     */
    private function getEmailWithLocal($lang)
    {
        $this->client->followRedirects();
        $user = $this->loginUser('user@fwdays.com', 'qwerty', $lang);
        $this->client->request('GET', '/'.$lang, ['_locale' => $lang]);

        $eventPHPDay = $this->em->getRepository('StfalconEventBundle:Event')->findOneBy(['slug' => 'php-day-2017']);
        $ticket = $this->em->getRepository('StfalconEventBundle:Ticket')
            ->findOneBy(['user' => $user->getId(), 'event' => $eventPHPDay->getId()]);
        /**
         * @var Payment
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
     * @param string $lang
     *
     * @return User $user
     */
    private function loginUser($userName, $userPass, $lang)
    {
        $user = $this->em->getRepository('ApplicationUserBundle:User')->findOneBy(['email' => $userName]);
        $this->assertNotNull($user, sprintf('User %s not founded!', $userName));

        $loginBtnCaption = 'Sign in';
        $accountLinkCaption = ' Account';

        if ('uk' === $lang) {
            $loginBtnCaption = 'Увійти';
            $accountLinkCaption = ' Кабінет';
        }
        /** start Login */
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $lang.'/login');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('<button class="btn btn--primary btn--lg form-col__btn" onclick="ga(\'send\', \'button\', \'enter\', \'event\');" type="submit">'.$loginBtnCaption.'
            </button>', $crawler->html());
        $form = $crawler->selectButton($loginBtnCaption)->form();
        $form['_username'] = $user->getEmail();
        $form['_password'] = $userPass;

        $this->client->submit($form);
        /** end Login */
        $crawler = $this->client->request('GET', $lang.'/');
        $this->assertGreaterThan(0, $crawler->filter('a:contains("'.$accountLinkCaption.'")')->count());

        return $user;
    }

    /**
     * Finc file in spool folder
     *
     * @param string $text
     *
     * @return string
     */
    private function findEmailWithText($text)
    {
        $finder = $this->getFilesFromSpoolFolder();

        $this->assertGreaterThan(0, count($finder), 'no emails in spool folder');
        $found = false;
        $hashFile = '';
        foreach ($finder as $file) {
            $message = quoted_printable_decode(unserialize(file_get_contents($file)));
            if (strpos($message, $text)) {
                $found = true;
                $hashFile = md5($file);
                break;
            }
        }

        $this->assertTrue($found, sprintf('In spool folder not found email with text "%s"', $text));

        return $hashFile;
    }

    /**
     * clear spool folder
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
