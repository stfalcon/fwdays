<?php

namespace Application\Bundle\DefaultBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ServiceStatusController.
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
