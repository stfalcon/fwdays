<?php

namespace Application\Bundle\DefaultBundle\Service;

use Doctrine\Common\Persistence\ObjectRepository;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Stfalcon\Bundle\EventBundle\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FooterBlockService.
 */
class FooterBlockService extends AbstractBlockService
{
    /** @var PageRepository */
    private $pageRepository;

    /**
     * ProgramEventBlockService constructor.
     *
     * @param string           $name
     * @param EngineInterface  $templating
     * @param ObjectRepository $pageRepository
     */
    public function __construct($name, EngineInterface $templating, ObjectRepository $pageRepository)
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
            'template' => 'ApplicationDefaultBundle:Redesign:_footer_pages.html.twig',
            'pages' => null,
        ]);
    }
}
