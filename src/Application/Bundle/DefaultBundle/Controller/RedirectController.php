<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RedirectController extends Controller
{
    /**
     * @Route(path="/page/about")
     * @return RedirectResponse
     */
    public function oldAboutAction()
    {
        return new RedirectResponse($this->generateUrl('about'));
    }

    /**
     * @Route(path="/page/contacts")
     * @return RedirectResponse
     */
    public function oldContactsAction()
    {
        return new RedirectResponse($this->generateUrl('contacts'));
    }
    /**
     * @Route(path="/event/{event_slug}/speakers")
     * @param string $event_slug
     * @return RedirectResponse
     */
    public function oldEventSpeakerPage($event_slug)
    {
        $url = $this->generateUrl('event_show_redesign', ['event_slug' => $event_slug]);
        return new RedirectResponse($url.'#speakers-event');
    }


    /**
     * @Route(path="/event/{event_slug}/participants")
     * @Route(path="/event/{event_slug}/page/{page_slug}")
     * @param string $event_slug
     * @param string $page_slug
     * @return RedirectResponse
     */
    public function oldEventPages($event_slug, $page_slug = '')
    {
        $addHash = '';
        $url = $this->generateUrl('event_show_redesign', ['event_slug' => $event_slug]);
        switch ($page_slug) {
            case 'venue' :
                $addHash = '#venue-event';
                break;
            case 'program' :
                $addHash = '#program-event';
                break;
        }
        $url .= $addHash;

        return new RedirectResponse($url);
    }

    /**
     * @Route("/news", name="news")
     */
    public function redirectAction()
    {
        return new RedirectResponse($this->generateUrl('homepage'));
    }
}