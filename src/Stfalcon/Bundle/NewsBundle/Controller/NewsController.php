<?php

namespace Stfalcon\Bundle\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\NewsBundle\Entity\News;

/**
 * News controller.
 */
class NewsController extends Controller
{
    /**
     * Lists all News entities.
     *
     * @Route("/news", name="news")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('StfalconNewsBundle:News')->findAll();

        return array('entities' => $entities);
    }

    /**
     * List las News entities
     *
     * @Template()
     *
     * @param null $count
     * @return void
     */
    public function listAction($count = null)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('StfalconNewsBundle:News')->findAll();
        
        return array('entities' => $entities);
    }

    /**
     * Finds and displays a News entity.
     *
     * @Route("/news/{slug}", name="news_show")
     * @Template()
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('StfalconNewsBundle:News')->findOneBy(array('slug' => $slug));

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }

        return array('entity' => $entity);
    }

}
