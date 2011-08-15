<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Stfalcon\Bundle\EventBundle\Entity\News;

/**
 * News controller
 */
class NewsController extends Controller
{
    
    /**
     * Lists all event news
     *
     * @Route("/event/{event_slug}/news", name="event_news")
     * @Template()
     */
    public function indexAction($event_slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $event = $em->getRepository('StfalconEventBundle:Event')->findOneBy(array('slug' => $event_slug));
        if (!$event) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }
        $this->container->set('stfalcon_event.current_event', $event);

        return array('news' => $event->getNews(), 'event' => $event);
    }
    
    /**
     * Finds and displays a News entity.
     *
     * @Route("/event/{event_slug}/news/{news_slug}", name="event_news_show")
     * @Template()
     */
    public function showAction($event_slug, $news_slug)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $event = $em->getRepository('StfalconEventBundle:Event')->findOneBy(array('slug' => $event_slug));
        if (!$event) {
            throw $this->createNotFoundException('Unable to find Event entity.');
        }
        $this->container->set('stfalcon_event.current_event', $event);
        
        $oneNews = $em->getRepository('StfalconEventBundle:News')->findOneBy(array('slug' => $news_slug, 'event' => $event->getId()));
        if (!$oneNews) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }

        return array('one_news' => $oneNews, 'event' => $event);
    }
    
    /**
     * List last news about event
     *
     * @Template()
     *
     * @param Event $event
     * @param integer|null $count
     * @return void
     */
    public function listAction(Event $event, $count = null)
    {
        $em = $this->getDoctrine()->getEntityManager();
        
        $news = $em->getRepository('StfalconEventBundle:News')->findBy(array('event' => $event->getId()));
        
        return array('news' => $news, 'event' => $event);
    }
}