<?php

namespace App\Controller;

use App\Entity\Page;
use App\Repository\EventRepository;
use App\Traits\ValidatorTrait;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController.
 */
class DefaultController extends AbstractController
{
    use ValidatorTrait;

    private $eventRepository;

    /**
     * @param EventRepository $eventRepository
     */
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @Route("/", name="homepage", options = {"expose"=true})
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        $events = $this->eventRepository->findBy(['active' => true], ['date' => Criteria::ASC]);

        return $this->render('Default/index.html.twig', ['events' => $events]);
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
                return $this->render('Redesign/static_contacts.page.html.twig', ['page' => $page]);
            case 'about':
                return $this->render('Page/about.html.twig', ['page' => $page]);
            default:
                return $this->render('Default/page.html.twig', ['page' => $page]);
        }
    }
}
