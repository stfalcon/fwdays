<?php

namespace App\Tests\Listener;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Ticket;
use App\Entity\User;
use App\EventListener\ORM\Payment\PaymentListener;
use App\Tests\BaseFunctionalTest\AbstractBaseFunctionalTest;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class PaymentListenerTest extends AbstractBaseFunctionalTest
{
    /** set up fixtures */
    protected function setUp(): void
    {
        parent::setUp();
        $this->clearSpoolFolder();
    }

    /** destroy */
    protected function tearDown(): void
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
        /* check email with ticket pdf file */
        $this->findEmailWithText('ticket-php-frameworks-day-2018.pdf');
        /* check email with string */
        $this->findEmailWithText('У вкладенні знаходиться ваш вхідний квиток. Покажіть його з екрана телефона, будь ласка, або роздрукуйте на папері.');
    }

    /**
     * Get email from listener.
     *
     * @param string $lang
     */
    private function getEmailWithLocal($lang)
    {
        $this->client->followRedirects();
        $user = $this->loginUser('jack.sparrow@fwdays.com', 'qwerty', $lang);
        $this->requestGet('/'.$lang, ['_locale' => $lang]);

        $eventPHPDay = $this->em->getRepository(Event::class)->findOneBy(['slug' => 'php-frameworks-day-2018']);
        $ticket = $this->em->getRepository(Ticket::class)
            ->findOneBy(['user' => $user->getId(), 'event' => $eventPHPDay->getId()]);
        /** @var Payment $payment */
        $payment = $ticket->getPayment();
        $payment->markedAsPaid();

        $event = new LifecycleEventArgs($payment, $this->em);

        $listener = new PaymentListener($this->container);
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
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $userName]);
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
        $crawler = $this->requestGet($lang.'/');
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

        $this->assertGreaterThan(0, \count($finder), 'no emails in spool folder');
        $found = false;
        $hashFile = '';
        foreach ($finder as $file) {
            $message = quoted_printable_decode(unserialize(file_get_contents($file)));
            if (\strpos($message, $text)) {
                $found = true;
                $hashFile = \md5($file);
                break;
            }
        }

        $this->assertTrue($found, \sprintf('In spool folder not found email with text "%s"', $text));

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
