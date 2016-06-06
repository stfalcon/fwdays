<?php

namespace Stfalcon\Bundle\EventBundle\Controller\Admin;

use Stfalcon\Bundle\EventBundle\Entity\Mail;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MailAdminController
 */
class MailAdminController extends Controller
{
    /**
     * Start mail action
     *
     * @param Request $request Request
     * @param Mail    $mail    Mail
     * @param int     $value   Value
     *
     * @return JsonResponse
     *
     * @Route("/mail/{id}/start/{value}", name="admin_start_mail")
     */
    public function startMailAction(Request $request, Mail $mail, $value)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException();
        }

        $em = $this->getDoctrine()->getManager();

        $mail->setStart((bool) $value);
        $em->persist($mail);
        $em->flush();

        return new JsonResponse([
            'status' => true,
            'value'  => $value,
        ]);
    }
}
