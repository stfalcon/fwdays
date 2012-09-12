<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Stfalcon\Bundle\EventBundle\Entity\Event;

/**
 * Event news controller
 */
class NewsController extends BaseController
{
    /**
     * List of all news for event
     *
     * @param string $eventSlug
     *
     * @return array
     *
     * @Route("/event/{event_slug}/news", name="event_news")
     * @Template()
     */
    public function indexAction($eventSlug)
    {
        $event = $this->getEventBySlug($eventSlug);
        // @todo refact. добавить пагинатор
        $news = $event->getNews();

        return array('event' => $event, 'news' => $news);
    }

    /**
     * Finds and displays a one news for event
     *
     * @param string $eventSlug Event slug
     * @param string $newsSlug  News slug
     *
     * @return array
     * @throws NotFoundHttpException
     *
     * @Route("/event/{event_slug}/news/{news_slug}", name="event_news_show")
     * @Template()
     */
    public function showAction($eventSlug, $newsSlug)
    {
        $event = $this->getEventBySlug($eventSlug);

        $oneNews = $this->getDoctrine()->getManager()
                        ->getRepository('StfalconEventBundle:News')
                        ->findOneBy(array('event' => $event->getId(), 'slug' => $newsSlug));

        if (!$oneNews) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }

        return array(
            'event'    => $event,
            'one_news' => $oneNews
        );
    }

    /**
     * List of last news for event
     *
     * @param Event $event Event
     * @param int   $count Number of news for last event
     *
     * @return array
     *
     * @Template()
     */
    public function widgetAction(Event $event, $count)
    {
        $news = $this->getDoctrine()->getManager()
                ->getRepository('StfalconEventBundle:News')->getLastNewsForEvent($event, $count);

        return array(
            'event' => $event,
            'news'  => $news
        );
    }
}
