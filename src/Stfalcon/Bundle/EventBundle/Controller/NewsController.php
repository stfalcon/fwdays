<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\News;

/**
 * Event news controller
 */
class NewsController extends BaseController
{
    
    /**
     * List of all news for event
     *
     * @Route("/event/{event_slug}/news", name="event_news")
     * @Template()
     */
    public function indexAction($event_slug)
    {
        $event = $this->getEventBySlug($event_slug);
        // @todo refact. добавить пагинатор
        $news = $event->getNews();
        
        return array('event' => $event, 'news' => $news);
    }
    
    /**
     * Finds and displays a one news for event
     *
     * @Route("/event/{event_slug}/news/{news_slug}", name="event_news_show")
     * @Template()
     */
    public function showAction($event_slug, $news_slug)
    {
        $event = $this->getEventBySlug($event_slug);
        
        $oneNews = $this->getDoctrine()->getEntityManager()
                        ->getRepository('StfalconEventBundle:News')
                        ->findOneBy(array('event' => $event->getId(), 'slug' => $news_slug));
        
        if (!$oneNews) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }        
        
        return array('event' => $event, 'one_news' => $oneNews);
    }
    
    /**
     * List of last news for event
     *
     * @Template()
     *
     * @param Event $event
     * @param integer $count
     * @return void
     */
    public function lastAction(Event $event, $count)
    {
        $news = $this->getDoctrine()->getEntityManager()
                ->getRepository('StfalconEventBundle:News')->getLastNewsForEvent($event, $count);
        
        return array('event' => $event, 'news' => $news);
    }
    
}