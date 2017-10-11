<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectController.
 */
class RedirectController extends Controller
{
    /**
     * @Route(path="/page/about")
     *
     * @return RedirectResponse
     */
    public function oldAboutAction()
    {
        return new RedirectResponse($this->generateUrl('about'));
    }

    /**
     * @Route(path="/page/contacts")
     *
     * @return RedirectResponse
     */
    public function oldContactsAction()
    {
        return new RedirectResponse($this->generateUrl('contacts'));
    }

    /**
     * @Route(path="/event/{eventSlug}/speakers")
     *
     * @param string $eventSlug
     *
     * @return RedirectResponse
     */
    public function oldEventSpeakerPage($eventSlug)
    {
        $url = $this->generateUrl('event_show_redesign', ['eventSlug' => $eventSlug]);

        return new RedirectResponse($url.'#speakers-event');
    }

    /**
     * @Route(path="/event/{eventSlug}/participants")
     *
     * @param string $eventSlug
     * @param string $pageSlug
     *
     * @return RedirectResponse
     */
    public function oldEventPages($eventSlug, $pageSlug = '')
    {
        $addHash = '';
        $url = $this->generateUrl('event_show_redesign', ['eventSlug' => $eventSlug]);
        switch ($pageSlug) {
            case 'venue':
                $addHash = '#venue-event';
                break;
            case 'program':
                $addHash = '#program-event';
                break;
        }
        $url .= $addHash;

        return new RedirectResponse($url);
    }

    /**
     * @Route("/news", name="news")
     *
     * @return RedirectResponse
     */
    public function redirectAction()
    {
        return new RedirectResponse($this->generateUrl('homepage'));
    }
}
