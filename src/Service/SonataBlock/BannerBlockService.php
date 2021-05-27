<?php

namespace App\Service\SonataBlock;

use App\Repository\BannerRepository;
use App\Traits\RequestStackTrait;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * BannerBlockService.
 */
class BannerBlockService extends AbstractBlockService
{
    use RequestStackTrait;

    /** @var BannerRepository */
    private $bannerRepository;

    /**
     * @param Environment      $twig
     * @param BannerRepository $bannerRepository
     */
    public function __construct(Environment $twig, BannerRepository $bannerRepository)
    {
        parent::__construct($twig);

        $this->bannerRepository = $bannerRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $banners = $this->bannerRepository->getActiveBanners();
        $request = $this->requestStack->getCurrentRequest();

        if ($request instanceof Request && !empty($banners)) {
            $currentUri = $request->getRequestUri();

            foreach ($banners as $banner) {
                if (\preg_match('~'.$banner->getUrl().'~', $currentUri)) {

                    return $this->renderResponse($blockContext->getTemplate(), [
                        'block' => $blockContext->getBlock(),
                        'banner' => $banner,
                    ], $response);
                }
            }
        }

        return new Response();
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
