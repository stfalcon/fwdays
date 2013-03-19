<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;

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
     * @param string $event_slug
     * @return array
     */
    public function indexAction($event_slug)
    {
        $event = $this->getEventBySlug($event_slug);
        $news = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:News')
            ->getLastNewsForEvent($event);

        return array('event' => $event, 'news' => $news);
    }

    /**
     * Finds and displays a one news for event
     *
     * @Route("/event/{event_slug}/news/{news_slug}", name="event_news_show")
     * @Template()
     * @param string $event_slug
     * @param string $news_slug
     * @return array
     */
    public function showAction($event_slug, $news_slug)
    {
        $event = $this->getEventBySlug($event_slug);

        $oneNews = $this->getDoctrine()
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
     * @return array
     */
    public function widgetAction(Event $event, $count)
    {
        $news = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:News')->getLastNewsForEvent($event, $count);

        return array('event' => $event, 'news' => $news);
    }

}