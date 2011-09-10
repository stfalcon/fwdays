<?php

namespace Application\Bundle\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\NewsBundle\Entity\News;
use Stfalcon\Bundle\NewsBundle\Controller\NewsController AS BaseNewsController;

/**
 * News controller
 */
class NewsController extends BaseNewsController
{
    private function _getNews($count = null)
    {
        $news = array();
        
        $eventsNews = $this->getDoctrine()->getEntityManager()
                ->getRepository('StfalconEventBundle:News')->getLastNews($count);
        foreach ($eventsNews as $oneNews) {
                $news[$oneNews->getCreatedAt()->getTimestamp()] = $oneNews;
        }
        
        $normalNews = $this->getDoctrine()->getEntityManager()
                ->getRepository('StfalconNewsBundle:News')->getLastNews($count);
        foreach ($normalNews as $oneNews) {
                $news[$oneNews->getCreatedAt()->getTimestamp()] = $oneNews;
        }
        
        krsort($news);
        
        return array_slice($news, 0, $count);
    }
    
    /**
     * @Template()
     */
    public function lastAction($count = 10)
    {
        return array('news' => $this->_getNews($count));
    }
    
    /**
     * List of all news
     *
     * @Route("/news", name="news")
     * @Template()
     */    
    public function indexAction()
    {
        return array('news' => $this->_getNews());
    }

}
