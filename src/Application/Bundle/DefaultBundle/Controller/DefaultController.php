<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController.
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage", options = {"expose"=true})
     *
     * @return Response
     */
    public function indexAction()
    {
        $events = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findBy(['active' => true], ['date' => 'ASC']);

        return $this->render('ApplicationDefaultBundle:Default:index.html.twig', ['events' => $events]);
    }

    // @todo remove this action
    /**
     * @Route("/page/about", name="page_about")
     *
     * @return Response
     */
    public function pageAboutAction()
    {
        return $this->render('@ApplicationDefault/Page/about.html.twig');
    }

    /**
     * @Route("/page/{slug}", name="page")
     *
     * @param string $slug
     *
     * @return Response
     */
    public function pageAction($slug)
    {
        $staticPage = $this->getDoctrine()->getRepository('StfalconEventBundle:Page')
            ->findOneBy(['slug' => $slug]);
        if (!$staticPage) {
            throw $this->createNotFoundException(sprintf('Page not found! %s', $slug));
        }

        return $this->render('@ApplicationDefault/Page/index.html.twig', ['text' => $staticPage->getText()]);
    }

    /**
     * @todo wtf?
     *
     * @return Response
     */
    public function renderMicrolayoutAction()
    {
        $events = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:Event')
            ->findClosesActiveEvents(3);

        return $this->render('ApplicationDefaultBundle::microlayout.html.twig', ['events' => $events]);
    }
}
