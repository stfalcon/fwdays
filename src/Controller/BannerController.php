<?php

namespace App\Controller;

use App\Entity\Banner;
use App\Service\BannerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * BannerController.
 */
class BannerController extends AbstractController
{
    /**
     * @Route(path="/close-banner/{id}", name="close_banner", options = {"expose"=true}, condition="request.isXmlHttpRequest()")
     *
     * @param Banner        $banner
     * @param BannerService $bannerService
     *
     * @return Response
     */
    public function referralAction(Banner $banner, BannerService $bannerService): Response
    {
        $response = new Response();
        $bannerService->addBannerToCloseResponseCookie($banner, $response);

        return $response;
    }
}
