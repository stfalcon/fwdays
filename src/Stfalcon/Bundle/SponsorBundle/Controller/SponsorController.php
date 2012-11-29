<?php

namespace Stfalcon\Bundle\SponsorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;

/**
 * Sponsor controller
 */
class SponsorController extends Controller
{
    /**
     * List of sponsors of event
     *
     * @param Event $event
     *
     * @return array List of sponsors
     *
     * @Template()
     */
    public function widgetAction(Event $event)
    {

        /** @var $sponsorRepository \Stfalcon\Bundle\SponsorBundle\Repository\SponsorRepository */
        $sponsorRepository = $this->getDoctrine()->getManager()
            ->getRepository('StfalconSponsorBundle:Sponsor');
        $sponsors = $sponsorRepository->getSponsorsOfEvent($event);

        $sortedSponsors = array();
        foreach ($sponsors as $sponsor){
            $sortedSponsors[$sponsor['category_name']][] = $sponsor['sponsor'];
        }

        return array('sponsors' => $sortedSponsors);
    }

    /**
     * Show main sponsors of active events
     *
     * @param Event $event
     *
     * @return array List of sponsors
     *
     * @Template()
     */
    public function mainWidgetAction()
    {
        /** @var $sponsorRepository \Stfalcon\Bundle\SponsorBundle\Repository\SponsorRepository */
        $sponsorRepository = $this->getDoctrine()->getManager()
            ->getRepository('StfalconSponsorBundle:Sponsor');
        $sponsors = $sponsorRepository->getCheckedSponsorsOfActiveEvents();

        return array('sponsors' => $sponsors);
    }

    /**
     * Show sponsors page for active events
     *
     * @return array List of sponsors
     *
     * @Route("/partners", name="partners_page")
     * @Template()
     */
    public function partnersAction()
    {
        /** @var $sponsorRepository \Stfalcon\Bundle\SponsorBundle\Repository\SponsorRepository */
        $sponsorRepository = $this->getDoctrine()->getManager()
            ->getRepository('StfalconSponsorBundle:Sponsor');
        $sponsors = $sponsorRepository->getCheckedSponsorsOfActiveEvents();

        return array('sponsors' => $sponsors);
    }
}
