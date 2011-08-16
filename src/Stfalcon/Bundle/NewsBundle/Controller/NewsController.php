<?php

namespace Stfalcon\Bundle\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\NewsBundle\Entity\News;

/**
 * News controller
 */
class NewsController extends Controller
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
                     ->getRepository('StfalconNewsBundle:News')->findAll();

        return array('news' => $news);
    }

    /**
     * Finds and displays a one news
     *
     * @Route("/news/{slug}", name="news_show")
     * @Template()
     */
    public function showAction(News $oneNews)
    {
        return array('one_news' => $oneNews);
    }

    /**
     * List of last news
     *
     * @Template()
     *
     * @param integer $count
     * @return void
     */
    public function lastAction($count)
    {
        // @todo здесь нужно будет добавить ограничение
        $news = $this->getDoctrine()->getEntityManager()
                     ->getRepository('StfalconNewsBundle:News')->getLastNews($count);

        return array('news' => $news);
    }
    
}
