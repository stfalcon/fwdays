<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Stfalcon\Bundle\EventBundle\Entity\GeneralNews;

/**
 * Page controller
 * @Route("/page")
 */
class GeneralNewsController extends Controller
{
    /**
     * List of all news
     *
     * @Route("/news", name="news")
     * @Template()
     */
    public function indexAction()
    {
        // @todo здесь нужно будет добавить пагинатор и заменить выборку
        $news = $this->getDoctrine()->getEntityManager()
            ->getRepository('StfalconEventBundle:GeneralNews')->findAll();

        return ['news' => $news];
    }

    /**
     * Finds and displays a one news
     *
     * @Route("/news/{slug}", name="news_show")
     * @Template()
     */
    public function showAction(GeneralNews $oneNews)
    {
        return ['one_news' => $oneNews];
    }

    /**
     * List of last news
     *
     * @param integer $count
     *
     * @Template()
     *
     * @return array
     */
    public function lastAction($count)
    {
        // @todo здесь нужно будет добавить ограничение
        $news = $this->getDoctrine()->getEntityManager()
            ->getRepository('StfalconEventBundle:GeneralNews')->getLastNews($count);

        return ['news' => $news];
    }
}
