<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ServiceStatusController.
 */
class ServiceStatusController extends AbstractController
{
    /**
     * @Route("/service/status", name="service_status")
     *
     * @return Response
     */
    public function serviceStatusAction()
    {
        return new Response('', Response::HTTP_OK);
    }
}
