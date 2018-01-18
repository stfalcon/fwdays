<?php

namespace Application\Bundle\DefaultBundle\Service;

use Sonata\BlockBundle\Block\BaseBlockService;
use Symfony\Component\HttpFoundation\Response;
use Sonata\BlockBundle\Block\BlockContextInterface;

/**
 * StatisticBlockService
 */
class StatisticBlockService extends BaseBlockService
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
                'block'         => $blockContext->getBlock(),
                'block_context' => $blockContext
            ],
            $response
        );
    }
}