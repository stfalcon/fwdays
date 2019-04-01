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
    const EN_FILE_HASH = 'bb93bfbc18dfa0a8956b6aee7a9466f6';
    const UK_FILE_HASH = '7a4916c2839dc31d6c2ad6ea3375a285';
    /** @var Client */
    protected $client;
    /** @var EntityManager */
    protected $em;

    protected $translator;

    /** set up fixtures */
    public function setUp()
    {
        $connection = $this->getContainer()->get('doctrine')->getConnection();
        $this->client = $this->createClient();

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
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->translator = $this->getContainer()->get('translator');
    }

    /** destroy */
    public function tearDown()
    {
        parent::tearDown();
    }

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
}
