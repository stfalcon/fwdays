<?php

namespace Application\Bundle\DefaultBundle\Tests;

use Application\Bundle\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Client;
use Doctrine\ORM\EntityManager;

/**
 * Class TicketControllerTest.
 */
class TicketControllerTest extends WebTestCase
{
    const EN_FILE_HASH = 'e41fa4f8f91eff3bf42ed94689524f21';
    const UK_FILE_HASH = '2317035330ba4a26d7220f9901b11a26';
    /** @var Client */
    protected $client;
    /** @var EntityManager */
    protected $em;

    protected $translator;

    /** set up fixtures */
    public function setUp()
    {
        $connection = $this->getContainer()->get('doctrine')->getConnection();

        $connection->exec('SET FOREIGN_KEY_CHECKS=0;');
        $connection->exec('DELETE FROM users;');
        $connection->exec('DELETE FROM event__tickets;');
        $connection->exec('ALTER TABLE event__tickets AUTO_INCREMENT = 1;');
        $connection->exec('SET FOREIGN_KEY_CHECKS=1;');
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

//    /**
//     * test en html ticket hash.
//     */
//    public function testEnTicketHash()
//    {
//        $this->assertEquals($this->getFileHash('en'), self::EN_FILE_HASH);
//    }
//
//    /**
//     * test uk html ticket hash.
//     */
//    public function testUkTicketHash()
//    {
//        $this->assertEquals($this->getFileHash('uk'), self::UK_FILE_HASH);
//    }

    /**
     * Test uk local in cookie.
     */
    public function testUkCookieLocale()
    {
        $this->assertEquals($this->getLangCookie('uk'), 'uk');
    }

    /**
     * Test en local in cookie.
     */
    public function testEnCookieLocale()
    {
        $this->assertEquals($this->getLangCookie('en'), 'en');
    }

    /**
     * get current local from cookie.
     *
     * @param string $lang
     *
     * @return string
     */
    private function getLangCookie($lang)
    {
        if (!empty($lang)) {
            $this->client->followRedirects();
            $this->client->request('GET', sprintf('/%s', $lang));

            return $this->client->getRequest()->cookies->get('hl');
        }

        return '';
    }

    /**
     * get file hash by lang.
     *
     * @param string $lang
     *
     * @return string
     */
    private function getFileHash($lang)
    {
        if (!empty($lang)) {
            $this->loginUser('user@fwdays.com', 'qwerty', $lang);
            $this->client->request('GET', sprintf('/%s/event/javaScript-framework-day-2018/ticket/html', $lang));
            $content = $this->client->getResponse()->getContent();

            return md5($content);
        }

        return '';
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
        $crawler = $this->client->request('GET', '/');
        $this->assertGreaterThan(0, $crawler->filter('a:contains("'.$accountLinkCaption.'")')->count());

        return $user;
    }
}
