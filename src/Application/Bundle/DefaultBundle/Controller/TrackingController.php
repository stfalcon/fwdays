<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Application\Bundle\UserBundle\Entity\User;
use Stfalcon\Bundle\EventBundle\Entity\MailQueue;

class TrackingController extends Controller
{
    /**
     * Open mail action.
     *
     * @param integer $userId
     * @param string  $hash
     * @param integer $mailId
     *
     * @return RedirectResponse
     *
     * @Route("/trackopenmail/{hash}/{userId}/{mailId}", name="trackopenmail")
     */
    public function actionTrackOpenMail($userId, $hash, $mailId = -1)
    {
        if (-1 !== $mailId) {
            $em = $this->getDoctrine()->getManager();
            /** @var User $user */
            $user = $em->getRepository('ApplicationUserBundle:User')
                ->findOneBy(['id' => $userId, 'salt' => $hash]);

            if ($user) {
                /** @var MailQueue $mailQueue */
                $mailQueue = $em->getRepository('StfalconEventBundle:MailQueue')->findOneBy(['user' => $userId, 'mail' => $mailId]);
                if ($mailQueue && !$mailQueue->getIsOpen()) {
                    $mailQueue->setIsOpen();
                    $em->flush();
                }
            }
        }

      return $this->redirect($this->generateUrl("homepage"));
    }
}