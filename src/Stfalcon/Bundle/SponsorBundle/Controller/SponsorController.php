<?php

namespace Stfalcon\Bundle\SponsorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\SponsorBundle\Entity\Sponsor;
use Stfalcon\Bundle\EventBundle\Entity\Event;


/**
 * Sponsor controller
 */
class SponsorController extends Controller
{
    /**
     * List of last news for event
     *
     * @Template()
     *
     * @param Event $event
     * @return array
     */
    public function widgetAction(Event $event)
    {
        $sponsors = $this->getDoctrine()->getEntityManager()
                ->getRepository('StfalconEventBundle:Sponsors')->getSponsorsOfEvent($event);

        return array('event' => $event, 'sponsors' => $sponsors);
    }
}
