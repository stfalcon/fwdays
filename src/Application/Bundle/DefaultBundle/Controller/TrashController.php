<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Application\Bundle\DefaultBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class DefaultController.
 */
class TrashController extends Controller
{
    /**
     * @Route("/share-contacts/{reply}", name="share_contacts")
     *
     * @param string $reply
     *
     * @Security("has_role('ROLE_USER')")
     *
     * @return RedirectResponse
     */
    public function shareContactsAction($reply = 'no')
    {
        /** @var User */
        $user = $this->getUser();

        if ('yes' === $reply) {
            $user->setAllowShareContacts(true);
        } else {
            $user->setAllowShareContacts(false);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $url = $this->get('request_stack')->getCurrentRequest()->headers->get('referer');

        return new RedirectResponse($url);
    }
}
