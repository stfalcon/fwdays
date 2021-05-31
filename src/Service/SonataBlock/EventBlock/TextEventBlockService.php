<?php

namespace App\Service\SonataBlock\EventBlock;

use App\Entity\EventBlock;
use App\Exception\RuntimeException;
use App\Traits\GrandAccessSonataBlockServiceTrait;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * TextEventBlockService.
 */
class TextEventBlockService extends AbstractBlockService
{
    use GrandAccessSonataBlockServiceTrait;

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $eventBlock = $blockContext->getSetting('event_block');
        if (!$eventBlock instanceof EventBlock) {
            throw new RuntimeException();
        }

        $accessGrand = $this->accessSonataBlockService->isAccessGrand($eventBlock);
        if (!$accessGrand) {
            return new Response();
        }

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'event_block' => $eventBlock,
        ], $response);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'template' => 'Redesign/Event/event.text_block.html.twig',
            'event_block' => null,
        ]);
    }
}
