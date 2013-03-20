<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Application news controller
 */
class NewsController extends Controller
{

    /**
     * Get all news (regular news and news for events)
     *
     * @param integer $count
     * @return array
     */
    private function _getNews($count = null)
    {
        $news = array();

        // get last news for events
        $eventsNews = $this->getDoctrine()
            ->getRepository('StfalconEventBundle:News')
            ->getLastNews($count);
        foreach ($eventsNews as $oneNews) {
            $news[$oneNews->getCreatedAt()->getTimestamp()] = $oneNews;
        }

        // get last news for events
        $regularNews = array();
        $this->getDoctrine()
            ->getRepository('StfalconNewsBundle:News')
            ->getLastNews($count);
        foreach ($regularNews as $oneNews) {
            $news[$oneNews->getCreatedAt()->getTimestamp()] = $oneNews;
        }

        // sort by time create
        krsort($news);

        // return mixed news
        return array_slice($news, 0, $count);
    }

    /**
     * Get last all news (block)
     *
     * @Template()
     * @return array
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
     * @return array
     */
    public function indexAction()
    {
        return array('news' => $this->_getNews());
    }

    /**
     * RSS news feed
     *
     * @Route("/rss", name="rss")
     * @return Response
     */
    public function rssAction()
    {
        $feed = new \Zend\Feed\Writer\Feed();

        // @todo text to config
        $feed->setTitle('Frameworks Days');
        $feed->setDescription('Новости событий, которые проходят под эгидой Frameworks Days');
        $feed->setLink($this->generateUrl('rss', array(), true));

        $news = $this->_getNews();
        foreach($news as $one_news) {
            // create entry and set fields
            $entry = new \Zend\Feed\Writer\Entry();
            $entry->setTitle($one_news->getTitle());
            $entry->setDescription($one_news->getPreview());
            $entry->setLink($this->generateUrl('news_show', array('slug' => $one_news->getSlug()), true));

            // add it to feed
            $feed->addEntry($entry);
        }

        // return rss 2.0 xml
        return new Response($feed->export('rss'));
    }

}
