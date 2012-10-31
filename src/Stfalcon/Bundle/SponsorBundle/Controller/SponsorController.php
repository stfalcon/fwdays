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

        return array('sponsors' => $sponsors);
    }

    /**
     * List of sponsors of event
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
        //Debug::dump($sponsors);exit;
        return array('sponsors' => $sponsors);
    }
}
