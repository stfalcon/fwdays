<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * ServiceStatusController.
 */
class ServiceStatusController extends Controller
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
