<?php

namespace Stfalcon\Bundle\SponsorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\SponsorBundle\Entity\Sponsor;

/**
 * Sponsor controller
 */
class SponsorController extends Controller
{
    /**
     * List of all news
     *
     * @Route("/news", name="news")
     */
    public function indexAction()
    {
        // @todo здесь нужно будет добавить пагинатор и заменить выборку
        $news = $this->getDoctrine()->getEntityManager()
                     ->getRepository('StfalconNewsBundle:News')->findAll();

        return $this->render('StfalconNewsBundle:News:index.html.twig', array('news' => $news));
    }

}
