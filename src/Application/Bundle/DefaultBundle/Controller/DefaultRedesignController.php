<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Stfalcon\Bundle\EventBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultRedesignController extends Controller
{
    /**
     * @Route("/", name="homepage_redesign")
     * @Template("ApplicationDefaultBundle:Redesign:index.html.twig")
     */
    public function indexAction()
    {
        $events = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(['active' => true ]);

        return [ 'events' => $events];
    }

    /**
     * @Route("/cabinet", name="cabinet")
     * @Template("ApplicationDefaultBundle:Redesign:cabinet.html.twig")
     */
    public function cabinetAction()
    {
        return [];
    }
    /**
     * @Route("/contacts", name="contacts")
     * @Template("ApplicationDefaultBundle:Redesign:contacts.html.twig")
     */
    public function contactsAction()
    {
        return [];
    }
    /**
     * @Route("/page/{slug}", name="show_page")
     * @Template("@ApplicationDefault/Redesign/static.page.html.twig")
     * @return array
     */
    public function pageAction(Page $staticPage)
    {
        return ['text' => $staticPage->getText()];
    }
}