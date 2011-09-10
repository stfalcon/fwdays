<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        $news = array();
        $count = 10;
        
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
        
        asort($news);
        $news = array_slice($news, 0, $count);
        
        return array('news' => $news);
    }

}
