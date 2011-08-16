<?php

namespace Stfalcon\Bundle\EventBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Stfalcon\Bundle\EventBundle\Entity\Speaker;
use Stfalcon\Bundle\EventBundle\Form\SpeakerType;

/**
 * Speaker controller
 */
class SpeakerController extends Controller
{
    /**
     * Lists all sreakers for event
     *
     * @Route("/event/{event_slug}/speakers", name="event_speakers")
     * @Template()
     */
    public function indexAction($event_slug)
    {
        // @todo это заглушка
        $em = $this->getDoctrine()->getEntityManager();
        $entities = $em->getRepository('StfalconEventBundle:Speaker')->findAll();
        return array('entities' => $entities);
    }

    /**
     * Finds and displays a Speaker entity.
     *
     * @Route("/event/{event_slug}/speaker/{id}", name="event_speaker_show")
     * @Template()
     */
    public function showAction($event_slug, $id)
    {
        // @todo это заглушка
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('StfalconEventBundle:Speaker')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Speaker entity.');
        }
        
        return array(
            'entity'      => $entity,
        );
    }

}
