<?php

namespace Stfalcon\Bundle\SponsorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Event;
use Doctrine\Common\Util\Debug;

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

//        $sponsorEvents = $event->getEventSponsors();
//        $sortedSponsors = array();
//        foreach ($sponsorEvents as $sponsorEvent){
//                $category = $sponsorEvent->getCategory();
//                $sortedSponsors[$category->getSortOrder()][$category->getName()] = $sponsorEvent->getSponsor();
//        }
//        ksort($sortedSponsors);

//
//        echo "<pre> ";
//        Debug::dump($sortedSponsors);
//        echo "<pre> ";
//        exit;

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
}
