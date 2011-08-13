<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\StfalconBundle\Entity\Page;

/**
 * Page controller.
 *
 * @Route("/page")
 */
class PageController extends Controller
{
    /**
     * Finds and displays a Page entity.
     *
     * @Route("/{slug}", name="page_show")
     * @Template()
     */
    public function showAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $page = $em->getRepository('StfalconPageBundle:Page')->findOneBy(array('slug' => $slug));

        if (!$page) {
            throw $this->createNotFoundException('Unable to find Page entity.');
        }

        return array('page' => $page);
    }
    
}