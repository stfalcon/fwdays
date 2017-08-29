<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Stfalcon\Bundle\EventBundle\Entity\Page;

/**
 * Page controller
 * @Route("/old/page")
 */
class PageController extends Controller
{
    /**
     * Finds and displays a Page entity.
     *
     * @param \Stfalcon\Bundle\EventBundle\Entity\Page $page
     *
     * @Route   ("/{slug}", name="page_show")
     * @Template()
     *
     * @return array
     */
    public function showAction(Page $page)
    {
        return array('page' => $page);
    }

}