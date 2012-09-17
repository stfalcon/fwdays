<?php

namespace Application\Bundle\DefaultBundle\Features\Context;

use Symfony\Component\HttpKernel\KernelInterface;

use Behat\Symfony2Extension\Context\KernelAwareInterface,
    Behat\MinkExtension\Context\MinkContext;

use Doctrine\Common\DataFixtures\Loader,
    Doctrine\Common\DataFixtures\Executor\ORMExecutor,
    Doctrine\Common\DataFixtures\Purger\ORMPurger;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Feature context.
 */
class BaseFeatureContext extends MinkContext implements KernelAwareInterface
{
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
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadEventData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadNewsData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadPagesData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadSpeakersData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadReviewData());
        $loader->addFixture(new \Stfalcon\Bundle\EventBundle\DataFixtures\ORM\LoadTicketData());
        $loader->addFixture(new \Stfalcon\Bundle\SponsorBundle\DataFixtures\ORM\LoadSponsorData());
        $loader->addFixture(new \Application\Bundle\UserBundle\DataFixtures\ORM\LoadUserData());
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->kernel->getContainer()->get('doctrine.orm.entity_manager');

        $purger = new ORMPurger();
        $executor = new ORMExecutor($em, $purger);
        $executor->purge();
        $executor->execute($loader->getFixtures(), true);
    }

    /**
     * @Given /^у меня должна быть подписка на все активные ивенты$/
     */
    public function iMustHaveTicketForAllEvents()
    {
        $activeEvents = $this->kernel->getContainer()->get('doctrine')->getEntityManager()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(array('active' => true ));

        $user = $this->kernel->getContainer()->get('fos_user.user_manager')->findUserByEmail('test@fwdays.com');
        $tickets = $this->kernel->getContainer()->get('doctrine')->getEntityManager()
            ->getRepository('StfalconEventBundle:Ticket')->findBy(array('user' => $user->getId()));

        assertEquals(count($tickets), count($activeEvents));
    }

    /**
     * Check that document contains image from some source
     *
     * @param string $src
     *
     * @Given /^я должен видеть картинку с исходником "([^"]*)"$/
     */
    public function documentContainsImageWithSrc($src)
    {
        $rawImages = $this->getSession()->getPage()->findAll('css', 'div.partner img');

        $founded = false;
        foreach ($rawImages as $rawImage) {
            if ($rawImage->getAttribute('src') == $src) {
                $founded = true;
                break;
            }
        }
        assertTrue($founded);
    }

    /**
     * Check that document not contains image from some source
     *
     * @param string $src
     *
     * @Given /^я не должен видеть картинку с исходником "([^"]*)"$/
     */
    public function documentNotContainsImageWithSrc($src)
    {
        $rawImages = $this->getSession()->getPage()->findAll('css', 'div.partner img');

        $founded = false;
        foreach ($rawImages as $rawImage) {
            if ($rawImage->getAttribute('src') == $src) {
                $founded = true;
                break;
            }
        }
        assertFalse($founded);
    }
}
