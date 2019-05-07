<?php

namespace Application\Bundle\DefaultBundle\Tests\Listener;

use Application\Bundle\DefaultBundle\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Application\Bundle\DefaultBundle\Entity\Payment;
use Application\Bundle\DefaultBundle\EventListener\PaymentListener;
use Symfony\Component\BrowserKit\Client;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Translator;

class PaymentListenerTest extends WebTestCase
{
    const INTERKASSA_MAIL_MSG_HELLO_UK = 'У вкладенні знаходиться ваш вхідний квиток. Покажіть його з екрана телефона, будь ласка, або роздрукуйте на папері.';
    const INTERKASSA_MAIL_MSG_THANKS_UK = 'З нетерпінням чекаємо вас на конференції у Києві.';

    const INTERKASSA_MAIL_MSG_HELLO_EN = 'Please, find your ticket to the conference attached. You do not need to print the ticket, show it on your screen at the registration desk.';
    const INTERKASSA_MAIL_MSG_THANKS_EN = 'We are looking forward to seeing you in Kyiv at the conference.';

    /** @var Client */
    protected $client;
    /** @var EntityManager */
    protected $em;
    /** @var Translator */
    protected $translator;

    /** set up fixtures */
    public function setUp()
    {
        $connection = $this->getContainer()->get('doctrine')->getConnection();

        $connection->exec('SET FOREIGN_KEY_CHECKS=0;');
        $connection->exec('DELETE FROM users;');
        $connection->exec('SET FOREIGN_KEY_CHECKS=1;');
        $connection->exec('DELETE FROM event__tickets;');
        $connection->exec('ALTER TABLE event__tickets AUTO_INCREMENT = 1;');

        $this->loadFixtures(
            [
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadEventData',
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadUserData',
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadPaymentData',
                'Application\Bundle\DefaultBundle\DataFixtures\ORM\LoadTicketData',
            ],
            null,
            'doctrine',
            ORMPurger::PURGE_MODE_DELETE
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
        $this->markTestSkipped();
        $this->getEmailWithLocal('uk');
        /* check email with ticket pdf file */
        $this->findEmailWithText('ticket-php-day-2017.pdf');
        /* check email with string */
        $this->findEmailWithText('У вкладенні знаходиться ваш вхідний квиток. Покажіть його з екрана телефона, будь ласка, або роздрукуйте на папері.');
    }

    /**
     * Test uk translate in email.
     */
    public function testEmailUkTranslate()
    {
        $this->markTestSkipped();
        $this->getEmailWithLocal('uk');
        $this->findEmailWithText(self::INTERKASSA_MAIL_MSG_HELLO_UK);
        $this->findEmailWithText(self::INTERKASSA_MAIL_MSG_THANKS_UK);
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

        $eventPHPDay = $this->em->getRepository('ApplicationDefaultBundle:Event')->findOneBy(['slug' => 'php-day-2017']);
        $ticket = $this->em->getRepository('ApplicationDefaultBundle:Ticket')
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
        $user = $this->em->getRepository('ApplicationDefaultBundle:User')->findOneBy(['email' => $userName]);
        $this->assertNotNull($user, sprintf('User %s not founded!', $userName));

        $loginBtnCaption = 'Sign in';
        $accountLinkCaption = ' Account';

        if ('uk' === $lang) {
            $loginBtnCaption = 'Увійти';
            $accountLinkCaption = ' Кабінет';
        }
        /* start Login */
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $lang.'/login');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains('<button class="btn btn--primary btn--lg form-col__btn" type="submit">'.$loginBtnCaption.'
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
     * Finc file in spool folder.
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
     * clear spool folder.
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
