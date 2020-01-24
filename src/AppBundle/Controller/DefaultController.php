<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function indexAction(): Response
    {
        $events = $this->getDoctrine()
            ->getRepository(Event::class)
            ->findBy(['active' => true], ['date' => 'ASC']);

        return $this->render('@App/Default/index.html.twig', ['events' => $events]);
    }

    /**
     * @Route("/page/{slug}", name="page")
     *
     * @param Page $page
     *
     * @return Response
     */
    public function pageAction(Page $page): Response
    {
        // @todo https://redmine.stfalcon.com/issues/59959
        switch ($page->getSlug()) {
            case 'contacts':
                return $this->render('@App/Redesign/static_contacts.page.html.twig', ['page' => $page]);
            case 'about':
                return $this->render('@App/Page/about.html.twig', ['page' => $page]);
            default:
                return $this->render('@App/Default/page.html.twig', ['page' => $page]);
        }
    }
}
