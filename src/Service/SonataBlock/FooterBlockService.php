<?php

namespace App\Service\SonataBlock;

use Doctrine\ORM\EntityRepository;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FooterBlockService.
 */
class FooterBlockService extends AbstractBlockService
{
    private $pageRepository;

    /**
     * ProgramEventBlockService constructor.
     *
     * @param string           $name
     * @param EngineInterface  $templating
     * @param EntityRepository $pageRepository
     */
    public function __construct($name, EngineInterface $templating, EntityRepository $pageRepository)
    {
        parent::__construct($name, $templating);

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
    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'template' => 'AppBundle:Redesign:_footer_pages.html.twig',
            'pages' => null,
        ]);
    }
}
