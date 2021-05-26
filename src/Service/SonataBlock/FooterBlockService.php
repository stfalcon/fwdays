<?php

namespace App\Service\SonataBlock;

use App\Repository\PageRepository;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * FooterBlockService.
 */
class FooterBlockService extends AbstractBlockService
{
    /** @var PageRepository */
    private $pageRepository;

    /**
     * @param Environment    $twig
     * @param PageRepository $pageRepository
     */
    public function __construct(Environment $twig, PageRepository $pageRepository)
    {
        parent::__construct($twig);

        $this->pageRepository = $pageRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $pages = $this->pageRepository->findBy(['showInFooter' => true]);

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'pages' => $pages,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Redesign/_footer_pages.html.twig',
            'pages' => null,
        ]);
    }
}
