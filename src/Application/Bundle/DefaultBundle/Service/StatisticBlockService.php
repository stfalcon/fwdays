<?php

namespace Application\Bundle\DefaultBundle\Service;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;

/**
 * StatisticBlockService.
 */
class StatisticBlockService extends AbstractBlockService
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'statistic';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSettings()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse(
            '@ApplicationDefault/Statistic/block_admin_list.html.twig',
            [
                'block' => $blockContext->getBlock(),
                'block_context' => $blockContext,
            ],
            $response
        );
    }
}
