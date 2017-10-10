<?php

namespace Application\Bundle\DefaultBundle\Tests;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class TicketControllerTest.
 */
class TicketControllerTest extends WebTestCase
{
    const FILE_HASH = '24e14bc5ead54a1438e370e2bdca6c68';
    /** @var Client */
    protected $client;
    /** @var EntityManager */
    protected $em;

    protected $translator;

    /** set up fixtures */
    public function setUp()
    {
        $this->loadFixtures(
            [
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData',
                'Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadPaymentData',
                'Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadTicketData',
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
    }

    public function testGetMd5()
    {
        $filePath = $this->getContainer()->getParameter('kernel.cache_dir').'/spool/ticket-javaScript-framework-day-2018_1.pdf';
        $file = new UploadedFile($filePath, 'ticket1.pdf');
        $hash1 = md5_file($file);

        $filePath = $this->getContainer()->getParameter('kernel.cache_dir').'/spool/ticket-javaScript-framework-day-2018_2.pdf';
        $file = new UploadedFile($filePath, 'ticket2.pdf');
        $hash2 = md5_file($file);

    }
    /**
     * test login user
     */
    public function testEnTicketHash()
    {
        $this->loginUser('user@fwdays.com', 'qwerty');
        $this->client->request('GET', '/en/event/javaScript-framework-day-2018/ticket');

        $filePath = $this->getContainer()->getParameter('kernel.cache_dir').'/spool/ticket-javaScript-framework-day-2018.pdf';
        file_put_contents($filePath, $this->client->getResponse()->getContent());
        $file = new UploadedFile($filePath, 'ticket.pdf');
        $hash = md5_file($file);
        $hashContent = md5($this->client->getResponse()->getContent());

        $this->assertEquals($hashContent, self::FILE_HASH);
    }
//db6ab3496eacacc113bbe0b1319156b2
    /**
     * @param string $userName
     * @param string $userPass
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
        $crawler = $this->client->submit($form);
        /** end Login */
        $crawler = $this->client->request('GET', '/');
        $this->assertGreaterThan(0, $crawler->filter('a:contains(" Сabinet")')->count());
    }
}
