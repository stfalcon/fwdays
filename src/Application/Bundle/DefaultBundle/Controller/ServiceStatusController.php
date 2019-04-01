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
        $sncClient = $this->get('snc_redis.session.client');
        $sncRedisResult = $sncClient->ping();

        $code = 'PONG' === $sncRedisResult->getPayload() ? 200 : 500;

        return new Response('', $code);
    }
}
