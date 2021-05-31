<?php

namespace App\Service\SonataBlock;

use App\Entity\Banner;
use App\Service\BannerService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * BannerBlockService.
 */
class BannerBlockService extends AbstractBlockService
{
    /** @var BannerService */
    private $bannerService;

    /**
     * @param Environment   $twig
     * @param BannerService $bannerService
     */
    public function __construct(Environment $twig, BannerService $bannerService)
    {
        parent::__construct($twig);

        $this->bannerService = $bannerService;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $banner = $this->bannerService->getActiveBannerWithOutCookieClosed();

        if (!$banner instanceof Banner) {
            return new Response();
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'banner' => $banner,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Banner/banner.html.twig',
        ]);
    }
}
