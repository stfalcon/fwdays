<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Stfalcon\Bundle\EventBundle\Entity\StaticPage;

/**
 * Page controller
 */
class StaticPageController extends Controller
{
    /**
     * Finds and displays a Page entity.
     *
     * @param \Stfalcon\Bundle\EventBundle\Entity\StaticPage $page
     *
     * @Route   ("/{slug}", name="page_show")
     * @Template()
     *
     * @return array
     */
    public function showAction(StaticPage $page)
    {
        return array('page' => $page);
    }

}