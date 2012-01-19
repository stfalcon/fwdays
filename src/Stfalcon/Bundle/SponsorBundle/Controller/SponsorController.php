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
     * List of sponsors of event
     *
     * @Template()
     *
     * @param Event $event
     *
     * @return array List of sponsors
     */
    public function widgetAction(Event $event)
    {
        $sponsors = $this->getDoctrine()->getEntityManager()
                         ->getRepository('StfalconSponsorBundle:Sponsor')->getSponsorsOfEvent($event);

        return array('sponsors' => $sponsors);
    }
}
